<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Logger\Api;

use DateTimeInterface;

/**
 * Interface for services that aggregate log data for the admin dashboard.
 *
 * Implementations should do the aggregation in the storage layer (e.g. SQL GROUP BY)
 * rather than loading log rows into PHP. All dates are expected to be in UTC.
 *
 * @package WPGraphQL\Logging
 *
 * @since 1.1.0
 */
interface LogStatsInterface {
	/**
	 * Gets summary totals for a time window.
	 *
	 * @param \DateTimeInterface $start Start of the window (inclusive, UTC).
	 * @param \DateTimeInterface $end   End of the window (exclusive, UTC).
	 *
	 * @return array{total: int, errors: int, operations: int} Total entries, error entries and distinct operation names.
	 */
	public function get_summary( DateTimeInterface $start, DateTimeInterface $end ): array;

	/**
	 * Gets log entry counts grouped into fixed-size time buckets.
	 *
	 * Buckets with no entries must still be present with zero counts.
	 *
	 * @param \DateTimeInterface $start          Start of the first bucket (inclusive, UTC).
	 * @param int                $bucket_seconds Size of each bucket in seconds.
	 * @param int                $bucket_count   Number of buckets.
	 *
	 * @return array<int, array{total: int, errors: int}> Counts keyed by bucket index (0 to $bucket_count - 1).
	 */
	public function get_time_series( DateTimeInterface $start, int $bucket_seconds, int $bucket_count ): array;

	/**
	 * Gets log entry counts grouped by log level.
	 *
	 * @param \DateTimeInterface $start Start of the window (inclusive, UTC).
	 * @param \DateTimeInterface $end   End of the window (exclusive, UTC).
	 *
	 * @return array<int, array{level: int, level_name: string, total: int}> Rows ordered by level, highest first.
	 */
	public function get_level_breakdown( DateTimeInterface $start, DateTimeInterface $end ): array;

	/**
	 * Gets the GraphQL operations with the most log entries.
	 *
	 * Entries without an operation name are grouped under an empty string.
	 *
	 * @param \DateTimeInterface $start Start of the window (inclusive, UTC).
	 * @param \DateTimeInterface $end   End of the window (exclusive, UTC).
	 * @param int                $limit Maximum number of operations to return.
	 *
	 * @return array<int, array{operation: string, total: int, errors: int}> Rows ordered by total, highest first.
	 */
	public function get_top_operations( DateTimeInterface $start, DateTimeInterface $end, int $limit = 10 ): array;
}
