<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Admin\View\Download;

use Generator;
use League\Csv\EscapeFormula;
use League\Csv\Writer;
use WPGraphQL\Logging\Logger\Api\LogEntityInterface;
use WPGraphQL\Logging\Logger\Api\LogServiceInterface;

/**
 * Service for handling log downloads.
 *
 * @package WPGraphQL\Logging
 *
 * @since 0.0.1
 */
class DownloadLogService {
	/**
	 * Default maximum number of log entries in a single bulk download.
	 *
	 * @var int
	 */
	public const DEFAULT_BULK_DOWNLOAD_LIMIT = 1000;

	/**
	 * Constructor.
	 *
	 * @param \WPGraphQL\Logging\Logger\Api\LogServiceInterface $log_service The log service.
	 */
	public function __construct(protected readonly LogServiceInterface $log_service) {
	}

	/**
	 * Generates and serves a CSV file for a single log entry.
	 *
	 * @param int $log_id The ID of the log to download.
	 */
	public function generate_csv( int $log_id ): void {
		$this->verify_permissions();

		if ( 0 === $log_id ) {
			wp_die( esc_html__( 'Invalid log ID.', 'wpgraphql-logging' ) );
		}

		$log = $this->log_service->find_entity_by_id( $log_id );

		if ( is_null( $log ) ) {
			wp_die( esc_html__( 'Log not found.', 'wpgraphql-logging' ) );
		}

		$filename = apply_filters( 'wpgraphql_logging_csv_filename', 'graphql_log_' . $log_id . '.csv' );
		$this->stream_csv( (string) $filename, [ $log ] );
	}

	/**
	 * Generates and serves a single CSV file containing multiple log entries.
	 *
	 * Entries are fetched and written one at a time, straight to the output
	 * stream, so memory use stays flat regardless of how many are selected.
	 *
	 * @param array<mixed> $log_ids The IDs of the logs to download.
	 */
	public function generate_bulk_csv( array $log_ids ): void {
		$this->verify_permissions();

		$log_ids = $this->sanitize_log_ids( $log_ids );
		if ( [] === $log_ids ) {
			wp_die( esc_html__( 'No logs selected.', 'wpgraphql-logging' ) );
		}

		$logs = $this->get_logs( $log_ids );

		// Starts the generator, so we can bail before any headers are sent.
		if ( ! $logs->valid() ) {
			wp_die( esc_html__( 'Logs not found.', 'wpgraphql-logging' ) );
		}

		$filename = apply_filters( 'wpgraphql_logging_bulk_csv_filename', 'graphql_logs_' . gmdate( 'Y-m-d_His' ) . '.csv', $log_ids );
		$this->stream_csv( (string) $filename, $logs );
	}

	/**
	 * Sanitizes a list of log IDs for a bulk download.
	 *
	 * Removes non-scalar, invalid and duplicate IDs and caps the list at the bulk download limit.
	 *
	 * @param array<mixed> $log_ids The raw log IDs.
	 *
	 * @return array<int> The sanitized log IDs.
	 */
	public function sanitize_log_ids( array $log_ids ): array {
		// absint() casts arrays to 1, so drop anything that isn't a scalar first.
		$log_ids = array_map( 'absint', array_filter( $log_ids, 'is_scalar' ) );
		$log_ids = array_values( array_unique( array_filter( $log_ids, static fn( int $id ): bool => $id > 0 ) ) );

		return array_slice( $log_ids, 0, $this->get_bulk_download_limit() );
	}

	/**
	 * Gets the maximum number of log entries allowed in a single bulk download.
	 *
	 * @return int The bulk download limit.
	 */
	public function get_bulk_download_limit(): int {
		$limit = (int) apply_filters( 'wpgraphql_logging_bulk_download_limit', self::DEFAULT_BULK_DOWNLOAD_LIMIT );
		return max( 1, $limit );
	}

	/**
	 * Writes log entries as CSV rows to a stream.
	 *
	 * The header row is written once, before the first entry. Cells that a
	 * spreadsheet would treat as a formula (=, +, -, @, ...) are escaped, since
	 * log values can come from unauthenticated GraphQL requests.
	 *
	 * @param resource $stream The stream to write to.
	 * @param iterable $logs   The log entries.
	 *
	 * @phpstan-param iterable<\WPGraphQL\Logging\Logger\Api\LogEntityInterface> $logs
	 *
	 * @return int The number of log entries written.
	 */
	public function write_csv( $stream, iterable $logs ): int {
		$writer = Writer::from( $stream );
		$writer->addFormatter( ( new EscapeFormula() )->escapeRecord( ... ) );
		$count = 0;

		foreach ( $logs as $log ) {
			if ( 0 === $count ) {
				$writer->insertOne( $this->get_headers( $log ) );
			}
			$writer->insertOne( $this->get_content( $log ) );
			++$count;
		}

		return $count;
	}

	/**
	 * Get default CSV headers.
	 *
	 * @param \WPGraphQL\Logging\Logger\Api\LogEntityInterface $log The log entry.
	 *
	 * @return array<string> The default CSV headers.
	 */
	public function get_headers(LogEntityInterface $log): array {
		$headers = [
			'ID',
			'Date',
			'Level',
			'Level Name',
			'Message',
			'Channel',
			'Query',
			'Context',
			'Extra',
		];
		return apply_filters( 'wpgraphql_logging_csv_headers', $headers, $log->get_id(), $log );
	}

	/**
	 * Get CSV content for a log entry.
	 *
	 * @param \WPGraphQL\Logging\Logger\Api\LogEntityInterface $log The log entry.
	 *
	 * @return array<string> The CSV content for the log entry.
	 */
	public function get_content(LogEntityInterface $log): array {
		$content = [
			$log->get_id(),
			$log->get_datetime(),
			$log->get_level(),
			$log->get_level_name(),
			$log->get_message(),
			$log->get_channel(),
			$log->get_query(),
			wp_json_encode( $log->get_context() ),
			wp_json_encode( $log->get_extra() ),
		];
		return apply_filters( 'wpgraphql_logging_csv_content', $content, $log->get_id(), $log );
	}

	/**
	 * Verifies the current user can download logs.
	 */
	protected function verify_permissions(): void {
		if ( ! current_user_can( 'manage_options' ) || ! is_admin() ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'wpgraphql-logging' ) );
		}
	}

	/**
	 * Lazily loads log entries by ID, skipping any that no longer exist.
	 *
	 * @param array<int> $log_ids The log IDs.
	 *
	 * @return \Generator<int, \WPGraphQL\Logging\Logger\Api\LogEntityInterface> The log entries.
	 */
	protected function get_logs( array $log_ids ): Generator {
		foreach ( $log_ids as $log_id ) {
			$log = $this->log_service->find_entity_by_id( $log_id );
			if ( null !== $log ) {
				yield $log;
			}
		}
	}

	/**
	 * Sends download headers and streams log entries to the browser as CSV, then exits.
	 *
	 * @param string   $filename The download filename.
	 * @param iterable $logs     The log entries.
	 *
	 * @phpstan-param iterable<\WPGraphQL\Logging\Logger\Api\LogEntityInterface> $logs
	 */
	protected function stream_csv( string $filename, iterable $logs ): void {
		$filename = sanitize_file_name( $filename );
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
		header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
		header( 'Expires: 0' );

		// Drop any open output buffers so rows go straight to the client instead of piling up in memory.
		while ( ob_get_level() > 0 ) {
			ob_end_clean();
		}

		$output = fopen( 'php://output', 'w' );
		if ( ! is_resource( $output ) ) {
			wp_die( esc_html__( 'Failed to create CSV output.', 'wpgraphql-logging' ) );
		}

		$this->write_csv( $output, $logs );
		fclose( $output ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- Closes the php://output stream, which WP_Filesystem can't handle.
		exit;
	}
}
