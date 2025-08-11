<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Events;

use GraphQL\Executor\ExecutionResult;
use GraphQL\Server\OperationParams;
use Monolog\Level;
use WPGraphQL\Logging\Logger\LoggerService;
use WPGraphQL\Request;

/**
 * WPGraphQL Query Event Lifecycle -
 *
 * Handles logging for GraphQL query lifecycle events.
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
     * @var \WPGraphQL\Logging\Logger\LoggerService
	 */
    protected LoggerService $logger;

    /**
     * @param \WPGraphQL\Logging\Logger\LoggerService $logger
     */
    protected function __construct( LoggerService $logger ) {
        $this->logger = $logger;
    }

	/**
	 * Get or create the single instance of the class.
	 */
	public static function init(): QueryEventLifecycle {
		if ( null === self::$instance ) {
			$logger         = LoggerService::get_instance();
			self::$instance = new self( $logger );
			self::$instance->setup();
		}

		return self::$instance;
	}

	/**
	 * Logs the pre-request event for a GraphQL query.
	 * This method is hooked into 'do_graphql_request'.
	 *
	 * @param string      $query The GraphQL query string.
	 * @param string|null $operation_name The name of the operation. Made nullable.
	 * @param array|null  $variables The variables for the query. Made nullable.
	 */
	public function log_pre_request( string $query, ?string $operation_name, ?array $variables ): void {
		try {
			$context = [
				'query'          => $query,
				'variables'      => $variables,
				'operation_name' => $operation_name,
			];

			// Allow subscribers to transform context/level via EventManager
            $payload = EventManager::transform( Events::PRE_REQUEST, [
                'query'          => $query,
                'variables'      => $variables,
                'operation_name' => $operation_name,
                'context'        => $context,
                'level'          => Level::Info,
            ] );

            $this->logger->log( $payload['level'], 'WPGraphQL Incoming Request', $payload['context'] );

            // Publish event for subscribers
            EventManager::publish( Events::PRE_REQUEST, [
                'query'          => $query,
                'variables'      => $variables,
                'operation_name' => $operation_name,
                'level'          => (string) $level->getName(),
            ] );
		} catch ( \Throwable $e ) {
			// @TODO - Handle logging errors gracefully.
			error_log( 'Error in log_pre_request: ' . $e->getMessage() ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		}
	}


	/**
	 * Logs the post-request event for a GraphQL query.
	 * This method is now hooked into 'graphql_after_execute'.
	 *
	 * @param \GraphQL\Executor\ExecutionResult|array<int, \GraphQL\Executor\ExecutionResult> $response The GraphQL execution result(s).
	 * This can be a single ExecutionResult object or an array of them for batch requests.
	 * @param \WPGraphQL\Request                                                              $request_instance The WPGraphQL Request instance.
	 */
	public function log_post_request( $response, Request $request_instance ): void {
		// Extract relevant data from the WPGraphQL Request instance
		$params         = $request_instance->get_params();
		$query          = null;
		$operation_name = null;
		$variables      = null;
		$status_code    = 200; // Default success status

		// Handle single or batch requests to get query details
		if ( $params instanceof OperationParams ) {
			$query          = $params->query;
			$operation_name = $params->operation;
			$variables      = $params->variables;
		} elseif ( is_array( $params ) && ! empty( $params[0] ) && $params[0] instanceof OperationParams ) {
			$query          = $params[0]->query;
			$operation_name = $params[0]->operation;
			$variables      = $params[0]->variables;
		} else {
			// Do nothing if the params are not an OperationParams object or an array of OperationParams objects
			return;
		}

		// Determine status code if available (WPGraphQL Router sets this)
		if ( class_exists( '\WPGraphQL\Router' ) && property_exists( '\WPGraphQL\Router', '$http_status_code' ) ) {
			$status_code = \WPGraphQL\Router::$http_status_code;
		}

		// Extract data and errors from the ExecutionResult object(s)
		$response_data   = null;
		$response_errors = null;

		if ( $response instanceof ExecutionResult ) {
			$response_data   = $response->data;
			$response_errors = $response->errors;
		} elseif ( is_array( $response ) && ! empty( $response[0] ) && $response[0] instanceof ExecutionResult ) {
			// For batch requests, aggregate data/errors from all results
			$response_data   = array_map( static fn( $res ) => $res->data, $response );
			$response_errors = array_reduce( $response, static fn( $carry, $res ) => array_merge( $carry, $res->errors ?? [] ), [] );
			if ( empty( $response_errors ) ) {
				$response_errors = null; // Ensure it's null if no errors
			}
		}


		try {
			$context = [
				'query'           => $query,
				'operation_name'  => $operation_name,
				'variables'       => $variables,
				'status_code'     => $status_code,
				'response_data'   => $response_data,
				'response_errors' => $response_errors,
			];
			$level   = Level::Info;

            $payload = EventManager::transform( Events::POST_REQUEST, [
                'query'          => $query,
                'variables'      => $variables,
                'operation_name' => $operation_name,
                'context'        => $context,
                'level'          => Level::Info,
            ] );

			$this->logger->log( $payload['level'], 'WPGraphQL Response', $payload['context'] );

			// Log errors specifically if present in the response
			if ( ! empty( $response_errors ) ) {
				$this->logger->error(
					'GraphQL query completed with errors.',
					[
						'query'          => $query,
						'operation_name' => $operation_name,
						'status_code'    => $status_code,
						'errors'         => array_map( static fn( $error ) => $error->getMessage(), $response_errors ), // Extract message from error object
						'full_errors'    => $response_errors, // Include full error details for debugging
					]
				);
			}
		} catch ( \Throwable $e ) {
			// @TODO - Handle logging errors gracefully.
			error_log( 'Error in log_post_request: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine() );
		}
	}

	/**
	 * Register actions and filters.
	 */
	protected function setup(): void {

		add_action( 'do_graphql_request', [ $this, 'log_pre_request' ], 10, 3 );



		/**
		 * @TODO
		* Pre-request: do_graphql_request
		 * Before Query Execution: pre_graphql_execute_request
		 * Before Field Resolve: graphql_pre_resolve_field
		 * After Field Resolve: graphql_resolve_field
		 * After Query Execution: graphql_execute
		 * Response: graphql_after_execute
		 * Failure: graphql_after_execute or graphql_request_results
		 */

		/**
		 * @psalm-suppress HookNotFound
		 */
		//add_action( 'graphql_after_execute', [ $this, 'log_post_request' ], 10, 2 );
	}
}
