---
title: "Explanation: Processors & Handlers"
description: "Understanding the Processors and Handlers system in WPGraphQL Logging Plugin and how they process and store log data."
---

The WPGraphQL Logging plugin builds on [Monolog](https://github.com/Seldaek/monolog), a widely adopted PHP logging library. Monolog's architecture separates concerns into two complementary systems: processors that enrich log data and handlers that determine where logs are written. Understanding this separation is key to extending the plugin's logging capabilities effectively.

## Why Monolog?

Rather than building a custom logging system from scratch, the plugin leverages Monolog for several compelling reasons:

1. **Battle-tested**: Monolog is the de facto standard for PHP logging, used by thousands of projects.
2. **Extensible**: The processor and handler architecture provides clear extension points.
3. **PSR-3 compliant**: Follows PHP Standards Recommendations for logging interfaces.
4. **Rich ecosystem**: Access to dozens of pre-built processors and handlers for common tasks.
5. **Channel-based**: Supports multiple logger instances with different configurations.

## Processors: Enriching Log Data

Processors modify log records before handlers write them. Think of processors as middleware in the logging pipeline: each log record passes through a chain of processors, and each processor can add, modify, or remove data.

### What Processors Do

A processor receives a `LogRecord` object and returns it, potentially modified. Processors typically add data to two key arrays on the record:

- **context**: Domain-specific data passed when logging (query variables, operation names, etc.).
- **extra**: Metadata added by processors (memory usage, request headers, server info, etc.).

This separation keeps intentional logged data (context) distinct from automatically captured metadata (extra).

### Default Processors

The plugin configures several processors by default through `LoggerService::get_default_processors()`:

1. **MemoryUsageProcessor**: Adds current memory consumption to track resource usage per query.
2. **MemoryPeakUsageProcessor**: Captures peak memory usage during the request lifecycle.
3. **WebProcessor**: Extracts HTTP request metadata (IP address, request method, URI, referrer).
4. **ProcessIdProcessor**: Logs the PHP process ID, useful for debugging concurrent requests.
5. **RequestHeadersProcessor**: Custom processor that captures and sanitizes HTTP request headers.
6. **DataSanitizationProcessor**: Custom processor that anonymizes, removes, or truncates sensitive data.

These processors run in order, each receiving the record modified by previous processors. The order matters: if one processor removes data that another processor expects, the second processor must run first.

### The RequestHeadersProcessor

This custom processor demonstrates a common pattern: extracting data from `$_SERVER` superglobal and adding it to the log record. It sanitizes header names and values before storing them in `extra['request_headers']`, ensuring no malicious input reaches log storage.

### The DataSanitizationProcessor

This processor implements compliance-friendly logging by applying configurable rules to remove or anonymize sensitive data. It supports three sanitization strategies:

- **Anonymize**: Replace values with `'***'` (for passwords, emails, usernames).
- **Remove**: Delete the key entirely (for user capability data, tokens).
- **Truncate**: Shorten strings to 47 characters + `'...'` (for long query strings).

The processor uses dot notation to target nested data (`variables.password`, `request.app_context.viewer.data`), making it flexible for complex log structures. Settings can use recommended defaults or custom field lists via `wpgraphql_logging_data_sanitization_rules` filter.

## Handlers: Writing Log Data

Handlers decide where logs are written: databases, files, streams, third-party services, or multiple destinations simultaneously. A single logger can use multiple handlers, each writing to different locations or filtering by log level.

### The WordPressDatabaseHandler

The plugin's default handler extends Monolog's `AbstractProcessingHandler` and writes to a WordPress custom table via the `LogStoreService`. Key characteristics:

- **Buffered by default**: Wrapped in a `BufferHandler` that batches up to 50 log records before writing, reducing database queries.
- **Error resilient**: Catches exceptions during writes and fires `wpgraphql_logging_write_database_error` action without breaking execution.
- **Level-aware**: Only writes records at or above its configured minimum level (default: all levels).

### Why Buffer Logs?

A single GraphQL query can trigger multiple log events (pre-request, execution, response). Without buffering, each event would cause an immediate database write, potentially adding dozens of queries per request. The `BufferHandler` accumulates records in memory and writes them in one batch at the end of the request, drastically improving performance.

### Handler vs. Processor Lifecycle

Understanding when processors and handlers run is critical:

1. Your code calls `$logger->info( 'message', $context )`.
2. LoggerService merges `$context` with default context (WP version, plugin version, site URL).
3. Each processor runs in order, modifying the `LogRecord`.
4. After all processors finish, handlers receive the final `LogRecord`.
5. Each handler checks if the record meets its minimum level requirement.
6. Handlers write the record to their respective destinations.

This pipeline ensures that all enrichment happens before any storage, and that different handlers can apply different level filters.

## Extensibility Through Filters

The plugin exposes two filters for customizing the logging pipeline:

- **wpgraphql_logging_default_processors**: Modify the array of processors before they're added to the logger. Add custom processors or remove defaults.
- **wpgraphql_logging_default_handlers**: Modify the array of handlers. Replace the database handler with a file handler, add a Slack handler for errors, or combine multiple destinations.

These filters run when `LoggerService::get_instance()` creates a logger, so changes apply immediately to all log operations.

## Common Customization Patterns

### Adding a File Handler for Errors

Add a `StreamHandler` that only writes ERROR and above to a file while keeping the default database handler for all levels. This creates an audit trail of serious issues without filling the database with debug logs.

### Removing Sensitive Processors

If your environment prohibits logging IP addresses, filter out the `WebProcessor` via `wpgraphql_logging_default_processors`, ensuring compliance without modifying core plugin files.

### Multi-destination Logging

Use multiple handlers to send INFO+ to the database, ERROR+ to a file, and CRITICAL+ to a Slack webhook. Each handler operates independently with its own level threshold.

## Performance Implications

Processors add computational overhead: the more processors, the longer each log operation takes. However, processors only run when logging actually occurs, and the RuleManager (see [Rule Manager Explanation](../rule-manager/index.md)) gates whether events log at all. Sampling rates and query exclusion rules ensure processors only run on a subset of requests.

Handlers determine I/O overhead. Database writes are buffered, file writes are asynchronous (at OS level), but network handlers (HTTP, Slack) can introduce latency. Use level thresholds to minimize expensive handler invocations.

## Related Documentation

For practical implementation examples, see:

- [How to add a new Processor](../../how-to/logger-add-processor/index.md)
- [How to add a new Handler](../../how-to/logger-add-handler/index.md)
