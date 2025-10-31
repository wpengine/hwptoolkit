<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Events;

use GraphQL\Executor\ExecutionResult;
use Monolog\Level;
use WPGraphQL\Logging\Logger\LoggerService;
use WPGraphQL\Logging\Logger\LoggingHelper;
use WPGraphQL\Request;
use WPGraphQL\WPSchema;

/**
 * Handles logging for GraphQL actions.
 *
 * This class is a dedicated component for listening to and logging data
 * from specific WPGraphQL action hooks.
 *
 * @package WPGraphQL\Logging
 *
 * @since 0.0.1
 */
class QueryActionLogger {
	use LoggingHelper;

	/**
	 * The logger service instance.
	 *
	 * @var \WPGraphQL\Logging\Logger\LoggerService
	 */
	protected LoggerService $logger;

	/**
	 * The basic configuration settings.
	 *
	 * @var array<string, string|int|bool|array<string>>
	 */
	protected array $config;

	/**
	 * QueryActionLogger constructor.
	 *
	 * @param \WPGraphQL\Logging\Logger\LoggerService $logger The logger instance.
	 * @param array<string, mixed>                    $config The logging configuration.
	 */
	public function __construct( LoggerService $logger, array $config ) {
		$this->logger = $logger;
		$this->config = $config;
	}

	/**
	 * Initial Incoming Request.
	 *
	 * This method hooks into the `do_graphql_request` action.
	 *
	 * @param string|null               $query
	 * @param string|null               $operation_name
	 * @param array<string, mixed>|null $variables
	 */
	public function log_pre_request( ?string $query, ?string $operation_name, ?array $variables ): void {
		try {
			if ( ! $this->should_log_event( Events::PRE_REQUEST, $query ) ) {
				return;
			}

			$context = [
				'query'          => $query,
				'variables'      => $variables,
				'operation_name' => $operation_name,
			];
			$payload = EventManager::transform( Events::PRE_REQUEST, [
				'context' => $context,
				'level'   => Level::Info,
			] );
			$this->logger->log( $payload['level'], 'WPGraphQL Pre Request', $payload['context'] );
			EventManager::publish( Events::PRE_REQUEST, [ 'context' => $payload['context'] ] );
		} catch ( \Throwable $e ) {
			$this->process_application_error( Events::PRE_REQUEST, $e );
		}
	}

	/**
	 * Before Request Execution.
	 *
	 * This method hooks into the `graphql_before_execute` action.
	 *
	 * @param \WPGraphQL\Request $request
	 */
	public function log_graphql_before_execute( Request $request ): void {
		try {
			/** @var \GraphQL\Server\OperationParams|null $params */
			$params = $request->params;
			if ( ! is_object( $params ) ) {
				return;
			}
			$query = $params->query;
			if ( ! $this->should_log_event( Events::BEFORE_GRAPHQL_EXECUTION, $query ) ) {
				return;
			}

			$context = [
				'query'          => $query,
				'operation_name' => $params->operation,
				'variables'      => $params->variables,
				'params'         => $params,
			];

			$payload = EventManager::transform( Events::BEFORE_GRAPHQL_EXECUTION, [
				'context' => $context,
				'level'   => Level::Info,
			] );
			$this->logger->log( $payload['level'], 'WPGraphQL Before Query Execution', $payload['context'] );
			EventManager::publish( Events::BEFORE_GRAPHQL_EXECUTION, [ 'context' => $payload['context'] ] );
		} catch ( \Throwable $e ) {
			$this->process_application_error( Events::BEFORE_GRAPHQL_EXECUTION, $e );
		}
	}

	/**
	 * Before the GraphQL response is returned to the client.
	 *
	 * This method hooks into the `graphql_return_response` action.
	 *
	 * @param array<mixed>|\GraphQL\Executor\ExecutionResult $filtered_response
	 * @param array<mixed>|\GraphQL\Executor\ExecutionResult $response
	 * @param \WPGraphQL\WPSchema                            $schema
	 * @param string|null                                    $operation
	 * @param string|null                                    $query
	 * @param array<string, mixed>|null                      $variables
	 * @param \WPGraphQL\Request                             $request
	 * @param string|null                                    $query_id
	 *
	 * @phpcs:disable Generic.Metrics.CyclomaticComplexity.TooHigh
	 * @phpcs:disable SlevomatCodingStandard.Complexity.Cognitive.ComplexityTooHigh
	 */
	public function log_before_response_returned(
		array|ExecutionResult $filtered_response,
		array|ExecutionResult $response,
		WPSchema $schema,
		?string $operation,
		?string $query,
		?array $variables,
		Request $request,
		?string $query_id
	): void {
		try {
			if ( ! $this->should_log_event( Events::BEFORE_RESPONSE_RETURNED, $query ) ) {
				return;
			}

			$encoded_request = wp_json_encode( $request );
			$context         = [
				'response'       => $response,
				'schema'         => $schema,
				'operation_name' => $operation,
				'query'          => $query,
				'variables'      => $variables,
				'request'        => false !== $encoded_request ? json_decode( $encoded_request, true ) : null,
				'query_id'       => $query_id,
			];
			if ( ! $this->should_log_response( $this->config ) ) {
				unset( $context['response'] );
			}
			$level   = Level::Info;
			$message = 'WPGraphQL Response';
			$errors  = $this->get_response_errors( $response );
			if ( null !== $errors && count( $errors ) > 0 ) {
				$context['errors'] = $errors;
				$level             = Level::Error;
				$message           = 'WPGraphQL Response with Errors';
			}
			$payload = EventManager::transform( Events::BEFORE_RESPONSE_RETURNED, [
				'context' => $context,
				'level'   => $level,
			] );
			$this->logger->log( $payload['level'], $message, $payload['context'] );
			EventManager::publish( Events::BEFORE_RESPONSE_RETURNED, [ 'context' => $payload['context'] ] );
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
}
