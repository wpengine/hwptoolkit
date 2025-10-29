<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Logger\Database;

use DateTime;
use WPGraphQL\Logging\Logger\Api\LogEntityInterface;
use WPGraphQL\Logging\Logger\Api\LogServiceInterface;

/**
 * WordPress Database Log Service for Monolog.
 *
 * @package WPGraphQL\Logging
 *
 * @phpcs:disable WordPress.DB.DirectDatabaseQuery
 *
 * @since 0.0.1
 */
class WordPressDatabaseLogService implements LogServiceInterface {
	/**
	 * The values for the where clause.
	 *
	 * @var array<string>
	 */
	protected array $where_values = [];

	/**
	 * Creates a new log entity.
	 *
	 * @param string       $channel The channel for the log entry.
	 * @param int          $level The logging level.
	 * @param string       $level_name The name of the logging level.
	 * @param string       $message The log message.
	 * @param array<mixed> $context Additional context for the log entry.
	 * @param array<mixed> $extra Extra data for the log entry.
	 *
	 * @return \WPGraphQL\Logging\Logger\Api\LogEntityInterface|null The created log entity, or null on failure.
	 */
	public function create_log_entity(string $channel, int $level, string $level_name, string $message, array $context = [], array $extra = []): ?LogEntityInterface {
		$entity = new WordPressDatabaseEntity( $channel, $level, $level_name, $message, $context, $extra );
		$entity->save();
		return $entity->get_id() > 0 ? $entity : null;
	}

	/**
	 * Finds a log entity by ID.
	 *
	 * @param int $id The ID of the log entity.
	 *
	 * @return \WPGraphQL\Logging\Logger\Api\LogEntityInterface|null The found log entity, or null on failure.
	 */
	public function find_entity_by_id(int $id): ?LogEntityInterface {
		global $wpdb;
		$table_name = $this->get_table_name();

		$query = $wpdb->prepare( "SELECT * FROM {$table_name} WHERE id = %d", $id ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$row   = $wpdb->get_row( $query, ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared

		if ( ! $row ) {
			return null;
		}

		return WordPressDatabaseEntity::from_array( $row );
	}

	/**
	 * Finds log entities by a where clause.
	 *
	 * @param array<string, mixed> $args The arguments for the where clause.
	 *
	 * @return array<\WPGraphQL\Logging\Logger\Api\LogEntityInterface> The found log entities.
	 */
	public function find_entities_by_where(array $args = []): array {
		global $wpdb;

		$this->where_values   = [];
		$sql                  = 'SELECT * FROM %i';
		$this->where_values[] = sanitize_text_field( $this->get_table_name() );
		if ( isset( $args['where'] ) && is_array( $args['where'] ) ) {
			$sql = $this->prepare_sql( $sql, $args['where'] );
		}

		$orderby = $args['orderby'] ?? 'id';
		$order   = $args['order'] ?? 'DESC';
		$limit   = $args['number'] ?? 100;
		$offset  = $args['offset'] ?? 0;

		$sql                 .= " ORDER BY $orderby $order LIMIT %d, %d";
		$this->where_values[] = (string) $offset;
		$this->where_values[] = (string) $limit;

		$results = $wpdb->get_results( $wpdb->prepare( $sql, $this->where_values ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		if ( empty( $results ) || ! is_array( $results ) ) {
			return [];
		}

		return array_map(
			static function (array $row) {
				return WordPressDatabaseEntity::from_array( $row );
			},
			$results
		);
	}

	/**
	 * Deletes a log entity by ID.
	 *
	 * @param int $id The ID of the log entity.
	 *
	 * @return bool True if the log entity was deleted, false otherwise.
	 */
	public function delete_entity_by_id(int $id): bool {
		global $wpdb;
		$table_name = $this->get_table_name();

		if ( $id <= 0 ) {
			return false;
		}

		$result = $wpdb->delete( $table_name, [ 'id' => $id ], [ '%d' ] );
		return false !== $result;
	}

	/**
	 * Deletes log entities older than a specific date.
	 *
	 * @param \DateTime $date The date to delete log entities older than.
	 *
	 * @return bool True if the log entities were deleted, false otherwise.
	 */
	public function delete_entities_older_than(DateTime $date): bool {
		global $wpdb;
		$table_name = $this->get_table_name();

		$result = $wpdb->query( $wpdb->prepare(
			'DELETE FROM %i WHERE datetime < %s',
			$table_name,
			$date->format( 'Y-m-d H:i:s' )
		) );
		return false !== $result;
	}

	/**
	 * Deletes all log entities.
	 *
	 * @return bool True if the log entities were deleted, false otherwise.
	 */
	public function delete_all_entities(): bool {
		global $wpdb;
		$table_name = $this->get_table_name();
		$result     = $wpdb->query( $wpdb->prepare( 'DELETE FROM %i', $table_name ) );
		return false !== $result;
	}

	/**
	 * Counts the number of log entities by a where clause.
	 *
	 * @param array<string, mixed> $args The arguments for the where clause.
	 *
	 * @return int The number of log entities.
	 */
	public function count_entities_by_where(array $args = []): int {
		global $wpdb;
		$this->where_values = [];
		$sql                = 'SELECT COUNT(*) FROM %i';
		$this->where_values = [ $this->get_table_name() ];
		$sql                = $this->prepare_sql( $sql, $args );
		// @TODO - Fix this.
		return (int) $wpdb->get_var( $wpdb->prepare( $sql, $this->where_values ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}

	/**
	 * Gets the table name.
	 */
	protected function get_table_name(): string {
		return WordPressDatabaseEntity::get_table_name();
	}

	/**
	 * Prepares the SQL query.
	 *
	 * @param string                                                        $sql The SQL query template.
	 * @param array<array{column: string, operator: string, value: string}> $where_conditions The where conditions.
	 *
	 * @phpcs:disable SlevomatCodingStandard.Complexity.Cognitive.ComplexityTooHigh
	 * @phpcs:disable Generic.Metrics.CyclomaticComplexity.TooHigh
	 *
	 * @return string The prepared SQL query.
	 */
	protected function prepare_sql(string $sql, array $where_conditions): string {
		$where_clauses  = [];
		$safe_operators = $this->get_safe_operators();
		foreach ( $where_conditions as $column => $condition ) {
			if ( ! is_array( $condition ) || ! isset( $condition['column'] ) || ! isset( $condition['value'] ) || ! isset( $condition['operator'] ) ) {
				continue;
			}

			$column = $condition['column'];
			if ( '' === $column ) {
				continue;
			}
			$value    = $condition['value'];
			$operator = $condition['operator'];
			if ( ! in_array( $operator, $safe_operators, true ) ) {
				continue;
			}

			$where_clauses[]      = "%i $operator %s";
			$this->where_values[] = sanitize_text_field( (string) $column );
			$this->where_values[] = sanitize_text_field( (string) $value );
		}

		if ( ! empty( $where_clauses ) ) {
			$sql .= ' WHERE ' . implode( ' AND ', $where_clauses );
		}

		return $sql;
	}

	/**
	 * The safe operators.
	 *
	 * @return array<string> The safe operators.
	 */
	protected function get_safe_operators(): array {
		return [ '=', '!=', '>', '<', '>=', '<=', 'LIKE', 'NOT LIKE', 'IN', 'NOT IN', 'BETWEEN', 'NOT BETWEEN' ];
	}
}
