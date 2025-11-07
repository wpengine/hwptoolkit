---
title: "How to Guide - Add a new Monolog Handler"
description: "This guide shows how to log to a file using a Monolog handler, either in addition to the default WordPress database handler or as a replacement. It also covers per-instance overrides."
---

# Introduction

In this guide, you will learn how to extend the logging capabilities of WPGraphQL Logging by adding a new [Monolog](https://github.com/Seldaek/monolog) handler. Specifically, we will demonstrate how to send logs to a file, which can be useful for long-term storage, offline analysis, or integration with external log management systems.


## What is a Monolog Handler?

Handlers decide where logs are written. By default, WPGraphQL Logging uses a custom `WordPressDatabaseHandler` to store logs in the database. You can add more destinations (files, streams, third-party services) or replace the defaults.

>[!NOTE]
> See <https://seldaek.github.io/monolog/doc/02-handlers-formatters-processors.html> for a list of handlers and processors

## Example 1: Add a new handler

Use the `wpgraphql_logging_default_handlers` filter to push a `StreamHandler` that writes to a file. The default database handler will remain enabled.

```php
<?php
use Monolog\Handler\StreamHandler;
use Monolog\Level;

add_filter( 'wpgraphql_logging_default_handlers', function( array $handlers ) {
    $path = WP_CONTENT_DIR . '/logs/wpgraphql_logging.log';
    if ( ! file_exists( dirname( $path ) ) ) {
        // Ensure directory exists
        wp_mkdir_p( dirname( $path ) );
    }

    // Log ERROR and higher to a file, in addition to the default DB handler
    $handlers[] = new StreamHandler( $path, Level::Error );
    return $handlers;
});
```

## Example 2: Replace the default handler

Return your own array of handlers from the same filter to replace the default handler entirely.

```php
<?php
use Monolog\Handler\StreamHandler;
use Monolog\Level;

add_filter( 'wpgraphql_logging_default_handlers', function( array $handlers ) {
    $path = WP_CONTENT_DIR . '/logs/wpgraphql_logging.log';
    if ( ! file_exists( dirname( $path ) ) ) {
        wp_mkdir_p( dirname( $path ) );
    }

    // Replace defaults: file handler only, log everything from DEBUG and up
    return [ new StreamHandler( $path, Level::Debug ) ];
});
```

## Tips

>[!IMPORTANT]
> You should restrict public access to the log file if being written in a public directory

- Ensure the logs directory is writable by the web server user.
- Consider `Monolog\\Handler\\RotatingFileHandler` to rotate files by day and limit disk usage.
- You can combine multiple handlers (e.g., database + file + Slack) either globally (filter) or per instance.

## Related Content

- See the [Logger reference](../reference/logging.md#filter-wpgraphql_logging_default_handlers) for `wpgraphql_logging_default_handlers` and other hooks.

---

## Contributing

We welcome and appreciate contributions from the community. If you'd like to help improve this documentation, please check out our [Contributing Guide](https://github.com/wpengine/hwptoolkit/blob/main/CONTRIBUTING.md) for more details on how to get started.
