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

The plugin configures six processors by default through `LoggerService::get_default_processors()`: MemoryUsageProcessor, MemoryPeakUsageProcessor, WebProcessor (HTTP request metadata), ProcessIdProcessor, RequestHeadersProcessor (custom, captures HTTP headers), and DataSanitizationProcessor (custom, anonymizes/removes/truncates sensitive data). These run in order, each receiving the record modified by previous processors.

The DataSanitizationProcessor implements compliance-friendly logging by applying configurable rules. It supports three strategies: anonymize (replace with `'***'`), remove (delete the key), or truncate (shorten to 47 characters). The processor uses dot notation to target nested data like `variables.password` or `request.app_context.viewer.data`, with settings via the `wpgraphql_logging_data_sanitization_rules` filter.

## Handlers: Writing Log Data

Handlers decide where logs are written: databases, files, streams, third-party services, or multiple destinations simultaneously. A single logger can use multiple handlers, each writing to different locations or filtering by log level.

### The WordPressDatabaseHandler

The plugin's default handler extends Monolog's `AbstractProcessingHandler` and writes to a WordPress custom table via the `LogStoreService`. Key characteristics:

- **Buffered by default**: Wrapped in a `BufferHandler` that batches up to 50 log records before writing, reducing database queries.
- **Error resilient**: Catches exceptions during writes and fires `wpgraphql_logging_write_database_error` action without breaking execution.
- **Level-aware**: Only writes records at or above its configured minimum level (default: all levels).

### The Logging Pipeline

A single GraphQL query can trigger multiple log events. Without buffering, each would cause an immediate database write. The `BufferHandler` accumulates up to 50 records in memory and writes them in one batch at the end of the request, drastically improving performance.

The pipeline flows sequentially: log call → merge default context → run processors in order → pass final record to handlers → each handler checks level requirement → write to destinations. This ensures all enrichment happens before any storage, and different handlers can apply different level filters.

## Extensibility

The plugin exposes two filters when `LoggerService::get_instance()` creates a logger:

- **wpgraphql_logging_default_processors**: Modify the array of processors. Add custom processors or remove defaults (e.g., remove WebProcessor for compliance).
- **wpgraphql_logging_default_handlers**: Modify the array of handlers. Replace the database handler, add file/Slack handlers for errors, or combine multiple destinations with different level thresholds.

## Performance Implications

Processors add computational overhead: the more processors, the longer each log operation takes. However, processors only run when logging actually occurs, and the RuleManager (see [Rule Manager Explanation](../rule-manager/index.md)) gates whether events log at all. Sampling rates and query exclusion rules ensure processors only run on a subset of requests.

Handlers determine I/O overhead. Database writes are buffered, file writes are asynchronous (at OS level), but network handlers (HTTP, Slack) can introduce latency. Use level thresholds to minimize expensive handler invocations.

## Related Documentation

For practical implementation examples, see:

- [How to add a new Processor](../../how-to/logger-add-processor/index.md)
- [How to add a new Handler](../../how-to/logger-add-handler/index.md)
