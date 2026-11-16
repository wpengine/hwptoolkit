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
	 * Headers that carry credentials. Their values are replaced with '***'.
	 *
	 * @var array<string>
	 */
	private const REDACTED_HEADERS = [ 'authorization', 'proxy-authorization', 'cookie', 'x-wp-nonce' ];

	/**
	 * Retrieves the request headers from the $_SERVER superglobal.
	 *
	 * @return array<string, mixed> The request headers.
	 */
	private function get_headers(): array {
		$headers = [];
		foreach ( $_SERVER as $key => $value ) {
			// Only real request headers; skips server fields such as PHP_AUTH_PW.
			if ( ! str_starts_with( $key, 'HTTP_' ) || ! is_string( $value ) || empty( $value ) ) {
				continue;
			}
			$header_name            = str_replace( '_', '-', substr( $key, 5 ) );
			$header_key             = ucwords( strtolower( sanitize_text_field( $header_name ) ), '-' );
			$headers[ $header_key ] = in_array( strtolower( $header_key ), self::REDACTED_HEADERS, true )
				? '***'
				: sanitize_text_field( $value );
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
