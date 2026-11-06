<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Events;

use Monolog\Level;
use WPGraphQL\Logging\Admin\Settings\ConfigurationHelper;
use WPGraphQL\Logging\Admin\Settings\Fields\Tab\BasicConfigurationTab;
use WPGraphQL\Logging\Logger\LoggerService;
use WPGraphQL\Logging\Logger\LoggingHelper;
use WPGraphQL\Logging\Logger\Rules\EnabledRule;
use WPGraphQL\Logging\Logger\Rules\ExcludeRestRouteRule;
use WPGraphQL\Logging\Logger\Rules\IpRestrictionsRule;
use WPGraphQL\Logging\Logger\Rules\LogRestRequestsRule;
use WPGraphQL\Logging\Logger\Rules\SamplingRateRule;
use WP_HTTP_Response;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Logs WordPress REST API requests through the plugin's logger pipeline.
 *
 * One log entry is written per REST request served over HTTP. Entries are
 * written to their own channel (see self::CHANNEL) so they can be told
 * apart from WPGraphQL entries without a schema change.
 *
 * Internal dispatches made with rest_do_request(), embedded (`_embed`)
 * sub-requests and block editor preloads are not served over HTTP and
 * are not logged.
 *
 * @package WPGraphQL\Logging
 *
 * @since 1.1.0
 */
class RestRequestLogger {
	use LoggingHelper;

	/**
	 * The logger channel used for REST API log entries.
	 *
	 * @var string
	 */
	public const CHANNEL = 'wpgraphql_logging_rest';

	/**
	 * The logger service instance.
	 *
	 * @var \WPGraphQL\Logging\Logger\LoggerService
	 */
	protected LoggerService $logger;

	/**
	 * The basic configuration settings.
	 *
	 * @var array<string, mixed>
	 */
	protected array $config;

	/**
	 * When dispatching of the timed request started, keyed by its object ID.
	 *
	 * Only one request is timed at a time: the top-level HTTP request is
	 * always dispatched first, and nested dispatches (batch sub-requests,
	 * rest_do_request() calls) must not grow this indefinitely.
	 *
	 * @var array<int, float>
	 */
	protected array $start_time = [];

	/**
	 * The single instance of the class.
	 *
	 * @var \WPGraphQL\Logging\Events\RestRequestLogger|null
	 */
	private static ?RestRequestLogger $instance = null;

	/**
	 * RestRequestLogger constructor.
	 *
	 * @param \WPGraphQL\Logging\Logger\LoggerService $logger The logger instance.
	 * @param array<string, mixed>                    $config The logging configuration.
	 */
	public function __construct( LoggerService $logger, array $config ) {
		$this->logger = $logger;
		$this->config = $config;
	}

	/**
	 * Register the REST hooks when REST logging has been switched on.
	 *
	 * Nothing is hooked while the setting is off, so installs that have not
	 * opted in pay no cost on REST requests.
	 */
	public static function init(): ?RestRequestLogger {
		if ( null !== self::$instance ) {
			return self::$instance;
		}

		$config = ConfigurationHelper::get_instance()->get_basic_config();
		if ( ! (bool) ( $config[ BasicConfigurationTab::LOG_REST_REQUESTS ] ?? false ) ) {
			return null;
		}

		self::$instance = new self( LoggerService::get_instance( self::CHANNEL ), $config );
		self::$instance->register_hooks();

		return self::$instance;
	}

	/**
	 * Register the WordPress REST API hooks.
	 */
	public function register_hooks(): void {
		add_filter( 'rest_pre_dispatch', [ $this, 'start_timer' ], 0, 3 );
		add_filter( 'rest_pre_serve_request', [ $this, 'log_rest_request' ], 10, 3 );
	}

	/**
	 * Record when dispatching of a REST request started.
	 *
	 * Hooked to the `rest_pre_dispatch` filter. The filtered value is
	 * returned untouched.
	 *
	 * @param mixed                  $result  Response to replace the requested version with.
	 * @param \WP_REST_Server|mixed  $server  Server instance.
	 * @param \WP_REST_Request|mixed $request Request used to generate the response.
	 *
	 * @return mixed The unmodified result.
	 */
	public function start_timer( mixed $result, mixed $server, mixed $request ): mixed {
		if ( $request instanceof WP_REST_Request && [] === $this->start_time ) {
			$this->start_time = [ spl_object_id( $request ) => microtime( true ) ];
		}
		return $result;
	}

	/**
	 * Log a REST request just before its response is sent to the client.
	 *
	 * Hooked to the `rest_pre_serve_request` filter, which only runs for
	 * requests served over HTTP. The filtered value is returned untouched.
	 *
	 * @param bool|mixed              $served  Whether the request has already been served.
	 * @param \WP_HTTP_Response|mixed $result  Result to send to the client.
	 * @param \WP_REST_Request|mixed  $request Request used to generate the response.
	 *
	 * @return bool|mixed The unmodified served flag.
	 */
	public function log_rest_request( mixed $served, mixed $result, mixed $request ): mixed {
		if ( ! $request instanceof WP_REST_Request ) {
			return $served;
		}

		try {
			$duration_ms = $this->stop_timer( $request );
			if ( ! $this->should_log_rest_request( $request ) ) {
				return $served;
			}

			$route = $request->get_route();

			$context = $this->get_context( $request, $result, $duration_ms );
			$level   = Level::Info;
			if ( isset( $context['errors'] ) ) {
				$level = Level::Error;
			}

			$payload = EventManager::transform( Events::REST_REQUEST_SERVED, [
				'context' => $context,
				'level'   => $level,
			] );
			$message = sprintf( 'WP REST %s %s', $request->get_method(), $route );
			$this->logger->log( $payload['level'], $message, $payload['context'] );
			EventManager::publish( Events::REST_REQUEST_SERVED, [ 'context' => $payload['context'] ] );
		} catch ( \Throwable $e ) {
			$this->process_application_error( Events::REST_REQUEST_SERVED, $e );
		}

		return $served;
	}

	/**
	 * Stop the dispatch timer and return the elapsed time for the request.
	 *
	 * @param \WP_REST_Request $request The REST request.
	 *
	 * @return float|null Milliseconds since dispatch started, or null if the request was not timed.
	 */
	protected function stop_timer( WP_REST_Request $request ): ?float {
		$request_id  = spl_object_id( $request );
		$duration_ms = isset( $this->start_time[ $request_id ] ) ? round( ( microtime( true ) - $this->start_time[ $request_id ] ) * 1000.0, 2 ) : null;

		$this->start_time = [];
		return $duration_ms;
	}

	/**
	 * Whether the REST request should be logged.
	 *
	 * @param \WP_REST_Request $request The REST request.
	 */
	protected function should_log_rest_request( WP_REST_Request $request ): bool {
		// CORS preflight requests carry no payload and would double the entries for browser clients.
		if ( 'OPTIONS' === $request->get_method() ) {
			return false;
		}

		// HTTP requests to the GraphQL endpoint are logged by the GraphQL lifecycle. This is a
		// safeguard: WPGraphQL does not serve those through the REST server. is_graphql_request()
		// is deliberately not used, as it is also true while a REST callback runs graphql().
		if ( function_exists( 'is_graphql_http_request' ) && is_graphql_http_request() ) {
			return false;
		}

		return $this->is_logging_enabled( $this->config, $request->get_route() );
	}

	/**
	 * Build the log context for a REST request.
	 *
	 * @param \WP_REST_Request        $request The REST request.
	 * @param \WP_HTTP_Response|mixed $result  The response about to be served.
	 * @param float|null              $duration_ms Milliseconds spent dispatching the request.
	 *
	 * @return array<string, mixed>
	 */
	protected function get_context( WP_REST_Request $request, mixed $result, ?float $duration_ms = null ): array {
		$context = [
			'method' => $request->get_method(),
			'route'  => $request->get_route(),
			'params' => $this->get_sent_params( $request ),
		];

		if ( null !== $duration_ms ) {
			$context['duration_ms'] = $duration_ms;
		}

		if ( ! $result instanceof WP_HTTP_Response ) {
			return $context;
		}

		$context['status'] = $result->get_status();
		if ( $result instanceof WP_REST_Response ) {
			$context['matched_route'] = $result->get_matched_route();
		}

		// Error bodies are small (code, message, data) and always useful, so they are logged
		// regardless of the Log Response setting. Success bodies are only logged when opted in.
		if ( $result->get_status() >= 400 ) {
			$context['errors'] = $result->get_data();
			return $context;
		}

		if ( $this->should_log_response( $this->config ) ) {
			$context['response'] = $result->get_data();
		}

		return $context;
	}

	/**
	 * Get the parameters the client sent, leaving out endpoint defaults.
	 *
	 * WP_REST_Request::get_params() also returns every registered default
	 * (page, per_page, context, order...), which would bloat each entry.
	 * Values come from get_params(), so they are the sanitized values the
	 * endpoint received.
	 *
	 * @param \WP_REST_Request $request The REST request.
	 *
	 * @return array<string, mixed>
	 */
	protected function get_sent_params( WP_REST_Request $request ): array {
		$sent = array_merge(
			$request->get_url_params(),
			$request->get_query_params(),
			$request->get_body_params(),
			$request->get_json_params() ?? []
		);

		return array_intersect_key( $request->get_params(), $sent );
	}

	/**
	 * Get the rules that decide whether a REST request is logged.
	 *
	 * GraphQL specific rules (query required, excluded queries) are swapped
	 * for REST equivalents. The route is passed to rules in place of the
	 * GraphQL query string. Sampling runs last so the dice are only rolled
	 * for requests that are otherwise eligible.
	 *
	 * @return array<\WPGraphQL\Logging\Logger\Api\LoggingRuleInterface>
	 */
	protected function get_logging_rules(): array {
		$default_rules = [
			new LogRestRequestsRule(),
			new EnabledRule(),
			new IpRestrictionsRule(),
			new ExcludeRestRouteRule(),
			new SamplingRateRule(),
		];

		/**
		 * Filter the logging rules used for WordPress REST API requests.
		 *
		 * @param array<\WPGraphQL\Logging\Logger\Api\LoggingRuleInterface> $rules Array of rule objects.
		 */
		return apply_filters( 'wpgraphql_logging_rest_rules', $default_rules );
	}
}
