<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Logger\Handlers;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\LogRecord;
use Throwable;
use WPGraphQL\Logging\Logger\Store\LogStoreService;

/**
 * WordPress Database Handler for Monolog
 *
 * @package WPGraphQL\Logging
 *
 * @since 0.0.1
 */
class WordPressDatabaseHandler extends AbstractProcessingHandler {
	/**
	 * Writes the log record to the database.
	 *
	 * This is the core method of the handler. It gets called by Monolog
	 * for each log record that needs to be processed.
	 *
	 * @param \Monolog\LogRecord $record The log record containing all log data.
	 */
	protected function write( LogRecord $record ): void {
		try {
			$log_service = LogStoreService::get_log_service();
			$log_service->create_log_entity( // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid
				$record->channel,
				$record->level->value,
				$this->get_record_name( $record ),
				$record->message,
				$record->context ?? [],
				$record->extra ?? []
			);
		} catch ( Throwable $e ) {
			do_action( 'wpgraphql_logging_write_database_error', $e, $record );
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( 'Error logging to WordPress database: ' . $e->getMessage() ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			}
		}
	}

	/**
	 * Gets the name of the log record.
	 *
	 * @param \Monolog\LogRecord $record The log record.
	 *
	 * @return string The name of the log record.
	 */
	protected function get_record_name( LogRecord $record ): string {

		/**
		* @psalm-suppress InvalidCast
		*/
		$name    = (string) $record->level->getName(); // @phpstan-ignore-line
		$default = 'INFO';

		return $name ?: $default; // @phpstan-ignore-line
	}
}
