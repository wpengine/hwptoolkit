---
title: "How To Guide: Update Log Store Service"
description: "Learn how to replace the default database logging with a custom log storage implementation in the WPGraphQL Logging plugin."
---


## Overview

The WPGraphQL Logging plugin provides a robust database logging solution out of the box. However, for advanced use cases or integration with external logging systems, you can replace the default storage mechanism with your own custom implementation.

This is made possible by the `wpgraphql_logging_log_store_service` filter.

## Requirements

Your custom log service class must implement the `\WPGraphQL\Logging\Logger\Api\LogServiceInterface`. This ensures that your custom service has all the methods the plugin expects to interact with.

## Example: Logging to a File

Here is an example of how you could replace the default database logger with a simple file-based logger.

**1. Create your custom Log Service class**

First, create a class that implements `LogServiceInterface`. This is a simplified example that would log to a file in the `wp-content/uploads` directory.

```php
<?php

use WPGraphQL\Logging\Logger\Api\LogServiceInterface;
use WPGraphQL\Logging\Logger\Api\LogEntityInterface;

class MyFileLogService implements LogServiceInterface {

    /**
     * @inheritDoc
     */
    public function create_log_entity( string $channel, int $level, string $level_name, string $message, array $context = [], array $extra = [] ): ?LogEntityInterface {
        $log_file = WP_CONTENT_DIR . '/uploads/wpgraphql-logs.log';
        $log_entry = sprintf(
            "[%s] %s.%s: %s %s %s\n",
            gmdate( 'Y-m-d H:i:s' ),
            $channel,
            $level_name,
            $message,
            wp_json_encode( $context ),
            wp_json_encode( $extra )
        );

        file_put_contents( $log_file, $log_entry, FILE_APPEND );

        // Return null as we are not creating a database entity.
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
```

**2. Hook into the filter**

Next, use the `wpgraphql_logging_log_store_service` filter to return an instance of your new class. It's best to do this early, for example on the `plugins_loaded` hook.

```php
<?php

add_action( 'plugins_loaded', function() {
    add_filter( 'wpgraphql_logging_log_store_service', function( $log_service ) {
        return new MyFileLogService();
    } );
}, 10, 0 );
```

With this in place, all logs from WPGraphQL Logging will be routed through your `MyFileLogService` and saved to a file instead of the database.


## Contributing

We welcome and appreciate contributions from the community. If you'd like to help improve this documentation, please check out our [Contributing Guide](https://github.com/wpengine/hwptoolkit/blob/main/CONTRIBUTING.md) for more details on how to get started.
