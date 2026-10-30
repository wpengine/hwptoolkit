---
title: "Explanation: Processors & Handlers"
description: "How the WPGraphQL Logging plugin uses Monolog processors to add data to log records and handlers to store them."
---

The plugin logs through [Monolog](https://github.com/Seldaek/monolog), the standard PHP logging library. Monolog splits the work in two: processors add or change data on each log record, and handlers decide where the record is written. Knowing which side a change belongs on tells you whether to write a processor or a handler.

## Why Monolog

Monolog is PSR-3 compliant and widely used, and it already has processors and handlers for most common needs, from memory usage to Slack alerts. Building on it means the plugin's own code only covers what is specific to WPGraphQL, and you can reuse any Monolog processor or handler without adapting it.

## Processors

A processor receives a `LogRecord` and returns it with changes. Data passed in the log call goes in `context`, such as the query and its variables. Data a processor adds about the environment goes in `extra`, such as memory usage or request headers. Keeping the two apart makes it clear what the caller logged and what was collected automatically.

`LoggerService::get_default_processors()` lists six processors:

- `MemoryUsageProcessor` and `MemoryPeakUsageProcessor` from Monolog
- `WebProcessor` from Monolog, for the URL, method, IP and similar request data
- `ProcessIdProcessor` from Monolog
- `RequestHeadersProcessor`, the plugin's own, for the HTTP request headers
- `DataSanitizationProcessor`, the plugin's own, for removing sensitive data

Monolog's `pushProcessor()` adds each processor to the front of the stack, so they run in the reverse of the order they are pushed. Keep that in mind if a processor depends on data another one adds.

`DataSanitizationProcessor` applies rules from the Data Management settings. Each rule targets a key in dot notation, such as `variables.password`, and does one of three things: anonymize replaces the value with `***`, remove deletes the key, and truncate cuts the value to 47 characters plus `...`. The recommended rules anonymize the username, password and email variables and remove the viewer's capability data. The `wpgraphql_logging_data_sanitization_rules` filter can change the rules.

## Handlers

A handler writes records somewhere: a database, a file, a remote service. A logger can have several, and each can ignore records below its own minimum level, so you can send everything to the database and only errors to Slack.

The default handler is `WordPressDatabaseHandler`, which writes to the plugin's log table through `LogStoreService`. If a write fails, it fires `wpgraphql_logging_write_database_error` with the exception instead of breaking the request.

One GraphQL request produces several log records, so the database handler is wrapped in Monolog's `BufferHandler`. Records are held in memory and written together when the buffer reaches 50 records or the request ends. The `wpgraphql_logging_default_buffer_limit` filter changes the limit.

## How a record moves through the logger

The log call merges in the default context, runs the processors, then hands the finished record to each handler. Every handler sees the same processed record, so sanitization applies to all destinations.

## Extending

`LoggerService::get_instance()` applies two filters when it creates a logger. `wpgraphql_logging_default_processors` receives the processor list, so you can add your own or remove one, such as `WebProcessor` if you don't want IP addresses stored. `wpgraphql_logging_default_handlers` receives the handler list, so you can replace the database handler or add another destination.

Processors only run for events the [Rule Manager](../rule-manager/index.md) lets through, so sampling and exclusions also limit their cost. Handlers that call remote services add latency to the request, so give them a higher minimum level.

## Related documentation

- [How to add a new Processor](../../how-to/logger-add-processor/index.md)
- [How to add a new Handler](../../how-to/logger-add-handler/index.md)
