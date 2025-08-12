<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Events;

use GraphQL\Executor\ExecutionResult;
use Monolog\Level;
use WPGraphQL\Logging\Logger\LoggerService;
use WPGraphQL\Request;
use WPGraphQL\WPSchema;

/**
 * WPGraphQL Query Event Lifecycle.
 *
 * Handles logging for GraphQL query lifecycle events.
 *
 * @package WPGraphQL\Logging
 *
 * @since 0.0.1
 */
class QueryEventLifecycle {
	/**
	 * The logger service instance.
	 *
	 * @var \WPGraphQL\Logging\Logger\LoggerService
	 */
	protected LoggerService $logger;

	/**
	 * The single instance of the class.
	 *
	 * @var \WPGraphQL\Logging\Events\QueryEventLifecycle|null
	 */
	private static ?QueryEventLifecycle $instance = null;

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
	 * Initial Incoming Request.
	 *
	 * @hook do_graphql_request
	 *
	 * @param string                    $query           The GraphQL query string.
	 * @param string|null               $operation_name  The name of the operation. Made nullable.
	 * @param array<string, mixed>|null $variables       The variables for the query. Made nullable.
	 */
	public function log_pre_request( string $query, ?string $operation_name, ?array $variables ): void {
		try {
			$context = [
				'query'          => $query,
				'variables'      => $variables,
				'operation_name' => $operation_name,
			];

			$payload = EventManager::transform(
				Events::PRE_REQUEST,
				[
					'context' => $context,
					'level'   => Level::Info,
				]
			);

			$this->logger->log( $payload['level'], 'WPGraphQL Pre Request', $payload['context'] );

			EventManager::publish(
				Events::PRE_REQUEST,
				[
					'context' => $payload['context'],
					'level'   => (string) $payload['level']->getName(),
				]
			);
		} catch ( \Throwable $e ) {
			$this->process_application_error( Events::PRE_REQUEST, $e );
		}
	}

	/**
	 * Before Request Execution.
	 *
	 * @hook graphql_before_execute
	 *
	 * @param \WPGraphQL\Request $request          The WPGraphQL Request instance.
	 */
	public function log_graphql_before_execute(Request $request ): void {
		try {
			/** @var \GraphQL\Server\OperationParams $params */
			$params  = $request->params;
			$context = [
				'query'          => $params->query,
				'operation_name' => $params->operation,
				'variables'      => $params->variables,
				'params'         => $params,
			];

			$payload = EventManager::transform(
				Events::BEFORE_GRAPHQL_EXECUTION,
				[
					'context' => $context,
					'level'   => Level::Info,
				]
			);

			$this->logger->log( $payload['level'], 'WPGraphQL Before Query Execution', $payload['context'] );

			EventManager::publish(
				Events::BEFORE_GRAPHQL_EXECUTION,
				[
					'context' => $payload['context'],
					'level'   => (string) $payload['level']->getName(),
				]
			);
		} catch ( \Throwable $e ) {
			$this->process_application_error( Events::BEFORE_GRAPHQL_EXECUTION, $e );
		}
	}

	/**
	 * After Query Execution.
	 *
	 * @hook graphql_execute
	 *
	 * @param array<mixed>|\GraphQL\Executor\ExecutionResult $response The GraphQL execution result(s).
	 * @param \WPGraphQL\WPSchema                            $schema The WPGraphQL schema.
	 * @param string|null                                    $operation The operation name.
	 * @param string                                         $query The query string.
	 * @param array<string, mixed>                           $variables The variables for the query.
	 * @param \WPGraphQL\Request                             $request The WPGraphQL request instance.
	 */
	public function log_after_graphql_execute( array|ExecutionResult $response, WPSchema $schema, ?string $operation, string $query, array $variables, Request $request ): void {

		try {
			$context = [
				'query'          => $query,
				'operation_name' => $operation ?? '',
				'variables'      => $variables,
				'response'       => $response,
				'schema'         => $schema,
				'request'        => $request,
			];

			$payload = EventManager::transform(
				Events::AFTER_GRAPHQL_EXECUTION,
				[
					'context' => $context,
					'level'   => Level::Info,
				]
			);

			$this->logger->log( $payload['level'], 'WPGraphQL After Query Execution', $payload['context'] );

			EventManager::publish(
				Events::AFTER_GRAPHQL_EXECUTION,
				[
					'context' => $payload['context'],
					'level'   => (string) $payload['level']->getName(),
				]
			);
		} catch ( \Throwable $e ) {
			$this->process_application_error( Events::AFTER_GRAPHQL_EXECUTION, $e );
		}
	}

	/**
	 * Before the GraphQL response is returned to the client.
	 *
	 * @hook graphql_return_response
	 *
	 * @param array<mixed>|\GraphQL\Executor\ExecutionResult $filtered_response The filtered response for the GraphQL request.
	 * @param array<mixed>|\GraphQL\Executor\ExecutionResult $response          The response for the GraphQL request.
	 * @param \WPGraphQL\WPSchema                            $schema            The schema object for the root request.
	 * @param string|null                                    $operation         The name of the operation.
	 * @param string                                         $query             The query that GraphQL executed.
	 * @param array<string, mixed>|null                      $variables    Variables passed to your GraphQL query.
	 * @param \WPGraphQL\Request                             $request           Instance of the Request.
	 * @param string|null                                    $query_id          The query id that GraphQL executed.
	 */
	public function log_before_response_returned(array|ExecutionResult $filtered_response, array|ExecutionResult $response, WPSchema $schema, ?string $operation, string $query, ?array $variables, Request $request, ?string $query_id): void {
		try {
			$context = [
				'response'       => $response,
				'schema'         => $schema,
				'operation_name' => $operation,
				'query'          => $query,
				'variables'      => $variables,
				'request'        => $request,
				'query_id'       => $query_id,
			];

			$level   = Level::Info;
			$message = 'WPGraphQL Response';
			$errors  = $this->get_response_errors( $response );
			if ( null !== $errors && count( $errors ) > 0 ) {
				$context['errors'] = $errors;
				$level             = Level::Error;
				$message           = 'WPGraphQL Response with Errors';
			}

			$payload = EventManager::transform(
				Events::BEFORE_RESPONSE_RETURNED,
				[
					'context' => $context,
					'level'   => $level,
				]
			);

			$this->logger->log( $payload['level'], $message, $payload['context'] );

			EventManager::publish(
				Events::BEFORE_RESPONSE_RETURNED,
				[
					'context' => $payload['context'],
					'level'   => (string) $payload['level']->getName(),
				]
			);
		} catch ( \Throwable $e ) {
			$this->process_application_error( Events::BEFORE_RESPONSE_RETURNED, $e );
		}
	}

	/**
	 * Get the context for the response.
	 *
	 * @param array<mixed>|\GraphQL\Executor\ExecutionResult $response The response.
	 *
	 * @return array<mixed>|null
	 */
	protected function get_response_errors( array|ExecutionResult $response ): ?array {
		if ( $response instanceof ExecutionResult && [] !== $response->errors ) {
			return $response->errors;
		}

		if ( ! is_array( $response ) ) {
			return null;
		}

		$errors = $response['errors'] ?? null;
		if ( null === $errors || [] === $errors ) {
			return null;
		}

		return $errors;
	}

	/**
	 * Register actions and filters to log the query event lifecycle.
	 *
	 * @psalm-suppress HookNotFound
	 */
	protected function setup(): void {

		/**
		 * Initial Incoming Request
		 */
		add_action( 'do_graphql_request', [ $this, 'log_pre_request' ], 10, 3 );

		/**
		 * Before Query Execution
		 */
		add_action( 'graphql_before_execute', [ $this, 'log_graphql_before_execute' ], 10, 1 );

		/**
		 * After Query Execution
		 */
		add_action( 'graphql_execute', [ $this, 'log_after_graphql_execute' ], 10, 6 );

		/**
		 * Response/Error Handling
		 */
		add_action( 'graphql_return_response', [ $this, 'log_before_response_returned' ], 10, 8 );
	}

	/**
	 * Processing application error when an exception is thrown.
	 *
	 * @param string     $event The event name.
	 * @param \Throwable $exception The exception.
	 */
	protected function process_application_error(string $event, \Throwable $exception): void {
		error_log( 'Error for WPGraphQL Logging - ' . $event . ': ' . $exception->getMessage() . ' in ' . $exception->getFile() . ' on line ' . $exception->getLine() ); //phpcs:ignore
	}
}
