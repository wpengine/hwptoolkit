# Events in WPGraphQL Logging

## Table of Contents

- [Overview](#overview)
- [Available Events](#available-events)
- [Usage](#usage)
  - [Example 1: How to subscribe to an event](#example-1-how-to-subscribe-to-an-event)
  - [Example 2: How to add context to an event](#example-2-how-to-add-context-to-an-event)
  - [Example 3: How to run a WPGraphQL event](#example-3-how-to-run-a-wpgraphql-event)
  - [Example 4: Use WordPress hooks](#example-4-use-wordpress-hooks)

## Overview

WPGraphQL Logging implements a pub/sub pattern for events to subscribe to certain WPGraphQL events and allows users to subscribe/publish or transform events.

This is achieved in the following classes under `src/Events/`:

- **Events** - List of events the plugin hooks into for WPGraphQL
- **EventManager** - An event manager which creates a pub/sub pattern to allow users to subscribe/publish events and also transform context or level for the current event
- **QueryEventLifecycle** - The service that puts this all together and creates the logic and logs the data into the LoggerService (Monolog logger)

> **Note**: If we are missing anything from this event system, please feel free to create an issue or contribute.

## Available Events

Currently we subscribe to the following WPGraphQL events:

| Event Constant | WPGraphQL Hook | Description |
| --- | --- | --- |
| `Events::PRE_REQUEST` | `do_graphql_request` | Before the request is processed |
| `Events::BEFORE_GRAPHQL_EXECUTION` | `graphql_before_execute` | Before query execution |
| `Events::BEFORE_RESPONSE_RETURNED` | `graphql_return_response` | Before response is returned to client |

## Usage

### Example 1: How to subscribe to an event

**Use case** You would like to access data for a specific event.

**Example**


```php
<?php
use WPGraphQL\Logging\Plugin;
use WPGraphQL\Logging\Events\Events;

add_action('init', function () {
    Plugin::on(Events::PRE_REQUEST, function(array $payload): void {
        $context = $payload['context']; // array
		$level = $payload['level']; // string
		// Custom logic
    }, 10);
});
```

## Example 2: How to add context to an event

**Use case** You would like to add some custom data as a third party plugin or as a developer to be logged as part of the lifecycle.

**Example**

```php
<?php
use WPGraphQL\Logging\Plugin;
use WPGraphQL\Logging\Events\Events;
use Monolog\Level;

add_action('init', function () {
    Plugin::transform(Events::BEFORE_RESPONSE_RETURNED, function(array $payload): array {

		// Add some custom context
		$payload['context']['custom_key'] = 'custom_value';

		// Set the level to debug
		$payload['level'] = Level::Debug;

        return $payload;
    }, 10);
});
```

### Example 3: How to run a WPGraphQL event

**Use case:** The current list of events that the plugin subscribes too does not give enough information or you need to subscribe to a particular event which is problematic.

**Example**

Currently the plugin logs at three points in the WPGraphQL lifecycle: `do_graphql_request`, `graphql_before_execute`, `graphql_return_response`. If you need more visibility you could do the following:

```php
use WPGraphQL\Logging\Logger\LoggerService;

add_action('graphql_pre_resolve_field', function($source, $args, $context, $info) {
    LoggerService::get_instance()->info('Resolving field', [
        'field' => $info->fieldName ?? '',
        'type'  => method_exists($info, 'parentType') ? (string) $info->parentType : '',
    ]);
}, 10, 4);

```

### Example 4. Use WordPress hooks

In addition to the internal API, every event also triggers standard WordPress hooks:

- Action: `wpgraphql_logging_event_{event_name}` receives the published payload
- Filter: `wpgraphql_logging_filter_{event_name}` can modify the payload before logging

 ```php
 <?php
 // Listen via WordPress action
 add_action('wpgraphql_logging_event_do_graphql_request', function(array $payload) {
     // ...
 }, 10, 1);

 // Transform via WordPress filter
 add_filter('wpgraphql_logging_filter_graphql_return_response', function(array $payload) {
     // ... mutate $payload
     return $payload;
 }, 10, 1);
 ```

## Further Reading

- [WPGraphQL Documentation](https://www.wpgraphql.com/docs/)
- [WPGraphQL Actions and Filters](https://www.wpgraphql.com/docs/actions-and-filters/)
- [Observer Pattern](https://en.wikipedia.org/wiki/Observer_pattern)
- [Publish-Subscribe Pattern](https://en.wikipedia.org/wiki/Publish%E2%80%93subscribe_pattern)
- [WordPress Plugin API (Actions & Filters)](https://developer.wordpress.org/plugins/hooks/)
- [Event-Driven Architecture](https://martinfowler.com/articles/201701-event-driven.html)
