<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Logger\Database;

/**
 * Entity class for the custom database table for Monolog.
 *
 * This class represents a single log entry in the database and provides methods to create, save, and manage log entries.
 *
 * @package WPGraphQL\Logging
 *
 * @since 0.0.1
 */
class DatabaseEntity {
	/**
	 * The ID of the log entry. Null if the entry is not yet saved.
	 *
	 * @var int|null
	 */
	protected ?int $id = null;

	/**
	 * The channel for the log entry.
	 *
	 * @var string
	 */
	protected string $channel = '';

	/**
	 * The logging level.
	 *
	 * @var int
	 */
	protected int $level = 0;

	/**
	 * The name of the logging level.
	 *
	 * @var string
	 */
	protected string $level_name = '';

	/**
	 * The log message.
	 *
	 * @var string
	 */
	protected string $message = '';

	/**
	 * Additional context for the log entry.
	 *
	 * @var array<mixed>
	 */
	protected array $context = [];

	/**
	 * Extra data for the log entry.
	 *
	 * @var array<mixed>
	 */
	protected array $extra = [];

	/**
	 * The datetime of the log entry.
	 *
	 * @var string
	 */
	protected string $datetime = '';

	/**
	 * The constructor is protected to encourage creation via static methods.
	 */
	protected function __construct() {
		// Set a default datetime for new, unsaved entries.
		$this->datetime = current_time( 'mysql', 1 );
	}

	/**
	 * Creates a new, unsaved log entry instance.
	 *
	 * @param string       $channel The channel for the log entry.
	 * @param int          $level The logging level.
	 * @param string       $level_name The name of the logging level.
	 * @param string       $message The log message.
	 * @param array<mixed> $context Additional context for the log entry.
	 * @param array<mixed> $extra Extra data for the log entry.
	 */
	public static function create(string $channel, int $level, string $level_name, string $message, array $context = [], array $extra = []): self {
		$entity             = new self();
		$entity->channel    = self::sanitize_text_field( $channel );
		$entity->level      = $level;
		$entity->level_name = self::sanitize_text_field( $level_name );
		$entity->message    = self::sanitize_text_field( $message );
		$entity->context    = self::sanitize_array_field( $context );
		$entity->extra      = self::sanitize_array_field( $extra );

		return $entity;
	}

	/**
	 * Finds a single log entry by its ID and returns it as an object.
	 *
	 * @param int $id The ID of the log entry to find.
	 *
	 * @return self|null Returns an instance of DatabaseEntity if found, or null if not found.
	 */
	public static function find_by_id(int $id): ?self {
		global $wpdb;
		$table_name = self::get_table_name();

		$query = $wpdb->prepare( "SELECT * FROM {$table_name} WHERE id = %d", $id ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$row   = $wpdb->get_row( $query, ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared

		if ( ! $row ) {
			return null;
		}

		return self::create_from_db_row( $row );
	}

	/**
	 * Helper to populate an instance from a database row.
	 *
	 * @param array<string, mixed> $row The database row to populate from.
	 *
	 * @return self The populated instance.
	 */
	public static function create_from_db_row(array $row): self {
		$log             = new self();
		$log->id         = (int) $row['id'];
		$log->channel    = $row['channel'];
		$log->level      = (int) $row['level'];
		$log->level_name = $row['level_name'];
		$log->message    = $row['message'];
		$log->context    = ( isset( $row['context'] ) && '' !== $row['context'] ) ? json_decode( $row['context'], true ) : [];
		$log->extra      = ( isset( $row['extra'] ) && '' !== $row['extra'] ) ? json_decode( $row['extra'], true ) : [];
		$log->datetime   = $row['datetime'];
		return $log;
	}

	/**
	 * Saves a new logging entity to the database. This is an insert-only operation.
	 *
	 * @return int The ID of the newly created log entry, or 0 on failure.
	 */
	public function save(): int {
		global $wpdb;
		$table_name = self::get_table_name();

		$data = [
			'channel'    => $this->channel,
			'level'      => $this->level,
			'level_name' => $this->level_name,
			'message'    => $this->message,
			'context'    => wp_json_encode( $this->context ),
			'extra'      => wp_json_encode( $this->extra ),
			'datetime'   => $this->datetime,
		];

		$formats = [ '%s', '%d', '%s', '%s', '%s', '%s', '%s' ];

		$result = $wpdb->insert( $table_name, $data, $formats ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery

		if ( $result ) {
			$this->id = (int) $wpdb->insert_id;
			return $this->id;
		}

		return 0;
	}

	/**
	 * Gets the ID of the log entry.
	 */
	public function get_id(): int {
		return (int) $this->id;
	}

	/**
	 * Gets the channel of the log entry.
	 */
	public function get_channel(): string {
		return $this->channel;
	}

	/**
	 * Gets the logging level of the log entry.
	 */
	public function get_level(): int {
		return $this->level;
	}

	/**
	 * Gets the name of the logging level of the log entry.
	 */
	public function get_level_name(): string {
		return $this->level_name;
	}

	/**
	 * Gets the message of the log entry.
	 *
	 * @return string The message of the log entry.
	 */
	public function get_message(): string {
		return $this->message;
	}

	/**
	 * Gets the context of the log entry.
	 *
	 * @return array<string, mixed> The context of the log entry.
	 */
	public function get_context(): array {
		return $this->context;
	}

	/**
	 * Gets the extra data of the log entry.
	 *
	 * @return array<string, mixed> The extra data of the log entry.
	 */
	public function get_extra(): array {
		return $this->extra;
	}

	/**
	 * Gets the datetime of the log entry.
	 *
	 * @return string The datetime of the log entry in MySQL format.
	 */
	public function get_datetime(): string {
		return $this->datetime;
	}

	/**
	 * Extracts and returns the GraphQL query from the context, if available.
	 *
	 * @phpcs:disable SlevomatCodingStandard.Complexity.Cognitive.ComplexityTooHigh, Generic.Metrics.CyclomaticComplexity.TooHigh
	 *
	 * @return string|null The GraphQL query string, or null if not available.
	 */
	public function get_query(): ?string {

		$context = $this->get_context();
		if ( empty( $context ) ) {
			return null;
		}

		$query = $context['query'] ?? null;
		if ( is_string( $query ) ) {
			return $query;
		}

		$request = $context['request'] ?? null;
		if ( empty( $request ) || ! is_array( $request ) ) {
			return $query;
		}

		$params = $request['params'] ?? null;
		if ( empty( $params ) || ! is_array( $params ) ) {
			return $query;
		}

		if ( isset( $params['query'] ) && is_string( $params['query'] ) ) {
			return $params['query'];
		}

		return $query;
	}

	/**
	 * Finds multiple log entries and returns them as an array.
	 *
	 * @param int                  $limit   The maximum number of log entries to return.
	 * @param int                  $offset  The offset for pagination.
	 * @param array<string, mixed> $where_clauses Optional. Additional WHERE conditions.
	 * @param string               $orderby The column to order by.
	 * @param string               $order   The order direction (ASC or DESC).
	 *
	 * @return array<\WPGraphQL\Logging\Logger\Database\DatabaseEntity> An array of DatabaseEntity instances, or an empty array if none found.
	 */
	public static function find_logs(int $limit, int $offset, array $where_clauses = [], string $orderby = 'id', string $order = 'DESC'): array {
		global $wpdb;
		$table_name = self::get_table_name();
		$order      = sanitize_text_field( strtoupper( $order ) );
		$orderby    = sanitize_text_field( $orderby );

		$where = '';
		foreach ( $where_clauses as $clause ) {
			if ( '' !== $where ) {
				$where .= ' AND ';
			}
			$where .= (string) $clause;
		}
		if ( '' !== $where ) {
			$where = 'WHERE ' . $where;
		}

		/** @psalm-suppress PossiblyInvalidCast */
		$query = $wpdb->prepare(
			"SELECT * FROM {$table_name} {$where} ORDER BY {$orderby} {$order} LIMIT %d, %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$offset,
			$limit
		);

		// We do not want to cache as this is a paginated query.
		$results = $wpdb->get_results( $query, ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared

		if ( empty( $results ) || ! is_array( $results ) ) {
			return [];
		}

		return array_map(
			static function (array $row) {
				return DatabaseEntity::create_from_db_row( $row );
			},
			$results
		);
	}

	/**
	 * Gets the name of the logging table.
	 */
	public static function get_table_name(): string {
		global $wpdb;
		$name = apply_filters( 'wpgraphql_logging_database_name', $wpdb->prefix . 'wpgraphql_logging' );
		return self::sanitize_text_field( $name );
	}

	/**
	 * Gets the database schema for the logging table.
	 */
	public static function get_schema(): string {
		global $wpdb;
		$table_name      = self::get_table_name();
		$charset_collate = $wpdb->get_charset_collate();

		// **IMPORTANT**: This schema format with PRIMARY KEY on its own line is the
		// correct and stable way to work with dbDelta.
		return "
	   CREATE TABLE {$table_name} (
		  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
		  channel VARCHAR(191) NOT NULL,
		  level SMALLINT UNSIGNED NOT NULL,
		  level_name VARCHAR(50) NOT NULL,
		  message LONGTEXT NOT NULL,
		  context JSON NULL,
		  extra JSON NULL,
		  datetime DATETIME NOT NULL,
		  PRIMARY KEY  (id),
		  INDEX channel_index (channel),
		  INDEX level_index (level),
		  INDEX datetime_index (datetime)
	   ) {$charset_collate};
	";
	}

	/**
	 * Creates the logging table in the database.
	 */
	public static function create_table(): void {
		require_once ABSPATH . 'wp-admin/includes/upgrade.php'; // @phpstan-ignore-line
		dbDelta( self::get_schema() );
	}

	/**
	 * Drops the logging table from the database.
	 */
	public static function drop_table(): void {
		global $wpdb;
		$table_name = self::get_table_name();
		$wpdb->query( "DROP TABLE IF EXISTS {$table_name}" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.NoCaching
	}

	/**
	 * Sanitizes a text field.
	 *
	 * @param string $value The value to sanitize.
	 */
	protected static function sanitize_text_field(string $value): string {
		return sanitize_text_field( $value );
	}

	/**
	 * Sanitizes an array field recursively.
	 *
	 * @param array<mixed> $data The array to sanitize.
	 *
	 * @return array<mixed> The sanitized array.
	 */
	protected static function sanitize_array_field(array $data): array {
		foreach ( $data as &$value ) {
			if ( is_string( $value ) ) {
				$value = self::sanitize_text_field( $value );
				continue;
			}

			if ( is_array( $value ) ) {
				$value = self::sanitize_array_field( $value );
			}
		}
		return $data;
	}
}
