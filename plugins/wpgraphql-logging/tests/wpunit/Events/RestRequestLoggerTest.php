<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Tests\Events;

use lucatume\WPBrowser\TestCase\WPTestCase;
use Monolog\Handler\BufferHandler;
use Monolog\Level;
use WP_REST_Request;
use WPGraphQL\Logging\Admin\Settings\ConfigurationHelper;
use WPGraphQL\Logging\Admin\Settings\Fields\Tab\BasicConfigurationTab;
use WPGraphQL\Logging\Admin\Settings\Fields\Tab\DataManagementTab;
use WPGraphQL\Logging\Events\Events;
use WPGraphQL\Logging\Events\RestRequestLogger;
use WPGraphQL\Logging\Logger\Api\LogServiceInterface;
use WPGraphQL\Logging\Logger\Database\WordPressDatabaseEntity;
use WPGraphQL\Logging\Logger\Handlers\WordPressDatabaseHandler;
use WPGraphQL\Logging\Logger\LoggerService;
use WPGraphQL\Logging\Logger\Processors\DataSanitizationProcessor;
use WPGraphQL\Logging\Logger\Store\LogStoreService;

/**
 * Tests for the RestRequestLogger class.
 *
 * Requests are dispatched with rest_do_request(). Because rest_do_request()
 * does not serve the response, the `rest_pre_serve_request` filter that
 * WP_REST_Server::serve_request() applies is fired by the test.
 *
 * @package WPGraphQL\Logging\Tests\Events
 *
 * @since 1.1.0
 */
class RestRequestLoggerTest extends WPTestCase {

	protected LoggerService $logger;

	protected LogServiceInterface $log_service;

	protected ?RestRequestLogger $instance = null;

	public function setUp(): void {
		parent::setUp();
		$this->log_service = LogStoreService::get_log_service();
		$this->logger      = LoggerService::get_instance( RestRequestLogger::CHANNEL );
	}

	public function tearDown(): void {
		if ( null !== $this->instance ) {
			remove_filter( 'rest_pre_dispatch', [ $this->instance, 'start_timer' ], 0 );
			remove_filter( 'rest_pre_serve_request', [ $this->instance, 'log_rest_request' ], 10 );
			$this->instance = null;
		}
		// The channel logger is a shared singleton with a BufferHandler. Flush records a test
		// wrote but never read, so they are deleted here instead of leaking into the next test.
		$this->flush();
		$this->log_service->delete_all_entities();
		parent::tearDown();
	}

	/**
	 * @param array<string, mixed> $config
	 */
	protected function create_instance( array $config, ?LoggerService $logger = null ): RestRequestLogger {
		$config = array_merge(
			[
				BasicConfigurationTab::ENABLED           => true,
				BasicConfigurationTab::LOG_REST_REQUESTS => true,
				BasicConfigurationTab::DATA_SAMPLING     => '100',
			],
			$config
		);

		$this->instance = new RestRequestLogger( $logger ?? $this->logger, $config );
		$this->instance->register_hooks();
		return $this->instance;
	}

	/**
	 * Dispatch a REST request and fire the serve hook like WP_REST_Server::serve_request().
	 *
	 * @param array<string, mixed> $query_params
	 */
	protected function serve( string $method, string $route, array $query_params = [] ): void {
		$request = new WP_REST_Request( $method, $route );
		$request->set_query_params( $query_params );
		$response = rest_do_request( $request );
		apply_filters( 'rest_pre_serve_request', false, $response, $request, rest_get_server() );
	}

	protected function flush( ?LoggerService $logger = null ): void {
		foreach ( ( $logger ?? $this->logger )->get_monolog()->getHandlers() as $handler ) {
			if ( $handler instanceof BufferHandler ) {
				$handler->flush();
			}
		}
	}

	/**
	 * @return array<\WPGraphQL\Logging\Logger\Database\WordPressDatabaseEntity>
	 */
	protected function get_logs(): array {
		$this->flush();
		return $this->log_service->find_entities_by_where( [] );
	}

	public function test_logs_served_rest_request(): void {
		$this->create_instance( [] );
		$this->serve( 'GET', '/wp/v2/posts', [ 'per_page' => 1 ] );

		$logs = $this->get_logs();
		$this->assertCount( 1, $logs );

		$log = $logs[0];
		$this->assertInstanceOf( WordPressDatabaseEntity::class, $log );
		$this->assertSame( RestRequestLogger::CHANNEL, $log->get_channel() );
		$this->assertSame( Level::Info->value, $log->get_level() );
		$this->assertSame( 'WP REST GET /wp/v2/posts', $log->get_message() );

		$context = $log->get_context();
		$this->assertSame( 'GET', $context['method'] );
		$this->assertSame( '/wp/v2/posts', $context['route'] );
		$this->assertSame( 200, $context['status'] );
		$this->assertSame( '/wp/v2/posts', $context['matched_route'] );
		$this->assertEquals( 1, $context['params']['per_page'] );
		$this->assertArrayNotHasKey( 'order', $context['params'], 'Endpoint defaults should not be logged.' );
		$this->assertArrayHasKey( 'duration_ms', $context );
		$this->assertArrayNotHasKey( 'response', $context );
		$this->assertArrayNotHasKey( 'errors', $context );
	}

	public function test_internal_rest_do_request_is_not_logged(): void {
		$this->create_instance( [] );
		rest_do_request( new WP_REST_Request( 'GET', '/wp/v2/posts' ) );

		$this->assertCount( 0, $this->get_logs() );
	}

	public function test_does_not_log_when_rest_logging_disabled(): void {
		$this->create_instance( [ BasicConfigurationTab::LOG_REST_REQUESTS => false ] );
		$this->serve( 'GET', '/wp/v2/posts' );

		$this->assertCount( 0, $this->get_logs() );
	}

	public function test_does_not_log_when_logging_disabled(): void {
		$this->create_instance( [ BasicConfigurationTab::ENABLED => false ] );
		$this->serve( 'GET', '/wp/v2/posts' );

		$this->assertCount( 0, $this->get_logs() );
	}

	public function test_does_not_log_when_sampling_rate_is_zero(): void {
		$this->create_instance( [ BasicConfigurationTab::DATA_SAMPLING => '0' ] );
		$this->serve( 'GET', '/wp/v2/posts' );

		$this->assertCount( 0, $this->get_logs() );
	}

	public function test_does_not_log_when_ip_not_allowed(): void {
		$remote_addr            = $_SERVER['REMOTE_ADDR'] ?? null;
		$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
		$this->create_instance( [ BasicConfigurationTab::IP_RESTRICTIONS => '10.0.0.1' ] );
		$this->serve( 'GET', '/wp/v2/posts' );

		if ( null === $remote_addr ) {
			unset( $_SERVER['REMOTE_ADDR'] );
		} else {
			$_SERVER['REMOTE_ADDR'] = $remote_addr;
		}

		$this->assertCount( 0, $this->get_logs() );
	}

	public function test_does_not_log_excluded_route(): void {
		$this->create_instance( [ BasicConfigurationTab::EXCLUDE_REST_ROUTES => '/wp/v2/types, /wp/v2/posts' ] );
		$this->serve( 'GET', '/wp/v2/posts' );
		$this->serve( 'GET', '/wp/v2/taxonomies' );

		$logs = $this->get_logs();
		$this->assertCount( 1, $logs );
		$this->assertSame( '/wp/v2/taxonomies', $logs[0]->get_context()['route'] );
	}

	public function test_graphql_exclude_queries_does_not_apply_to_rest(): void {
		$this->create_instance( [ BasicConfigurationTab::EXCLUDE_QUERY => 'posts' ] );
		$this->serve( 'GET', '/wp/v2/posts' );

		$this->assertCount( 1, $this->get_logs() );
	}

	public function test_log_points_setting_does_not_apply_to_rest(): void {
		$this->create_instance( [ BasicConfigurationTab::EVENT_LOG_SELECTION => [] ] );
		$this->serve( 'GET', '/wp/v2/posts' );

		$this->assertCount( 1, $this->get_logs() );
	}

	public function test_rest_rules_filter_can_replace_rules(): void {
		$callback = static fn(): array => [];
		add_filter( 'wpgraphql_logging_rest_rules', $callback );
		$this->create_instance( [ BasicConfigurationTab::LOG_REST_REQUESTS => false ] );
		$this->serve( 'GET', '/wp/v2/posts' );
		remove_filter( 'wpgraphql_logging_rest_rules', $callback );

		$this->assertCount( 1, $this->get_logs() );
	}

	public function test_is_enabled_filter_applies_to_rest(): void {
		add_filter( 'wpgraphql_logging_is_enabled', '__return_false' );
		$this->create_instance( [] );
		$this->serve( 'GET', '/wp/v2/posts' );
		remove_filter( 'wpgraphql_logging_is_enabled', '__return_false' );

		$this->assertCount( 0, $this->get_logs() );
	}

	public function test_duration_is_omitted_when_request_was_not_dispatched(): void {
		$instance = $this->create_instance( [] );
		$request  = new WP_REST_Request( 'GET', '/wp/v2/posts' );
		$instance->log_rest_request( false, rest_ensure_response( [] ), $request );

		$context = $this->get_logs()[0]->get_context();
		$this->assertArrayNotHasKey( 'duration_ms', $context );
		$this->assertSame( 200, $context['status'] );
	}

	public function test_only_the_first_dispatched_request_is_timed(): void {
		$instance = $this->create_instance( [] );
		$outer    = new WP_REST_Request( 'POST', '/batch/v1' );
		$inner    = new WP_REST_Request( 'GET', '/wp/v2/posts' );
		$instance->start_timer( null, rest_get_server(), $outer );
		$instance->start_timer( null, rest_get_server(), $inner );

		$property = new \ReflectionProperty( RestRequestLogger::class, 'start_time' );
		$property->setAccessible( true );
		$this->assertSame( [ spl_object_id( $outer ) ], array_keys( $property->getValue( $instance ) ) );
	}

	public function test_timer_is_reset_when_request_is_not_logged(): void {
		$instance = $this->create_instance( [ BasicConfigurationTab::DATA_SAMPLING => '0' ] );
		$this->serve( 'GET', '/wp/v2/posts' );

		$property = new \ReflectionProperty( RestRequestLogger::class, 'start_time' );
		$property->setAccessible( true );
		$this->assertSame( [], $property->getValue( $instance ) );
	}

	public function test_error_response_logged_as_error(): void {
		$this->create_instance( [] );
		$this->serve( 'GET', '/wp/v2/does-not-exist' );

		$logs = $this->get_logs();
		$this->assertCount( 1, $logs );
		$this->assertSame( Level::Error->value, $logs[0]->get_level() );

		$context = $logs[0]->get_context();
		$this->assertSame( 404, $context['status'] );
		$this->assertSame( 'rest_no_route', $context['errors']['code'] );
	}

	public function test_error_body_is_not_duplicated_as_response(): void {
		$this->create_instance( [ BasicConfigurationTab::LOG_RESPONSE => true ] );
		$this->serve( 'GET', '/wp/v2/does-not-exist' );

		$context = $this->get_logs()[0]->get_context();
		$this->assertArrayHasKey( 'errors', $context );
		$this->assertArrayNotHasKey( 'response', $context );
	}

	public function test_options_preflight_requests_are_not_logged(): void {
		$this->create_instance( [] );
		$this->serve( 'OPTIONS', '/wp/v2/posts' );

		$this->assertCount( 0, $this->get_logs() );
	}

	public function test_non_rest_request_arguments_are_ignored(): void {
		$instance = $this->create_instance( [] );

		$this->assertSame( 'served', $instance->log_rest_request( 'served', null, 'not-a-request' ) );
		$this->assertSame( 'pre', $instance->start_timer( 'pre', null, null ) );
		$this->assertCount( 0, $this->get_logs() );
	}

	public function test_logs_response_when_log_response_enabled(): void {
		$this->create_instance( [ BasicConfigurationTab::LOG_RESPONSE => true ] );
		$this->serve( 'GET', '/wp/v2/types/post' );

		$logs = $this->get_logs();
		$this->assertCount( 1, $logs );
		$this->assertSame( 'post', $logs[0]->get_context()['response']['slug'] );
	}

	public function test_sensitive_params_are_anonymized_by_recommended_rules(): void {
		$processor  = new DataSanitizationProcessor();
		$reflection = new \ReflectionProperty( $processor, 'config' );
		$reflection->setAccessible( true );
		$reflection->setValue( $processor, [
			DataManagementTab::DATA_SANITIZATION_ENABLED => true,
			DataManagementTab::DATA_SANITIZATION_METHOD  => 'recommended',
		] );

		$logger = LoggerService::get_instance(
			RestRequestLogger::CHANNEL . '_sanitization_test',
			[ new BufferHandler( new WordPressDatabaseHandler() ) ],
			[ $processor ]
		);

		$this->create_instance( [], $logger );
		$this->serve( 'GET', '/wp/v2/posts', [ 'password' => 'secret', 'search' => 'hello' ] );
		$this->flush( $logger );

		$logs = $this->log_service->find_entities_by_where( [] );
		$this->assertCount( 1, $logs );
		$params = $logs[0]->get_context()['params'];
		$this->assertSame( '***', $params['password'] );
		$this->assertSame( 'hello', $params['search'] );
	}

	public function test_serve_filter_value_is_returned_unchanged(): void {
		$instance = $this->create_instance( [] );
		$request  = new WP_REST_Request( 'GET', '/wp/v2/posts' );
		$response = rest_do_request( $request );

		$this->assertTrue( $instance->log_rest_request( true, $response, $request ) );
		$this->assertFalse( $instance->log_rest_request( false, $response, $request ) );
		$this->assertSame( 'pre', $instance->start_timer( 'pre', rest_get_server(), $request ) );
	}

	public function test_event_transform_can_modify_context(): void {
		$this->create_instance( [] );
		$callback = static function ( array $payload ): array {
			$payload['context']['custom'] = 'value';
			return $payload;
		};
		add_filter( 'wpgraphql_logging_filter_' . Events::REST_REQUEST_SERVED, $callback );

		$this->serve( 'GET', '/wp/v2/posts' );
		remove_filter( 'wpgraphql_logging_filter_' . Events::REST_REQUEST_SERVED, $callback );

		$logs = $this->get_logs();
		$this->assertCount( 1, $logs );
		$this->assertSame( 'value', $logs[0]->get_context()['custom'] );
	}

	public function test_init_does_not_hook_when_setting_is_off(): void {
		$this->reset_singleton();
		$this->set_basic_config( [ BasicConfigurationTab::LOG_REST_REQUESTS => false ] );

		$this->assertNull( RestRequestLogger::init() );
	}

	public function test_init_hooks_when_setting_is_on(): void {
		$this->reset_singleton();
		$this->set_basic_config( [ BasicConfigurationTab::LOG_REST_REQUESTS => true ] );

		$instance = RestRequestLogger::init();
		$this->assertInstanceOf( RestRequestLogger::class, $instance );
		$this->assertSame( $instance, RestRequestLogger::init() );
		$this->assertSame( 0, has_filter( 'rest_pre_dispatch', [ $instance, 'start_timer' ] ) );
		$this->assertSame( 10, has_filter( 'rest_pre_serve_request', [ $instance, 'log_rest_request' ] ) );

		$this->instance = $instance;
		$this->reset_singleton();
	}

	protected function reset_singleton(): void {
		$property = new \ReflectionProperty( RestRequestLogger::class, 'instance' );
		$property->setAccessible( true );
		$property->setValue( null, null );
	}

	/**
	 * @param array<string, mixed> $basic_config
	 */
	protected function set_basic_config( array $basic_config ): void {
		$helper = ConfigurationHelper::get_instance();
		update_option( $helper->get_option_key(), [ BasicConfigurationTab::get_name() => $basic_config ] );
		$helper->clear_cache();
	}
}
