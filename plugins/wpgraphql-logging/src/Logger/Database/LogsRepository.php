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
		];
		$args     = wp_parse_args( $args, $defaults );

		$orderby = esc_sql( $args['orderby'] );
		if ( ! is_string( $orderby ) || '' === $orderby ) {
			$orderby = $defaults['orderby'];
		}
		$order = esc_sql( $args['order'] );
		if ( ! is_string( $order ) || '' === $order ) {
			$order = $defaults['order'];
		}
		$limit  = absint( $args['number'] );
		$offset = absint( $args['offset'] );

		return DatabaseEntity::find_logs( $limit, $offset, $orderby, $order );
	}

	/**
	 * Get the total number of log entries.
	 *
	 * @return int The total number of log entries.
	 */
	public function get_log_count(): int {
		$cache_key = 'wpgraphql_logs_count';
		$count     = wp_cache_get( $cache_key );

		if ( is_int( $count ) ) {
			return $count;
		}

		global $wpdb;
		$table_name = DatabaseEntity::get_table_name();
		$count      = $wpdb->get_var( $wpdb->prepare( // @phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			'SELECT COUNT(*) FROM %i',
			$table_name
		) );
		wp_cache_set( $cache_key, $count, '', 300 );

		return (int) $count;
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
