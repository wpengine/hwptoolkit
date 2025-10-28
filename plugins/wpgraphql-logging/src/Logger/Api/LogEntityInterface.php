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
	 * Gets the schema for the log entry.
	 *
	 * @return string The schema for the log entry.
	 */
	public function get_schema(): string;

	/**
	 * Creates a new log entry.
	 *
	 * @param string               $channel The channel for the log entry.
	 * @param int                  $level The logging level.
	 * @param string               $level_name The name of the logging level.
	 * @param string               $message The log message.
	 * @param array<string, mixed> $context Additional context for the log entry.
	 * @param array<string, mixed> $extra Extra data for the log entry.
	 *
	 * @return self The log entry.
	 */
	public function create(string $channel, int $level, string $level_name, string $message, array $context = [], array $extra = []): self;

	/**
	 * Saves the log entry
	 *
	 * @return int|null The ID of the newly created log entry, or null on failure.
	 */
	public function save(): ?int;
}
