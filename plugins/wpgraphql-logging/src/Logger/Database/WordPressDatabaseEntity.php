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
	 * Creates a new, unsaved log entry instance.
	 *
	 * @param string       $channel The channel for the log entry.
	 * @param int          $level The logging level.
	 * @param string       $level_name The name of the logging level.
	 * @param string       $message The log message.
	 * @param array<mixed> $context Additional context for the log entry.
	 * @param array<mixed> $extra Extra data for the log entry.
	 */
	public function __construct(string $channel, int $level, string $level_name, string $message, array $context = [], array $extra = []) {
		$this->channel    = $this->sanitize_text_field( $channel );
		$this->level      = $level;
		$this->level_name = $this->sanitize_text_field( $level_name );
		$this->message    = $this->sanitize_text_field( $message );
		$this->context    = $this->sanitize_array_field( $context );
		$this->extra      = $this->sanitize_array_field( $extra );

		// Set a default datetime for new, unsaved entries.
		$this->datetime = current_time( 'mysql', 1 );
	}

	/**
	 * Creates a new log entry instance from an array.
	 *
	 * @param array<string, mixed> $data The array to create the log entry from.
	 *
	 * @return \WPGraphQL\Logging\Logger\Database\WordPressDatabaseEntity The created log entry instance.
	 */
	public static function from_array(array $data): self {
		$entity           = new self(
			(string) $data['channel'],
			(int) $data['level'],
			(string) $data['level_name'],
			(string) $data['message'],
			( isset( $data['context'] ) && '' !== $data['context'] ) ? json_decode( $data['context'], true ) : [],
			( isset( $data['extra'] ) && '' !== $data['extra'] ) ? json_decode( $data['extra'], true ) : [],
		);
		$entity->id       = (int) $data['id'];
		$entity->datetime = (string) $data['datetime'];
		return $entity;
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
		if ( ! is_array( $request ) ) {
			return $query;
		}

		$params = $request['params'] ?? null;
		if ( ! is_array( $params ) ) {
			return $query;
		}

		if ( isset( $params['query'] ) && is_string( $params['query'] ) ) {
			return $params['query'];
		}

		return $query;
	}

	/**
	 * Saves the log entry to the database.
	 *
	 * @return int The ID of the saved log entry, or 0 on failure.
	 */
	public function save(): int {
		global $wpdb;
		$table_name = $this->get_table_name();


		// Note: Data sanitization is handled in the constructor..
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
	 * Gets the schema for the log entry.
	 *
	 * @return string The schema for the log entry.
	 */
	public static function get_schema(): string {
		global $wpdb;
		$table_name      = self::get_table_name();
		$charset_collate = $wpdb->get_charset_collate();

		return "
	   CREATE TABLE {$table_name} (
		  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
		  channel VARCHAR(100) NOT NULL,
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
		  INDEX datetime_index (datetime),
		  INDEX datetime_level_index (datetime, level)
	   ) {$charset_collate};
	";
	}

	/**
	 * Gets the name of the table for the log entry.
	 *
	 * @return string The name of the table for the log entry.
	 */
	public static function get_table_name(): string {
		global $wpdb;
		// @TODO - Check for multisite
		return $wpdb->prefix . 'wpgraphql_logging';
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
