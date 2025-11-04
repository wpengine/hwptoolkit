<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Unit\Logger\Store;

use WPGraphQL\Logging\Logger\Api\LogEntityInterface;
use WPGraphQL\Logging\Logger\Api\LogServiceInterface;
use DateTime;

/**
 * A stub implementation of LogServiceInterface for testing purposes.
 */
class CustomLogServiceStub implements LogServiceInterface
{
    public function create_log_entity(string $channel, int $level, string $level_name, string $message, array $context = [], array $extra = []): ?LogEntityInterface
    {
        return null;
    }

    public function find_entity_by_id(int $id): ?LogEntityInterface
    {
        return null;
    }

    public function find_entities_by_where(array $args = []): array
    {
        return [];
    }

    public function delete_entity_by_id(int $id): bool
    {
        return true;
    }

    public function delete_entities_older_than(DateTime $date): bool
    {
        return true;
    }

    public function delete_all_entities(): bool
    {
        return true;
    }

    public function count_entities_by_where(array $args = []): int
    {
        return 0;
    }

    public function activate(): void
    {
    }

    public function deactivate(): void
    {
    }
}
