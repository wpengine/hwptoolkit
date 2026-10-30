---
title: "Explanation: Event Architecture"
description: "Understanding the Event Architecture system in WPGraphQL Logging Plugin and how events are published and subscribed to throughout the logging process."
---

The WPGraphQL Logging plugin implements a lightweight publish/subscribe event system that provides a modern, flexible interface on top of WordPress's traditional hook system. This architecture enables developers to observe, transform, and extend the logging behavior at multiple points throughout a GraphQL request lifecycle.

## Why an Event System?

While WordPress already provides actions and filters, the plugin's event architecture adds several advantages:

1. **Discoverability**: Event names are defined as constants in the `Events` class, making them easy to discover through IDE autocomplete and eliminating typos.
2. **Structured payloads**: Events carry well-defined data structures rather than arbitrary function arguments.
3. **Priority-based execution**: Like WordPress hooks, with explicit ordering guarantees (ascending numeric priority).
4. **Separation of concerns**: Subscribe for read-only observation or transform for mutation, making intent clear.
5. **Error isolation**: Listener errors are caught and logged without breaking the execution pipeline.

## Core Architectural Concepts

### The EventManager

The `EventManager` class serves as the central hub for the event system. It maintains two separate registries:

- **Subscribers**: Read-only listeners that observe event payloads but don't modify them.
- **Transformers**: Functions that can mutate payloads before they reach core logging code.

This separation makes it immediately clear whether code is observing or modifying data, improving code clarity and reducing bugs.

### The WordPress Bridge

Every event published through the `EventManager` automatically triggers a corresponding WordPress action or filter. This bridging means:

- The plugin's event system fires `wpgraphql_logging_event_{event_name}` actions for publishing.
- Transforms fire `wpgraphql_logging_filter_{event_name}` filters.
- Existing WordPress code can hook into events without learning a new API.
- Plugin code can use either the modern event API or traditional WordPress hooks.

### Event Lifecycle

The `QueryEventLifecycle` class orchestrates how WPGraphQL lifecycle events are captured and logged. It acts as a facade, delegating to specialized logger classes (`QueryActionLogger` for actions, `QueryFilterLogger` for filters) and mapping WPGraphQL core hooks to the plugin's event system.

When WPGraphQL executes a query, the lifecycle flows through several stages:

1. `Events::PRE_REQUEST` maps to `do_graphql_request` action (before the request is processed).
2. `Events::REQUEST_DATA` maps to `graphql_request_data` filter (captures the full payload).
3. `Events::BEFORE_GRAPHQL_EXECUTION` maps to `graphql_before_execute` action (before query execution).
4. `Events::REQUEST_RESULTS` maps to `graphql_request_results` filter (final execution results).
5. `Events::BEFORE_RESPONSE_RETURNED` maps to `graphql_return_response` action (before response sent to client).
6. `Events::RESPONSE_HEADERS_TO_SEND` maps to `graphql_response_headers_to_send` filter (response headers).

This mapping creates a consistent abstraction layer, shielding your code from WPGraphQL core implementation details.

## The Plugin Facade

The `Plugin` class exposes three static methods that provide a clean, simple API:

- `Plugin::on( $event, callable $listener, $priority )`: Subscribe to observe events.
- `Plugin::transform( $event, callable $transform, $priority )`: Transform event payloads.
- `Plugin::emit( $event, array $payload )`: Publish custom events.

These methods delegate to the `EventManager` but provide a simpler interface that doesn't require understanding the underlying implementation.

## Why This Matters

The event architecture creates extensibility points throughout the logging pipeline. You can:

- Add custom context data to specific events (environment info, app IDs, user segments).
- Build analytics on top of logged queries without modifying core logging.
- Create custom log destinations triggered by specific query patterns.
- Publish your own events from custom resolvers that integrate with the logging system.

The architecture also supports defensive programming. If a listener throws an exception, the `EventManager` catches it, logs it via `wpgraphql_logging_event_error_listener` or `wpgraphql_logging_event_error_transform` actions, and continues processing. This ensures that poorly written extensions can't break your logging infrastructure.

## Performance Considerations

The event system is designed for minimal overhead:

- Events only register listeners when explicitly subscribed.
- Priority sorting happens once per event, not per invocation.
- The WordPress bridge uses native `do_action` and `apply_filters`, leveraging WordPress's optimized hook system.
- Error handling uses try-catch blocks only around listener execution, not the entire pipeline.

## Related Documentation

For practical implementation examples, see:

- [How to use the Events Pub/Sub System](../../how-to/event-pub-sub/index.md)
- [How to add data to an Event](../../how-to/event-add-context/index.md)
