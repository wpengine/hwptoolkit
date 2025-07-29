<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Events;

use Monolog\Level;
use WPGraphQL\Logging\Logger\LoggerService;

/**
 * WPGraphQL Query Event Lifecycle -
 *
 * POC @TODO - Add pub/sub for query events.
 *
 * @package WPGraphQL\Logging
 *
 * @since 0.0.1
 */
class QueryEventLifecycle {
	/**
	 * The single instance of the class.
	 *
	 * @var \WPGraphQL\Logging\Events\QueryEventLifecycle|null
	 */
	private static ?QueryEventLifecycle $instance = null;

	/**
	 * The logger service instance.
	 *
	 * @param \WPGraphQL\Logging\Logger\LoggerService $logger
	 */
	protected function __construct(readonly LoggerService $logger) {
	}

	/**
	 * Get or create the single instance of the class.
	 */
	public static function init(): QueryEventLifecycle {
		if ( null === self::$instance ) {
			// @TODO - Add filter to allow for custom logger service.
			$logger         = LoggerService::get_instance();
			self::$instance = new self( $logger );
			self::$instance->setup();
		}
		return self::$instance;
	}

	/**
	 * Logs the pre-request event for a GraphQL query.
	 *
	 * @param string $query The GraphQL query.
	 * @param mixed  $variables The variables for the query.
	 * @param string $operation_name The name of the operation.
	 */
	public function log_pre_request( $query, $variables, $operation_name ): void {

		try {
			$context = [];
			$context = apply_filters( 'wpgraphql_logging_pre_request_context', $context, $query, $variables, $operation_name );
			$level   = apply_filters( 'wpgraphql_logging_pre_request_level', Level::Info, $query, $variables, $operation_name );
			$this->logger->log( $level, 'WPGraphQL Incoming Request', $context );
		} catch ( \Throwable $e ) {
			// @TODO - Handle logging errors gracefully.
			error_log( 'Error in log_pre_request: ' . $e->getMessage() ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		}
	}

	/**
	 * Logs the post-request event for a GraphQL query.
	 *
	 * @param mixed                $response The response from the GraphQL request.
	 * @param mixed                $result The result of the GraphQL request.
	 * @param string               $operation_name The name of the operation.
	 * @param string               $query The GraphQL query.
	 * @param array<string, mixed> $variables The variables for the query.
	 */
	public function log_post_request( $response, $result, string $operation_name, string $query, array $variables ): void {

		try {
			$context = [];
			$level   = Level::Info;
			$context = apply_filters( 'wpgraphql_logging_post_request_context', $context, $response, $result, $operation_name, $query, $variables );
			$level   = apply_filters( 'wpgraphql_logging_post_request_level', $level, $response, $result, $operation_name, $query, $variables );
			$this->logger->log( $level, 'WPGraphQL Outgoing Response', $context );
		} catch ( \Throwable $e ) {
			// @TODO - Handle logging errors gracefully.
			error_log( 'Error in log_post_request: ' . $e->getMessage() );  // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		}
	}

	/**
	 * Register actions and filters.
	 */
	protected function setup(): void {

		// @TODO: Update POC and use pub/sub for query events.

		/**
		 * @psalm-suppress HookNotFound
		 */
		add_action( 'do_graphql_request', [ $this, 'log_pre_request' ], 10, 3 );

		/**
		 * @psalm-suppress HookNotFound
		 */
		add_action( 'graphql_process_http_request_response', [ $this, 'log_post_request' ], 10, 5 );
	}
}
