<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Logger\Api;

/**
 * This interface for a log entity.
 *
 * @package WPGraphQL\Logging
 *
 * @since 0.0.1
 */
interface LogEntityInterface {
	/**
	 * The constructor.
	 *
	 * @param string               $channel The channel for the log entry.
	 * @param int                  $level The logging level.
	 * @param string               $level_name The name of the logging level.
	 * @param string               $message The log message.
	 * @param array<string, mixed> $context Additional context for the log entry.
	 * @param array<string, mixed> $extra Extra data for the log entry.
	 */
	public function __construct(string $channel, int $level, string $level_name, string $message, array $context = [], array $extra = []);

	/**
	 * Creates a new log entry instance from an array.
	 *
	 * @param array<string, mixed> $data The array to create the log entry from.
	 *
	 * @return \WPGraphQL\Logging\Logger\Api\LogEntityInterface The created log entry instance.
	 */
	public static function from_array(array $data): self;

	/**
	 * Gets the ID of the log entry.
	 *
	 * @return int The ID of the log entry.
	 */
	public function get_id(): int;

	/**
	 * Gets the channel of the log entry.
	 *
	 * @return string The channel of the log entry.
	 */
	public function get_channel(): string;

	/**
	 * Gets the logging level of the log entry.
	 *
	 * @return int The logging level of the log entry.
	 */
	public function get_level(): int;

	/**
	 * Gets the name of the logging level of the log entry.
	 *
	 * @return string The name of the logging level of the log entry.
	 */
	public function get_level_name(): string;

	/**
	 * Gets the message of the log entry.
	 *
	 * @return string The message of the log entry.
	 */
	public function get_message(): string;

	/**
	 * Gets the context of the log entry.
	 *
	 * @return array<string, mixed> The context of the log entry.
	 */
	public function get_context(): array;

	/**
	 * Gets the extra data of the log entry.
	 *
	 * @return array<string, mixed> The extra data of the log entry.
	 */
	public function get_extra(): array;

	/**
	 * Gets the datetime of the log entry.
	 *
	 * @return string The datetime of the log entry in MySQL format.
	 */
	public function get_datetime(): string;

	/**
	 * Gets the query of the log entry.
	 *
	 * @return string|null The query of the log entry.
	 */
	public function get_query(): ?string;

	/**
	 * Gets the schema for the log entry.
	 *
	 * @return string The schema for the log entry.
	 */
	public function get_schema(): string;

	/**
	 * Saves the log entry to the database.
	 *
	 * @return int The ID of the saved log entry, or 0 on failure.
	 */
	public function save(): int;
}
