<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Logger\Database;

use DateTimeInterface;
use WPGraphQL\Logging\Logger\Api\LogStatsInterface;

/**
 * Aggregates log data stored in the WordPress database table for the admin dashboard.
 *
 * All aggregation happens in SQL (COUNT / GROUP BY / LIMIT) and uses the
 * datetime index, so no log rows are loaded into PHP.
 *
 * @package WPGraphQL\Logging
 *
 * @phpcs:disable WordPress.DB.DirectDatabaseQuery
 *
 * @since 1.1.0
 */
class WordPressDatabaseLogStats implements LogStatsInterface {
	/**
	 * The lowest Monolog level that counts as an error (Monolog\Level::Error).
	 *
	 * @var int
	 */
	public const ERROR_LEVEL = 400;

	/**
	 * SQL expression that extracts the GraphQL operation name from the context JSON.
	 *
	 * JSON null and a missing key both resolve to an empty string.
	 *
	 * @var string
	 */
	protected const OPERATION_SQL = "COALESCE(NULLIF(JSON_UNQUOTE(JSON_EXTRACT(context, '$.operation_name')), 'null'), '')";

	/**
	 * {@inheritDoc}
	 */
	public function get_summary( DateTimeInterface $start, DateTimeInterface $end ): array {
		global $wpdb;

		$sql = 'SELECT COUNT(*) AS total,'
			. ' SUM(CASE WHEN level >= %d THEN 1 ELSE 0 END) AS errors,'
			. ' COUNT(DISTINCT NULLIF(' . self::OPERATION_SQL . ", '')) AS operations"
			. ' FROM %i WHERE datetime >= %s AND datetime < %s';

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- The only interpolated value is a class constant.
		$row = $wpdb->get_row( $wpdb->prepare( $sql, self::ERROR_LEVEL, $this->get_table_name(), $this->format_date( $start ), $this->format_date( $end ) ), ARRAY_A );

		return [
			'total'      => (int) ( $row['total'] ?? 0 ),
			'errors'     => (int) ( $row['errors'] ?? 0 ),
			'operations' => (int) ( $row['operations'] ?? 0 ),
		];
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_time_series( DateTimeInterface $start, int $bucket_seconds, int $bucket_count ): array {
		global $wpdb;

		$bucket_seconds = max( 1, $bucket_seconds );
		$bucket_count   = max( 1, $bucket_count );
		$start_date     = $this->format_date( $start );
		$end_date       = gmdate( 'Y-m-d H:i:s', $start->getTimestamp() + ( $bucket_seconds * $bucket_count ) );

		// TIMESTAMPDIFF between two naive DATETIME values is independent of the MySQL session time zone.
		$results = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT FLOOR(TIMESTAMPDIFF(SECOND, %s, datetime) / %d) AS bucket,'
				. ' COUNT(*) AS total,'
				. ' SUM(CASE WHEN level >= %d THEN 1 ELSE 0 END) AS errors'
				. ' FROM %i WHERE datetime >= %s AND datetime < %s'
				. ' GROUP BY bucket ORDER BY bucket',
				$start_date,
				$bucket_seconds,
				self::ERROR_LEVEL,
				$this->get_table_name(),
				$start_date,
				$end_date
			),
			ARRAY_A
		);

		$series = array_fill( 0, $bucket_count, [
			'total'  => 0,
			'errors' => 0,
		] );

		if ( ! is_array( $results ) ) {
			return $series;
		}

		foreach ( $results as $row ) {
			$bucket = (int) $row['bucket'];
			if ( $bucket < 0 || $bucket >= $bucket_count ) {
				continue;
			}
			$series[ $bucket ] = [
				'total'  => (int) $row['total'],
				'errors' => (int) $row['errors'],
			];
		}

		return $series;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_level_breakdown( DateTimeInterface $start, DateTimeInterface $end ): array {
		global $wpdb;

		$results = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT level, level_name, COUNT(*) AS total FROM %i'
				. ' WHERE datetime >= %s AND datetime < %s'
				. ' GROUP BY level, level_name ORDER BY level DESC',
				$this->get_table_name(),
				$this->format_date( $start ),
				$this->format_date( $end )
			),
			ARRAY_A
		);

		if ( ! is_array( $results ) ) {
			return [];
		}

		return array_map(
			static fn ( array $row ): array => [
				'level'      => (int) $row['level'],
				'level_name' => (string) $row['level_name'],
				'total'      => (int) $row['total'],
			],
			$results
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_top_operations( DateTimeInterface $start, DateTimeInterface $end, int $limit = 10 ): array {
		global $wpdb;

		$sql = 'SELECT ' . self::OPERATION_SQL . ' AS operation,'
			. ' COUNT(*) AS total,'
			. ' SUM(CASE WHEN level >= %d THEN 1 ELSE 0 END) AS errors'
			. ' FROM %i WHERE datetime >= %s AND datetime < %s'
			. ' GROUP BY operation ORDER BY total DESC, operation ASC LIMIT %d';

		$results = $wpdb->get_results(
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- The only interpolated value is a class constant.
			$wpdb->prepare( $sql, self::ERROR_LEVEL, $this->get_table_name(), $this->format_date( $start ), $this->format_date( $end ), max( 1, $limit ) ),
			ARRAY_A
		);

		if ( ! is_array( $results ) ) {
			return [];
		}

		return array_map(
			static fn ( array $row ): array => [
				'operation' => (string) $row['operation'],
				'total'     => (int) $row['total'],
				'errors'    => (int) $row['errors'],
			],
			$results
		);
	}

	/**
	 * Gets the log table name.
	 */
	protected function get_table_name(): string {
		return WordPressDatabaseEntity::get_table_name();
	}

	/**
	 * Formats a date as a UTC MySQL DATETIME string.
	 *
	 * @param \DateTimeInterface $date The date to format.
	 */
	protected function format_date( DateTimeInterface $date ): string {
		return gmdate( 'Y-m-d H:i:s', $date->getTimestamp() );
	}
}
