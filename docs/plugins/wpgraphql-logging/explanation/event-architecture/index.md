---
title: "Explanation: Event Architecture"
description: "How the WPGraphQL Logging plugin publishes events during a GraphQL request, and how your code can observe or change them."
---

The plugin has a small publish/subscribe event system on top of WordPress hooks. Each stage of a WPGraphQL request becomes a named event, and your code can watch an event or change its payload before it is logged.

## Why events instead of plain hooks

WordPress actions and filters would work, but the event layer gives each stage a name and a payload shape the plugin controls. Event names are constants on the `Events` class, so they show up in autocomplete and a typo fails loudly instead of silently hooking nothing. The layer also keeps WPGraphQL's own hook names out of your code, so a change in WPGraphQL core only needs a change in the plugin's mapping.

The API separates watching from changing. A subscriber gets the payload and returns nothing. A transform gets the payload and returns a new one, which is what the plugin logs. Reading code that uses `Plugin::on()` or `Plugin::transform()` tells you which one it is.

## EventManager

`EventManager` holds two registries, one for subscribers and one for transforms, each keyed by event name and priority. Lower priorities run first, as with WordPress hooks.

When an event is published, the manager calls each subscriber in priority order and then fires the WordPress action `wpgraphql_logging_event_{event_name}`. Transforms work the same way and finish with the filter `wpgraphql_logging_filter_{event_name}`. Code that prefers plain WordPress hooks can use those and skip the event API entirely.

A listener that throws does not stop the request. The manager catches the exception, fires `wpgraphql_logging_event_error_listener` or `wpgraphql_logging_event_error_transform` with the exception, and carries on with the next listener. A transform that throws leaves the payload as it was.

## How WPGraphQL stages map to events

`QueryEventLifecycle` registers the plugin on WPGraphQL's hooks and passes the work to two classes: `QueryActionLogger` for the action hooks and `QueryFilterLogger` for the filter hooks. Each maps one WPGraphQL hook to one event:

| Event | WPGraphQL hook | Type |
| --- | --- | --- |
| `Events::PRE_REQUEST` | `do_graphql_request` | action |
| `Events::REQUEST_DATA` | `graphql_request_data` | filter |
| `Events::BEFORE_GRAPHQL_EXECUTION` | `graphql_before_execute` | action |
| `Events::REQUEST_RESULTS` | `graphql_request_results` | filter |
| `Events::BEFORE_RESPONSE_RETURNED` | `graphql_return_response` | action |
| `Events::RESPONSE_HEADERS_TO_SEND` | `graphql_response_headers_to_send` | filter |

Before logging each of these, the logger asks the [Rule Manager](../rule-manager/index.md) whether this event should be logged at all.

## The Plugin facade

Most code only needs three static methods on `Plugin`:

- `Plugin::on( $event, $listener, $priority )` subscribes to an event.
- `Plugin::transform( $event, $transform, $priority )` registers a transform.
- `Plugin::emit( $event, $payload )` publishes an event of your own, for example from a custom resolver.

They call straight through to `EventManager`.

## Related documentation

- [How to use the Events Pub/Sub System](../../how-to/event-pub-sub/index.md)
- [How to add data to an Event](../../how-to/event-add-context/index.md)
