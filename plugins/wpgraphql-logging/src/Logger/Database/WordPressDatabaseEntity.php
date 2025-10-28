<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Logger\Database;

use WPGraphQL\Logging\Logger\Api\LogEntityInterface;

/**
 * WordPress Database Entity for Monolog.
 *
 * @package WPGraphQL\Logging
 *
 * @since 0.0.1
 */
class WordPressDatabaseEntity implements LogEntityInterface {
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
	public function __construct() {
		// Set a default datetime for new, unsaved entries.
		$this->datetime = current_time( 'mysql', 1 );
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
	 * Gets the schema for the log entry.
	 *
	 * @return string The schema for the log entry.
	 */
	public function get_schema(): string {
		global $wpdb;
		$table_name      = $this->get_table_name();
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
		  INDEX level_name_index (level_name),
		  INDEX level_index (level),
		  INDEX datetime_index (datetime)
	   ) {$charset_collate};
	";
	}

	/**
	 * Gets the name of the table for the log entry.
	 *
	 * @return string The name of the table for the log entry.
	 */
	public function get_table_name(): string {
		global $wpdb;
		return apply_filters( 'wpgraphql_logging_database_name', $wpdb->prefix . 'wpgraphql_logging' );
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
	public function create(string $channel, int $level, string $level_name, string $message, array $context = [], array $extra = []): self {
		$entity             = new self();
		$entity->channel    = $this->sanitize_text_field( $channel );
		$entity->level      = $level;
		$entity->level_name = $this->sanitize_text_field( $level_name );
		$entity->message    = $this->sanitize_text_field( $message );
		$entity->context    = $this->sanitize_array_field( $context );
		$entity->extra      = $this->sanitize_array_field( $extra );

		return $entity;
	}

	/**
	 * Saves a new logging entity to the database. This is an insert-only operation.
	 *
	 * @return int|null The ID of the newly created log entry, or 0 on failure.
	 */
	public function save(): ?int {
		global $wpdb;
		$table_name = self::get_table_name();

		$data = [
			'channel'    => $this->get_channel(),
			'level'      => $this->get_level(),
			'level_name' => $this->get_level_name(),
			'message'    => $this->get_message(),
			'context'    => wp_json_encode( $this->get_context() ),
			'extra'      => wp_json_encode( $this->get_extra() ),
			'datetime'   => $this->get_datetime(),
		];

		$formats = [ '%s', '%d', '%s', '%s', '%s', '%s', '%s' ];

		$result = $wpdb->insert( $table_name, $data, $formats ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery

		if ( $result ) {
			$this->id = (int) $wpdb->insert_id;
			return $this->get_id();
		}

		return null;
	}

	/**
	 * Sanitizes a text field.
	 *
	 * @param string $value The value to sanitize.
	 */
	protected function sanitize_text_field(string $value): string {
		return sanitize_text_field( $value );
	}

	/**
	 * Sanitizes an array field recursively.
	 *
	 * @param array<mixed> $data The array to sanitize.
	 *
	 * @return array<mixed> The sanitized array.
	 */
	protected function sanitize_array_field(array $data): array {
		foreach ( $data as &$value ) {
			if ( is_string( $value ) ) {
				$value = $this->sanitize_text_field( $value );
				continue;
			}

			if ( is_array( $value ) ) {
				$value = $this->sanitize_array_field( $value );
			}
		}
		return $data;
	}
}
