<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Logger\Api;

use DateTime;

/**
 * This interface for a log service for CRUD operations on log entities.
 *
 * @package WPGraphQL\Logging
 *
 * @since 0.0.1
 */
interface LogServiceInterface {
	/**
	 * Activates the log service.
	 */
	public function activate(): void;

	/**
	 * Deactivates the log service.
	 */
	public function deactivate(): void;

	/**
	 * Creates a new log entity.
	 *
	 * @param string               $channel The channel for the log entry.
	 * @param int                  $level The logging level.
	 * @param string               $level_name The name of the logging level.
	 * @param string               $message The log message.
	 * @param array<string,mixed>  $context Additional context for the log entry.
	 * @param array<string, mixed> $extra Extra data for the log entry.
	 *
	 * @return \WPGraphQL\Logging\Logger\Api\LogEntityInterface|null The created log entity, or null on failure.
	 */
	public function create_log_entity(string $channel, int $level, string $level_name, string $message, array $context = [], array $extra = []): ?LogEntityInterface;

	/**
	 * Finds a log entity by ID.
	 *
	 * @param int $id The ID of the log entity.
	 *
	 * @return \WPGraphQL\Logging\Logger\Api\LogEntityInterface|null The found log entity, or null on failure.
	 */
	public function find_entity_by_id(int $id): ?LogEntityInterface;

	/**
	 * Finds log entities by a where clause.
	 *
	 * @param array<string, mixed> $args The arguments for the where clause.
	 *
	 * @return array<\WPGraphQL\Logging\Logger\Api\LogEntityInterface> The found log entities.
	 */
	public function find_entities_by_where(array $args = []): array;

	/**
	 * Deletes a log entity by ID.
	 *
	 * @param int $id The ID of the log entity.
	 *
	 * @return bool True if the log entity was deleted, false otherwise.
	 */
	public function delete_entity_by_id(int $id): bool;

	/**
	 * Deletes log entities older than a specific date.
	 *
	 * @param \DateTime $date The date to delete log entities older than.
	 *
	 * @return bool True if the log entities were deleted, false otherwise.
	 */
	public function delete_entities_older_than(DateTime $date): bool;

	/**
	 * Deletes all log entities.
	 *
	 * @return bool True if the log entities were deleted, false otherwise.
	 */
	public function delete_all_entities(): bool;

	/**
	 * Counts the number of log entities by a where clause.
	 *
	 * @param array<string, mixed> $args The arguments for the where clause.
	 *
	 * @return int The number of log entities.
	 */
	public function count_entities_by_where(array $args = []): int;
}
