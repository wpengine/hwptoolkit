## How to Add a New Handler (File Logging)

This guide shows how to log to a file using a Monolog handler, either in addition to the default WordPress database handler or as a replacement. It also covers per-instance overrides.

### What is a Handler?

Handlers decide where logs are written. By default, WPGraphQL Logging uses a custom `WordPressDatabaseHandler` to store logs in the database. You can add more destinations (files, streams, third-party services) or replace the defaults.

>[!NOTE]
> See <https://seldaek.github.io/monolog/doc/02-handlers-formatters-processors.html> for a list of handlers and processors

### Option A: Add a file handler globally (in addition to the default)

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

### Option B: Replace the default handler globally (file only)

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

### Option C: Override handlers per logger instance

You can bypass the global defaults for a specific logger channel by passing handlers to `LoggerService::get_instance()`.

```php
<?php
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use WPGraphQL\Logging\Logger\LoggerService;
use WPGraphQL\Logging\Logger\Handlers\WordPressDatabaseHandler;

// Add file handler in addition to the DB handler for this specific channel
$handlers = [
    new WordPressDatabaseHandler(),
    new StreamHandler( WP_CONTENT_DIR . '/logs/wpgraphql-channel.log', Level::Info ),
];

$logger = LoggerService::get_instance( 'file_plus_db', $handlers );
$logger->info( 'Per-instance handlers configured' );

// Or replace defaults for the instance (file only)
$fileOnly = LoggerService::get_instance( 'file_only', [
    new StreamHandler( WP_CONTENT_DIR . '/logs/wpgraphql-file-only.log', Level::Warning ),
] );
$fileOnly->warning( 'This goes only to the file' );
```

### Tips

- Ensure the logs directory is writable by the web server user.
- Consider `Monolog\\Handler\\RotatingFileHandler` to rotate files by day and limit disk usage.
- You can combine multiple handlers (e.g., database + file + Slack) either globally (filter) or per instance.

### Related

- See the [Logger reference](../reference/logging.md#filter-wpgraphql_logging_default_handlers) for `wpgraphql_logging_default_handlers` and other hooks.
