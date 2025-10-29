## Logger Reference

The WPGraphQL Logging subsystem is built on [Monolog](https://github.com/Seldaek/monolog). This reference documents the Logger classes under `src/Logger` and all available WordPress actions/filters for extending behavior.

## Table of Contents

- [Logger\LoggerService](#class-loggerloggerservice)
- [Logger\LoggingHelper](#trait-loggerlogginghelper)
- [Logger\Handlers\WordPressDatabaseHandler](#class-loggerhandlerswordpressdatabasehandler)
- [Logger\Processors\RequestHeadersProcessor](#class-loggerprocessorsrequestheadersprocessor)
- [Logger\Processors\DataSanitizationProcessor](#class-loggerprocessorsdatasanitizationprocessor)
- [Logger\Database\WordPressDatabaseEntity](#class-loggerdatabasewordpressdatabaseentity)
- [Logger\Scheduler\DataDeletionScheduler](#class-loggerschedulerdatadeletionscheduler)
- [Quick Start](#quick-start)
- [Available Log Levels](#available-log-levels)


---

### Class: `Logger\LoggerService`
Source: <https://github.com/wpengine/hwptoolkit/blob/main/plugins/wpgraphql-logging/src/Logger/LoggerService.php>

Manages Monolog instances (per-channel singleton). Provides default handlers, processors, and context.

#### Filter: `wpgraphql_logging_default_processors`
Filters the default processor list.

Parameters:
- `$processors` (array<ProcessorInterface>) Current processors

Returns: array

Example:
```php
add_filter( 'wpgraphql_logging_default_processors', function( array $processors ) {
    $processors[] = new \MyPlugin\Logging\UserContextProcessor();
    return $processors;
});
```

#### Filter: `wpgraphql_logging_default_buffer_limit`
Filters the default buffer limit for the BufferHandler.

Parameters:
- `$buffer_limit` (int) Current buffer limit (default: 50)

Returns: int

Example:
```php

add_filter( 'wpgraphql_logging_default_buffer_limit', function( int $buffer_limit ) {
    // Increase buffer limit for high-traffic sites
    return 100;
});


```



#### Filter: `wpgraphql_logging_default_handlers`
Filters the default handler list.

Parameters:
- `$handlers` (array<HandlerInterface>) Current handlers

Returns: array

Example:
```php
use Monolog\Handler\RotatingFileHandler;
use Monolog\Level;

add_filter( 'wpgraphql_logging_default_handlers', function( array $handlers ) {
    $handlers[] = new RotatingFileHandler( WP_CONTENT_DIR . '/logs/wpgraphql.log', 7, Level::Info );
    return $handlers;
});
```

#### Filter: `wpgraphql_logging_default_context`
Filters the default context merged into every record.

Parameters:
- `$context` (array) Current default context

Returns: array

Example:
```php
add_filter( 'wpgraphql_logging_default_context', function( array $context ) {
    $context['environment'] = wp_get_environment_type();
    $context['multisite']   = is_multisite();
    return $context;
});
```


---

### Trait: `Logger\LoggingHelper`
Source: <https://github.com/wpengine/hwptoolkit/blob/main/plugins/wpgraphql-logging/src/Logger/LoggingHelper.php>

Common helpers used by logger-aware classes; composes a `RuleManager` and evaluates whether logging is enabled and which events are selected.

#### Filter: `wpgraphql_logging_rule_manager`
Allows custom rules to be added to the `RuleManager`.

Parameters:
- `$rule_manager` (RuleManager) The rule manager instance

Returns: RuleManager

Example:
```php
add_filter( 'wpgraphql_logging_rule_manager', function( $rule_manager ) {
    $rule_manager->add_rule( new \MyPlugin\Logging\Rules\BlockPrivateIPsRule() );
    return $rule_manager;
});
```

#### Filter: `wpgraphql_logging_is_enabled`
Filters the final decision (true/false) for whether logging is enabled for the current request.

Parameters:
- `$is_enabled` (bool) Computed result from rules
- `$config` (array) Current logging configuration

Returns: bool

Example:
```php
add_filter( 'wpgraphql_logging_is_enabled', function( bool $enabled, array $config ) {
    if ( defined( 'WPGRAPHQL_LOGGING_FORCE_DISABLE' ) && WPGRAPHQL_LOGGING_FORCE_DISABLE ) {
        return false;
    }
    return $enabled;
}, 10, 2 );
```


---

### Class: `Logger\Handlers\WordPressDatabaseHandler`
Source: <https://github.com/wpengine/hwptoolkit/blob/main/plugins/wpgraphql-logging/src/Logger/Handlers/WordPressDatabaseHandler.php>

Monolog handler that persists records to the WordPress database via `DatabaseEntity`.

Hooks: None.


---

### Class: `Logger\Processors\RequestHeadersProcessor`
Source: <https://github.com/wpengine/hwptoolkit/blob/main/plugins/wpgraphql-logging/src/Logger/Processors/RequestHeadersProcessor.php>

Adds request headers to the record `extra` data.

Hooks: None.


---

### Class: `Logger\Processors\DataSanitizationProcessor`
Source: <https://github.com/wpengine/hwptoolkit/blob/main/plugins/wpgraphql-logging/src/Logger/Processors/DataSanitizationProcessor.php>

Sanitizes sensitive fields in record `context` and `extra` based on settings.

#### Filter: `wpgraphql_logging_data_sanitization_enabled`
Controls whether sanitization is active.

Parameters:
- `$enabled` (bool) From settings

Returns: bool

Example:
```php
add_filter( 'wpgraphql_logging_data_sanitization_enabled', function( $enabled ) {
    return $enabled && ! defined( 'WPGRAPHQL_LOGGING_TRUSTED_ENV' );
});
```

#### Filter: `wpgraphql_logging_data_sanitization_rules`
Filters the active rule map (field path => action).

Parameters:
- `$rules` (array) Computed rules (recommended or custom)

Returns: array

Example:
```php
add_filter( 'wpgraphql_logging_data_sanitization_rules', function( array $rules ) {
    $rules['request.params.password'] = 'remove';
    return $rules;
});
```

#### Filter: `wpgraphql_logging_data_sanitization_recommended_rules`
Filters the built-in recommended rules prior to use.

Parameters:
- `$rules` (array)

Returns: array

#### Filter: `wpgraphql_logging_data_sanitization_record`
Filters the final `LogRecord` after sanitization is applied.

Parameters:
- `$record` (Monolog\LogRecord)

Returns: Monolog\LogRecord


---

### Class: `Logger\Database\WordPressDatabaseEntity`
Source: <https://github.com/wpengine/hwptoolkit/blob/main/plugins/wpgraphql-logging/src/Logger/Database/DatabaseEntity.php>

Represents a single log entry and provides persistence helpers.

#### Filter: `wpgraphql_logging_database_name`
Filters the database table name used for logs.

Parameters:
- `$table_name` (string)

Returns: string

Example:
```php
add_filter( 'wpgraphql_logging_database_name', function( string $name ) {
    return $name . '_tenant_' . get_current_blog_id();
});
```

#### Filter: `wpgraphql_logging_allowed_orderby_columns`
Filters the allowed columns for ORDER BY in `find_logs()` queries.

**Security:** This filter adds whitelist validation to prevent SQL injection in ORDER BY clauses. Only columns in this array can be used for sorting.

Parameters:
- `$allowed_columns` (array<string>) Default allowed columns: `['id', 'datetime', 'level', 'level_name', 'channel', 'message']`

Returns: array<string>

Example:
```php
// Add custom column to allowed ORDER BY list
add_filter( 'wpgraphql_logging_allowed_orderby_columns', function( array $columns ) {
    $columns[] = 'custom_field';
    return $columns;
});
```

**Note:** If an invalid column is requested, the query will fallback to ordering by `id` (default).


---

### Class: `Logger\Scheduler\DataDeletionScheduler`
Source: <https://github.com/wpengine/hwptoolkit/blob/main/plugins/wpgraphql-logging/src/Logger/Scheduler/DataDeletionScheduler.php>

Schedules and performs periodic deletion of old logs according to retention settings.

#### Action: `wpgraphql_logging_deletion_cleanup`
Cron hook fired to perform deletion. You can also trigger it manually with `do_action` or WP-CLI cron.

Parameters: None

Example:
```php
// Manually trigger cleanup (e.g., in a maintenance task)
do_action( 'wpgraphql_logging_deletion_cleanup' );
```

#### Action: `wpgraphql_logging_cleanup_error`
Fired when an exception occurs during cleanup.

Parameters:
- `$payload` (array) Includes: `error_message`, `retention_days`, `timestamp`

Example:
```php
add_action( 'wpgraphql_logging_cleanup_error', function( array $payload ) {
    error_log( '[WPGraphQL Logging] Cleanup error: ' . $payload['error_message'] );
}, 10, 1 );
```


---

### Quick Start

```php
use WPGraphQL\Logging\Logger\LoggerService;
use Monolog\Level;

// Default logger
$logger = LoggerService::get_instance();

// Context is merged with defaults (WP version, plugin version, etc.)
$logger->info( 'User performed action', [ 'user_id' => 123 ] );

// Custom channel with extra handlers/processors
$logger = LoggerService::get_instance(
    'my_channel',
    null, // use default handlers via filter
    null, // use default processors via filter
    [ 'component' => 'catalog' ]
);

// Generic form
$logger->log( Level::Debug, 'Debug details', [ 'trace_id' => 'abc123' ] );
```


---

### Available Log Levels

WPGraphQL Logging supports standard PSR-3/Monolog levels:

| Level | Method |
| --- | --- |
| `EMERGENCY` | `$logger->emergency()` |
| `ALERT` | `$logger->alert()` |
| `CRITICAL` | `$logger->critical()` |
| `ERROR` | `$logger->error()` |
| `WARNING` | `$logger->warning()` |
| `NOTICE` | `$logger->notice()` |
| `INFO` | `$logger->info()` |
| `DEBUG` | `$logger->debug()` |

You can also call `$logger->log(\Monolog\Level::Info, 'message', $context)`. 

---

Further reading:

- [Monolog Documentation](https://github.com/Seldaek/monolog/blob/main/doc/01-usage.md)
- [PSR-3 Logger Interface](https://www.php-fig.org/psr/psr-3/)
- [WordPress Plugin API (Hooks)](https://developer.wordpress.org/plugins/hooks/)
