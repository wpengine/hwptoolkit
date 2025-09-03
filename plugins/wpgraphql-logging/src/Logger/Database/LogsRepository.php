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
	 * @return array<int, \WPGraphQL\Logging\Logger\Database\DatabaseEntity>
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
		$order   = esc_sql( $args['order'] );
		$limit   = absint( $args['number'] );
		$offset  = absint( $args['offset'] );

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

		if ( false === $count ) {
			global $wpdb;
			$table_name = DatabaseEntity::get_table_name();
			$count      = $wpdb->get_var( $wpdb->prepare( // @phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
				'SELECT COUNT(*) FROM %i',
				$table_name
			) );
			wp_cache_set( $cache_key, $count, '', 300 ); // Cache for 5 minutes.
		}

		return (int) $count;
	}
}
