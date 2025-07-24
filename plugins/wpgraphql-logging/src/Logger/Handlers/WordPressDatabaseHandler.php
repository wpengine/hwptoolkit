<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Logger\Handlers;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\LogRecord;
use Throwable;
use WPGraphQL\Logging\Logger\Database\DatabaseEntity;

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
			$name   = $record->level->getName();
			$entity = DatabaseEntity::create(
				$record->channel,
				$record->level->value,
				(is_string($name) && $name !== '') ? $name : 'INFO',
				$record->message,
				$record->context ?: [],
				$record->extra ?: []
			);

			$entity->save();
		} catch ( Throwable $e ) {
			error_log( 'Error logging to WordPress database: ' . $e->getMessage() ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		}
	}
}
