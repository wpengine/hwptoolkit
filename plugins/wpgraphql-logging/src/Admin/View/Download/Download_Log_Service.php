<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Admin\View\Download;

use League\Csv\Writer;
use WPGraphQL\Logging\Logger\Database\LogsRepository;

/**
 * Service for handling log downloads.
 *
 * @package WPGraphQL\Logging
 *
 * @since 0.0.1
 */
class Download_Log_Service {
	/**
	 * Generates and serves a CSV file for a single log entry.
	 *
	 * @param int $log_id The ID of the log to download.
	 */
	public function generate_csv( int $log_id ): void {
		if ( ! current_user_can( 'manage_options' ) || ! is_admin() ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'wpgraphql-logging' ) );
		}

		if ( 0 === $log_id ) {
			wp_die( esc_html__( 'Invalid log ID.', 'wpgraphql-logging' ) );
		}

		$repository = new LogsRepository();
		$log        = $repository->get_log( $log_id );
		if ( is_null( $log ) ) {
			wp_die( esc_html__( 'Log not found.', 'wpgraphql-logging' ) );
		}

		// Set headers for CSV download.
		$filename = apply_filters( 'wpgraphql_logging_csv_filename', 'graphql_log_' . $log_id . '.csv' );
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
		header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
		header( 'Expires: 0' );

		// Create CSV.
		$output = fopen( 'php://output', 'w' );
		if ( ! is_resource( $output ) ) {
			wp_die( esc_html__( 'Failed to create CSV output.', 'wpgraphql-logging' ) );
		}
		$writer = Writer::createFromStream( $output );

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


		$headers = apply_filters( 'wpgraphql_logging_csv_headers', $headers, $log_id, $log );
		$content = apply_filters( 'wpgraphql_logging_csv_content', $content, $log_id, $log );
		$writer->insertOne( $headers );
		$writer->insertOne( $content );
		fclose( $output );
		exit;
	}
}
