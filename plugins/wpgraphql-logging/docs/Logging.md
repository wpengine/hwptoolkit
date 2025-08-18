# Loggers in WPGraphQL Logging

## Table of Contents

- [Overview](#overview)
- [Architecture](#architecture)
- [Default Components](#default-components)
- [Usage](#usage)
  - [Example 1: Basic logging with LoggerService](#example-1-basic-logging-with-loggerservice)
  - [Example 2: Creating a custom logger instance](#example-2-creating-a-custom-logger-instance)
  - [Example 3: Adding custom handlers](#example-3-adding-custom-handlers)
  - [Example 4: Adding custom processors](#example-4-adding-custom-processors)
  - [Example 5: Customizing log levels and filtering](#example-5-customizing-log-levels-and-filtering)
  - [Example 6: Using WordPress filters to modify defaults](#example-6-using-wordpress-filters-to-modify-defaults)

## Overview

WPGraphQL Logging uses [Monolog](https://github.com/Seldaek/monolog), the powerful PHP logging library, as its foundation. The `LoggerService` class provides a singleton wrapper around Monolog, making it easy to log throughout the application with consistent configuration.

The logging system is built around three core components under `src/Logger/`:

- **LoggerService** - The main service that manages Monolog instances with custom channels, handlers, processors, and context
- **Handlers** - Determine where logs are written (database, files, external services, etc.)
- **Processors** - Add extra data to log records (memory usage, request info, GraphQL query details, etc.)

> **Note**: This system leverages the full power of Monolog. For advanced usage, refer to the [Monolog documentation](https://github.com/Seldaek/monolog/blob/main/doc/01-usage.md).

## Architecture

### Channels
Channels group log messages by context or component. The default channel is `wpgraphql_logging`, but you can create multiple logger instances with different channels for different parts of your application.

### Handlers
Handlers determine where log records are written. The plugin includes a simple database handler.
- **WordPressDatabaseHandler** - Writes logs to a WordPress database table (`{$wpdb->prefix}wpgraphql_logging`)

### Processors
Processors add extra data to each log record. The plugin includes several default processors:

- **MemoryUsageProcessor** - Adds current memory usage
- **MemoryPeakUsageProcessor** - Adds peak memory usage
- **WebProcessor** - Adds web request data (IP, method, URI, etc.)
- **ProcessIdProcessor** - Adds the process ID
- **WPGraphQLQueryProcessor** - Adds GraphQL query, variables, and operation name

## Default Components

The LoggerService comes configured with sensible defaults:

| Component | Default Implementation | Purpose |
| --- | --- | --- |
| Handler | `WordPressDatabaseHandler` | Stores logs in WordPress database |
| Processors | Memory, Web, Process ID, WPGraphQL Query | Enriches log records with contextual data |
| Log Levels | All levels (DEBUG to EMERGENCY) | Monolog's standard PSR-3 log levels |
| Default Context | WP version, plugin version, debug mode, site URL | Consistent context across all logs |

## Usage

### Example 1: Basic logging with LoggerService

**Use case:** You want to log custom events from your plugin or theme.

```php
<?php
use WPGraphQL\Logging\Logger\LoggerService;
use Monolog\Level;

// Get the default logger instance
$logger = LoggerService::get_instance();

// Log at different levels
$logger->info('User performed action', ['user_id' => 123, 'action' => 'login']);
$logger->warning('Rate limit approaching', ['requests' => 95, 'limit' => 100]);
$logger->error('Database connection failed', ['error' => $exception->getMessage()]);

// Use the generic log method with Monolog levels
$logger->log(Level::Debug, 'Debug information', ['debug_data' => $debug_info]);
```

### Example 2: Creating a custom logger instance

**Use case:** You need a separate logger with a different channel for a specific component.

```php
<?php
use WPGraphQL\Logging\Logger\LoggerService;
use WPGraphQL\Logging\Logger\Handlers\WordPressDatabaseHandler;

// Create a logger with a custom channel
$custom_logger = LoggerService::get_instance(
    'my_custom_channel',
    [new WordPressDatabaseHandler()], // Custom handlers
    null, // Use default processors
    ['component' => 'my_plugin'] // Custom default context
);

$custom_logger->info('Custom component event', ['data' => 'example']);
```

### Example 3: Adding custom handlers

**Use case:** You want to log to multiple destinations (database + file + external service).

```php
<?php
use WPGraphQL\Logging\Logger\LoggerService;
use WPGraphQL\Logging\Logger\Handlers\WordPressDatabaseHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SlackWebhookHandler;
use Monolog\Level;

// Create custom handlers
$handlers = [
    new WordPressDatabaseHandler(), // Database (all levels)
    new StreamHandler('/path/to/app.log', Level::Info), // File (Info and above)
    new SlackWebhookHandler('webhook_url', '#alerts', 'WPGraphQL Bot', true, null, Level::Error), // Slack (Errors only)
];

$logger = LoggerService::get_instance('multi_output', $handlers);
$logger->error('Critical error occurred', ['error' => 'Something went wrong']);
```

### Example 4: Adding custom processors

**Use case:** You want to add custom data to all log records, such as user information or custom metrics.

```php
<?php
use WPGraphQL\Logging\Logger\LoggerService;
use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;

// Create a custom processor
class UserContextProcessor implements ProcessorInterface {
    public function __invoke(LogRecord $record): LogRecord {
        if (is_user_logged_in()) {
            $record->extra['user_id'] = get_current_user_id();
            $record->extra['user_role'] = wp_get_current_user()->roles[0] ?? 'unknown';
        }
        return $record;
    }
}

// Add custom processors
$processors = array_merge(
    LoggerService::get_default_processors(),
    [new UserContextProcessor()]
);

$logger = LoggerService::get_instance('user_aware', null, $processors);
$logger->info('User action logged'); // Will include user_id and user_role in extra data
```

### Example 5: Customizing log levels and filtering

**Use case:** You want to control which log levels are actually processed, or apply filtering based on content.

```php
<?php
use WPGraphQL\Logging\Logger\LoggerService;
use Monolog\Handler\FilterHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Level;

// Create a handler that only processes WARNING and above
$filtered_handler = new FilterHandler(
    new StreamHandler('/path/to/warnings.log'),
    Level::Warning,
    Level::Emergency
);

// Create a logger with level filtering
$logger = LoggerService::get_instance('filtered', [$filtered_handler]);

$logger->debug('Debug message'); // Won't be logged
$logger->info('Info message');   // Won't be logged
$logger->warning('Warning message'); // Will be logged
$logger->error('Error message');     // Will be logged
```

### Example 6: Using WordPress filters to modify defaults

**Use case:** You want to globally modify the default handlers, processors, or context for all LoggerService instances.

```php
<?php
use Monolog\Handler\RotatingFileHandler;
use Monolog\Level;

// Add a rotating file handler to all logger instances
add_filter('wpgraphql_logging_default_handlers', function($handlers) {
    $handlers[] = new RotatingFileHandler('/path/to/logs/wpgraphql.log', 0, Level::Info);
    return $handlers;
});

// Add custom context to all log records
add_filter('wpgraphql_logging_default_context', function($context) {
    $context['environment'] = wp_get_environment_type();
    $context['multisite'] = is_multisite();
    return $context;
});

// Add a custom processor to all loggers
add_filter('wpgraphql_logging_default_processors', function($processors) {
    $processors[] = new MyCustomProcessor();
    return $processors;
});

// Now all LoggerService instances will include these modifications
$logger = LoggerService::get_instance();
$logger->info('This will include the custom context and use all handlers');
```

## Available Log Levels

WPGraphQL Logging supports all standard [PSR-3 log levels](https://www.php-fig.org/psr/psr-3/) via Monolog:

| Level | Method | Use Case |
| --- | --- | --- |
| `EMERGENCY` | `$logger->emergency()` | System is unusable |
| `ALERT` | `$logger->alert()` | Action must be taken immediately |
| `CRITICAL` | `$logger->critical()` | Critical conditions |
| `ERROR` | `$logger->error()` | Error conditions |
| `WARNING` | `$logger->warning()` | Warning conditions |
| `NOTICE` | `$logger->notice()` | Normal but significant condition |
| `INFO` | `$logger->info()` | Informational messages |
| `DEBUG` | `$logger->debug()` | Debug-level messages |

You can also use the generic `$logger->log($level, $message, $context)` method with `Monolog\Level` constants.

## WordPress Filters

The following WordPress filters are available to customize the logging system:

- `wpgraphql_logging_default_handlers` - Modify default handlers
- `wpgraphql_logging_default_processors` - Modify default processors  
- `wpgraphql_logging_default_context` - Modify default context
- `wpgraphql_logging_database_name` - Customize the database table name

## Further Reading

- [Monolog Documentation](https://github.com/Seldaek/monolog/blob/main/doc/01-usage.md)
- [PSR-3 Logger Interface](https://www.php-fig.org/psr/psr-3/)
- [Monolog Handlers](https://github.com/Seldaek/monolog/blob/main/doc/02-handlers-formatters-processors.md#handlers)
- [Monolog Processors](https://github.com/Seldaek/monolog/blob/main/doc/02-handlers-formatters-processors.md#processors)
