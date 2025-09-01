<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Events;

use GraphQL\Executor\ExecutionResult;
use Monolog\Level;
use WPGraphQL\Logging\Admin\Settings\Fields\Tab\Basic_Configuration_Tab;
use WPGraphQL\Logging\Logger\LoggerService;
use WPGraphQL\Logging\Logger\LoggingHelper;
use WPGraphQL\Request;

/**
 * Handles logging for GraphQL filters.
 *
 * This class is a dedicated component for listening to and logging data
 * from specific WPGraphQL filter hooks.
 *
 * @package WPGraphQL\Logging
 *
 * @since 0.0.1
 */
class QueryFilterLogger {
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
	 * QueryFilterLogger constructor.
	 *
	 * @param \WPGraphQL\Logging\Logger\LoggerService $logger The logger instance.
	 * @param array<string, mixed>                    $config The logging configuration.
	 */
	public function __construct( LoggerService $logger, array $config ) {
		$this->logger = $logger;
		$this->config = $config;
	}

	/**
	 * Logs and returns the GraphQL request data.
	 *
	 * This method hooks into the `graphql_request_data` filter.
	 *
	 * @param array<string, mixed> $query_data The raw GraphQL request data.
	 *
	 * @return array<string, mixed> The filtered query data.
	 */
	public function log_graphql_request_data( array $query_data ): array {
		try {
			if ( ! $this->is_logging_enabled( $this->config ) ) {
				return $query_data;
			}

			$selected_events = $this->config[ Basic_Configuration_Tab::EVENT_LOG_SELECTION ] ?? [];
			if ( ! in_array( Events::REQUEST_DATA, $selected_events, true ) ) {
				return $query_data;
			}

			$context = [
				'query'          => $query_data['query'] ?? null,
				'variables'      => $query_data['variables'] ?? null,
				'operation_name' => $query_data['operationName'] ?? null,
			];

			$payload = EventManager::transform( Events::REQUEST_DATA, [ 'context' => $context ] );
			$this->logger->log( Level::Info, 'WPGraphQL Request Data', $payload['context'] );
			EventManager::publish( Events::REQUEST_DATA, [ 'context' => $payload['context'] ] );
		} catch ( \Throwable $e ) {
			$this->process_application_error( Events::REQUEST_DATA, $e );
		}

		return $query_data;
	}

	/**
	 * Logs and returns the final GraphQL request results.
	 *
	 * This method hooks into the `graphql_request_results` filter.
	 *
	 * @param array<mixed>|\GraphQL\Executor\ExecutionResult $response    The final GraphQL response.
	 * @param \WPGraphQL\WPSchema                            $schema      The GraphQL schema.
	 * @param string|null                                    $operation   The name of the operation being executed.
	 * @param string|null                                    $query       The raw GraphQL query string.
	 * @param array<mixed>|null                              $variables   The query variables.
	 * @param \WPGraphQL\Request                             $request     The WPGraphQL request instance.
	 * @param string|null                                    $query_id    The unique ID of the query.
	 *
	 * @return array<mixed>|\GraphQL\Executor\ExecutionResult The filtered response.
	 */
	public function log_graphql_request_results(
		array|ExecutionResult $response,
		\WPGraphQL\WPSchema $schema,
		?string $operation,
		?string $query,
		?array $variables,
		Request $request,
		?string $query_id
	): array|ExecutionResult {
		try {
			if ( ! $this->is_logging_enabled( $this->config ) ) {
				return $response;
			}

			$selected_events = $this->config[ Basic_Configuration_Tab::EVENT_LOG_SELECTION ] ?? [];
			if ( ! in_array( Events::REQUEST_RESULTS, $selected_events, true ) ) {
				return $response;
			}

			$context = [
				'response'       => $response,
				'operation_name' => $request->params->operation,
				'query'          => $request->params->query,
				'variables'      => $request->params->variables,
				'request'        => $request,
				'query_id'       => $query_id,
			];

			$level   = Level::Info;
			$message = 'WPGraphQL Response';
			if ( isset( $response['errors'] ) && ! empty( $response['errors'] ) ) {
				$context['errors'] = $response['errors'];
				$level             = Level::Error;
				$message           = 'WPGraphQL Response with Errors';
			}

			$payload = EventManager::transform( Events::REQUEST_RESULTS, [
				'context' => $context,
				'level'   => $level,
			] );
			$this->logger->log( $payload['level'], $message, $payload['context'] );
			EventManager::publish( Events::REQUEST_RESULTS, [ 'context' => $payload['context'] ] );
		} catch ( \Throwable $e ) {
			$this->process_application_error( Events::REQUEST_RESULTS, $e );
		}

		return $response;
	}

	/**
	 * Adds a unique logging ID to the GraphQL response headers.
	 *
	 * This method hooks into the `graphql_response_headers_to_send` filter.
	 *
	 * @param array<string, string> $headers The array of response headers.
	 *
	 * @return array<string, string> The filtered array of headers.
	 */
	public function add_logging_headers( array $headers ): array {
		if ( ! $this->is_logging_enabled( $this->config ) ) {
			return $headers;
		}

		$request_id                        = uniqid( 'wpgql_log_' );
		$headers['X-WPGraphQL-Logging-ID'] = $request_id;

		return $headers;
	}

	/**
	 * Handles and logs application errors.
	 *
	 * @param string     $event The name of the event where the error occurred.
	 * @param \Throwable $exception The exception that was caught.
	 */
	protected function process_application_error( string $event, \Throwable $exception ): void {
        error_log( 'Error for WPGraphQL Logging - ' . $event . ': ' . $exception->getMessage() . ' in ' . $exception->getFile() . ' on line ' . $exception->getLine() ); //phpcs:ignore
	}
}
