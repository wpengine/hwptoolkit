<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Logger\Database;

/**
 * LogsRepository class for WPGraphQL Logging.
 *
 * This class handles the retrieval of log entries from the database.
 *
 * @package WPGraphQL\Logging
 *
 * @since 0.0.1
 */
class LogsRepository {
	/**
	 * @param array<string, mixed> $args
	 *
	 * @return array<\WPGraphQL\Logging\Logger\Database\DatabaseEntity>
	 */
	public function get_logs(array $args = []): array {
		global $wpdb;
		$defaults = [
			'number'  => 100,
			'offset'  => 0,
			'orderby' => 'id',
			'order'   => 'DESC',
			'where'   => [],
		];
		$args     = wp_parse_args( $args, $defaults );

		$orderby = $args['orderby'];
		if ( ! is_string( $orderby ) || '' === $orderby ) {
			$orderby = $defaults['orderby'];
		}
		$order = $args['order'];
		if ( ! is_string( $order ) || '' === $order ) {
			$order = $defaults['order'];
		}
		$where = $args['where'];
		if ( ! is_array( $where ) ) {
			$where = $defaults['where'];
		}

		$limit  = absint( $args['number'] );
		$offset = absint( $args['offset'] );


		return DatabaseEntity::find_logs( $limit, $offset, $where, $orderby, $order );
	}

	/**
	 * Get the total number of log entries.
	 *
	 * @param array<string> $where_clauses Array of where clauses to filter the count.
	 *
	 * @phpcs:disable WordPress.DB.DirectDatabaseQuery.NoCaching
	 *
	 * @return int The total number of log entries.
	 */
	public function get_log_count(array $where_clauses): int {
		global $wpdb;
		$table_name = DatabaseEntity::get_table_name();

		if ( empty( $where_clauses ) ) {
			return (int) $wpdb->get_var( $wpdb->prepare( // @phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
				'SELECT COUNT(*) FROM %i',
				$table_name
			) );
		}

		$where = '';
		foreach ( $where_clauses as $clause ) {
			if ( '' !== $where ) {
				$where .= ' AND ';
			}
			$where .= (string) $clause;
		}

		return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table_name} WHERE {$where}" ); // @phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	}

	/**
	 * Get a single log entry by ID.
	 *
	 * @param int $id The log entry ID.
	 *
	 * @return ?\WPGraphQL\Logging\Logger\Database\DatabaseEntity The log entry or null if not found.
	 */
	public function get_log( int $id ): ?DatabaseEntity {
		return DatabaseEntity::find_by_id( $id );
	}

	/**
	 * Delete a single log entry by ID.
	 *
	 * @param int $id
	 */
	public function delete(int $id): bool {
		global $wpdb;
		$table_name = DatabaseEntity::get_table_name();

		if ( $id <= 0 ) {
			return false;
		}

		$result = $wpdb->delete( $table_name, [ 'id' => $id ], [ '%d' ] ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return false !== $result;
	}

	/**
	 * Delete all log entries.
	 *
	 * @return bool True if all logs were deleted successfully, false otherwise.
	 */
	public function delete_all(): bool {
		global $wpdb;
		$table_name = DatabaseEntity::get_table_name();
		$result     = $wpdb->query( $wpdb->prepare( 'TRUNCATE TABLE %s', $table_name ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		return false !== $result;
	}
}
