<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Logger\Processors;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;
use WPGraphQL\Logging\Events\RequestTimer;

/**
 * Adds the elapsed time of the current WPGraphQL request to each log record.
 *
 * The value is the time in milliseconds between the start of the WPGraphQL
 * request lifecycle and the moment the record is logged. Records written
 * outside of a WPGraphQL request are left untouched.
 *
 * @package WPGraphQL\Logging
 *
 * @since 1.1.0
 */
class RequestDurationProcessor implements ProcessorInterface {
	/**
	 * This method is called for each log record. It adds the elapsed request
	 * time to the record's 'extra' array.
	 *
	 * @param \Monolog\LogRecord $record The log record to process.
	 *
	 * @return \Monolog\LogRecord The processed log record.
	 */
	public function __invoke( LogRecord $record ): LogRecord {
		$elapsed = RequestTimer::get_elapsed_ms();
		if ( null !== $elapsed ) {
			$record->extra[ RequestTimer::DURATION_KEY ] = $elapsed;
		}

		return $record;
	}
}
