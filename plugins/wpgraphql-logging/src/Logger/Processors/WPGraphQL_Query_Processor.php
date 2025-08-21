<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Logger\Processors;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;

/**
 * This class is responsible for capturing and processing GraphQL query data
 * for logging purposes.
 *
 * @package WPGraphQL\Logging
 *
 * @since 0.0.1
 */
class WPGraphQL_Query_Processor implements ProcessorInterface {
	/**
	 * The GraphQL query string for the current request.
	 *
	 * @var string|null
	 */
	protected static ?string $query = null;

	/**
	 * The variables for the current GraphQL request.
	 *
	 * @var array<string, mixed>|null
	 */
	protected static ?array $variables = null;

	/**
	 * The operation name for the current GraphQL request.
	 *
	 * @var string|null
	 */
	protected static ?string $operation_name = null;

	/**
	 * Constructor for the WPGraphQL_Query_Processor.
	 *
	 * This constructor sets up the necessary hooks to capture GraphQL request data and clear it after the request is completed.
	 */
	public function __construct() {
		/**
		 * @psalm-suppress HookNotFound
		 */
		add_action( 'graphql_request_data', [ self::class, 'capture_request_data' ], 10, 1 ); // @phpstan-ignore-line

		/**
		 * @psalm-suppress HookNotFound
		 */
		add_action( 'graphql_process_http_request_response', [ self::class, 'clear_request_data' ], 999, 0 );
	}

	/**
	 * Captures the GraphQL request data.
	 *
	 * @param array<string, mixed> $request_data The raw request data from WPGraphQL.
	 *
	 * @return array<string, mixed> The raw request data from WPGraphQL.
	 */
	public static function capture_request_data(array $request_data): array {
		self::$query          = $request_data['query'] ?? null;
		self::$variables      = $request_data['variables'] ?? null;
		self::$operation_name = $request_data['operationName'] ?? null;

		return $request_data;
	}

	/**
	 * Clears the stored GraphQL request data to ensure it does not persist across requests.
	 */
	public static function clear_request_data(): void {
		self::$query          = null;
		self::$variables      = null;
		self::$operation_name = null;
	}

	/**
	 * This method is called for each log record. It adds the captured
	 * GraphQL data to the record's 'extra' array.
	 *
	 * @param \Monolog\LogRecord $record The log record to process.
	 *
	 * @return \Monolog\LogRecord The processed log record.
	 */
	public function __invoke(LogRecord $record): LogRecord {
		if ( null !== self::$query ) {
			$record->extra['wpgraphql_query'] = self::$query;
		}
		if ( null !== self::$operation_name ) {
			$record->extra['wpgraphql_operation_name'] = self::$operation_name;
		}
		if ( null !== self::$variables ) {
			$record->extra['wpgraphql_variables'] = self::$variables;
		}

		return $record;
	}
}
