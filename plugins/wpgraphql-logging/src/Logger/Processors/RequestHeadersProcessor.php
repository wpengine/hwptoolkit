<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Logger\Processors;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;

/**
 * This class is responsible for capturing and processing request headers
 * for logging purposes.
 *
 * @package WPGraphQL\Logging
 *
 * @since 0.0.1
 */
class RequestHeadersProcessor implements ProcessorInterface {
	/**
	 * Retrieves the request headers from the $_SERVER superglobal.
	 *
	 * @return array<string, mixed> The request headers.
	 */
	private function get_headers(): array {
		$headers = [];
		foreach ( $_SERVER as $key => $value ) {
			if ( ! is_string( $value ) || empty( $value ) ) {
				continue;
			}
			$header_key             = substr( $key, 5 );
			$header_key             = str_replace( '_', '-', $header_key );
			$header_key             = ucwords( strtolower( sanitize_text_field( (string) $header_key ) ), '-' );
			$headers[ $header_key ] = sanitize_text_field( $value );
		}

		return $headers;
	}

	/**
	 * This method is called for each log record. It adds the captured
	 * request headers to the record's 'extra' array.
	 *
	 * @param \Monolog\LogRecord $record The log record to process.
	 *
	 * @return \Monolog\LogRecord The processed log record.
	 */
	public function __invoke( LogRecord $record ): LogRecord {
		$record->extra['request_headers'] = $this->get_headers();

		return $record;
	}
}
