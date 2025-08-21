<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Logger\Handlers;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\LogRecord;
use Throwable;
use WPGraphQL\Logging\Logger\Database\Database_Entity;

/**
 * WordPress Database Handler for Monolog
 *
 * @package WPGraphQL\Logging
 *
 * @since 0.0.1
 */
class WordPress_Database_Handler extends AbstractProcessingHandler {
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
			$entity = Database_Entity::create(
				$record->channel,
				$record->level->value,
				$this->get_record_name( $record ),
				$record->message,
				$record->context ?? [],
				$record->extra ?? []
			);

			$entity->save();
		} catch ( Throwable $e ) {
			error_log( 'Error logging to WordPress database: ' . $e->getMessage() ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
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
