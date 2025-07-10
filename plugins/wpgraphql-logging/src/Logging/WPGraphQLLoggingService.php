<?php

declare( strict_types=1 );

namespace WPGraphQL\Logging\Logging;

use GraphQL\Server\OperationParams;
use Monolog\Formatter\LineFormatter;
use Monolog\Logger;
use WPGraphQL\Logging\Logging\Handlers\WPGraphQLDatabaseHandler;

class WPGraphQLLoggingService {
	/**
	 * The instance of the logger.
	 *
	 * @var \WPGraphQL\Logging\Logging\WPGraphQLLoggingService|null
	 */
	protected static ?WPGraphQLLoggingService $instance = null;

	/**
	 * @TODO POC - this should be some type of service but for POC this works.
	 *
	 *
	 * @var Logger|null
	 */
	protected ?Logger $logger = null;


	protected $start_time = null;
	protected int $start_memory = 0;

	/**
	 * Private constructor to prevent direct instantiation.
	 */
	protected function __construct() {
	}

	/**
	 * Get the instance of the logger.
	 *
	 * @return \WPGraphQL\Logging\Logging\WPGraphQLLoggingService
	 */
	public static function init(): self {
		if ( ! isset( self::$instance ) || ! ( is_a( self::$instance, self::class ) ) ) {
			self::$instance = new self();
			self::$instance->setup();
		}

		return self::$instance;
	}

	/**
	 * @TODO POC
	 *
	 * This is POC on how to log to monolog. This isn't the finished product and should have different services
	 */
	protected function setup(): void {
		// Setup handlers
		$this->setup_handlers();
		$this->setup_logging_events();
	}

	/**
	 * Setup the logging handlers.
	 *
	 * @TODO POC - Refactor this to use a service container or similar pattern.
	 *
	 * @return vpod
	 */
	protected function setup_handlers(): void {

		// Create the database handler
		$logger    = new Logger( 'wpgraphql_logging' );
		$dbHandler = new WPGraphQLDatabaseHandler( Logger::DEBUG );

		// Optional: Set a custom formatter
		$formatter = new LineFormatter(
			"[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n",
			'Y-m-d H:i:s'
		);
		$dbHandler->setFormatter( $formatter );

		// Add the handler to the logger
		$logger->pushHandler( $dbHandler );

		$this->logger = $logger;
	}

	protected function setup_logging_events(): void {
		add_action( 'do_graphql_request', [ $this, 'log_pre_request' ], 10, 3 );
		add_action( 'graphql_process_http_request_response', [ $this, 'log_post_request' ], 10, 5 );
		// @TODO POC - Add more events here
		// @TODO somehow group these?
	}

	public function log_pre_request( $query, $variables, $operation_name ): void {
		$this->start_time   = microtime( true );
		$this->start_memory = memory_get_usage( true );

		// Log the incoming request
		$this->logger->info( 'Request', [
			'query'          => $query,
			'time'           => $this->start_time,
			'memory_usage'   => $this->start_memory,
			'variables'      => $variables,
			'operation_name' => $operation_name,
			'user_id'        => get_current_user_id()
		] );
	}

	public function log_post_request( $response, $result, string $operation_name, string $query, array $variables ) {
		$time   = microtime( true );
		$memory = memory_get_usage( true );

		// Log the incoming request
		$this->logger->info( 'Response', [
			'query'          => $query,
			'time'           => $time,
			'memory_usage'   => $memory,
			'variables'      => $variables,
			'operation_name' => $operation_name,
			'user_id'        => get_current_user_id()
		] );
	}
}
