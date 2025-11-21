---
title: "How To Guide: Add a new Processor"
description: "Learn how to add custom Monolog processors to the WPGraphQL Logging plugin to transform log records."
---

In this guide, you will learn how to extend the logging capabilities of WPGraphQL Logging by adding a new [Monolog](https://github.com/Seldaek/monolog) processor.

Specifically, we will demonstrate how to add the current WordPress environment variable to the extra data, which can be useful for distinguishing between development, staging, and production environments in your logs.

### What is a Monolog Processor?

Processors in Monolog add or transform data on each log record before handlers write it. They can modify the `context` or `extra` arrays on a record. See [Monolog documentation](https://seldaek.github.io/monolog/doc/02-handlers-formatters-processors.html).

### Step 1: Create a new Monolog Processor class

Create a PHP class that implements `Monolog\Processor\ProcessorInterface` and returns the updated `LogRecord`.

```php
<?php
namespace MyPlugin\Logging;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;

class EnvironmentProcessor implements ProcessorInterface {
    public function __invoke( LogRecord $record ): LogRecord {
        $record->extra['environment'] = wp_get_environment_type();
        return $record;
    }
}
```

### Step 2: Register the processor globally

Use the `wpgraphql_logging_default_processors` filter to add your processor to all logger instances.

```php
<?php
add_filter( 'wpgraphql_logging_default_processors', function( array $processors ) {
    $processors[] = new \MyPlugin\Logging\EnvironmentProcessor();
    return $processors;
});
```

You should see `environment` in the log record's `extra` data (e.g. in the Logs admin UI or your chosen handler output).

![Processor Example](screenshot.png)

>[!NOTE]
> The `LoggerService` allows you to specify your own list of default handlers and processors if you ever prefer to re-use it or change the list of default handlers and processors e.g.

```php
LoggerService::get_instance($channel, $handlers, $processors, $default_context);
```


## Contributing

We welcome and appreciate contributions from the community. If you'd like to help improve this documentation, please check out our [Contributing Guide](https://github.com/wpengine/hwptoolkit/blob/main/CONTRIBUTING.md) for more details on how to get started.
