---
title: How To Guide: Event Pub/Sub System
description: Learn how to use the WPGraphQL Logging plugin's event pub/sub system to subscribe, transform, and emit events.
---

## How to use the WPGraphQL Logging events pub/sub system

The plugin exposes a lightweight pub/sub bus around key WPGraphQL lifecycle events and bridges them to standard WordPress actions/filters. You can:

* Subscribe (read-only) to observe event payloads
* Transform payloads before core code logs and emits them
* Publish your own custom events for your app/plugins

See the [Events Reference](../reference/events.md) for available built-in events and their mappings.

## Core concepts

* Subscribe (read-only): `Plugin::on( $event, callable $listener, $priority )`
* Transform (mutate): `Plugin::transform( $event, callable $transform, $priority )`
* Emit (publish): `Plugin::emit( $event, array $payload )`

Priorities run ascending (lower numbers first). Transforms must return the updated payload array; subscribers receive the payload and do not return.

## Programmatic API (recommended)

```php
<?php
use WPGraphQL\Logging\Plugin;
use WPGraphQL\Logging\Events\Events;
use Monolog\Level;

// 1) Subscribe (read-only) to inspect context for the pre-request event
add_action( 'init', function() {
    Plugin::on( Events::PRE_REQUEST, function( array $payload ): void {
        $context = $payload['context'] ?? [];
        // e.g., inspect or trigger side effects
        // error_log( 'Incoming operation: ' . ( $context['operation_name'] ?? '' ) );
    }, 10 );
} );

// 2) Transform the payload before it is logged/emitted by core code
add_action( 'init', function() {
    Plugin::transform( Events::BEFORE_RESPONSE_RETURNED, function( array $payload ): array {
        $payload['context']['env']       = wp_get_environment_type();

        // Optionally change severity for this event instance
        if ( ! empty( $payload['context']['errors'] ) ) {
            $payload['level'] = Level::Error;
        }
        return $payload;
    }, 5 ); // lower priority runs earlier
} );

// 3) Emit your own custom event anywhere in your code
add_action( 'user_register', function( $user_id ) {
    Plugin::emit( 'my_plugin/user_registered', [
        'context' => [ 'user_id' => (int) $user_id ],
    ] );
} );
```

Notes:

* Built-in events are transformed internally before they are logged and then published.
* `emit()` publishes to subscribers and the WordPress action bridge; it does not apply transforms by itself.
  * If you want the “transform then publish” pattern for your custom event, call `EventManager::transform( $event, $payload )` yourself before publishing.

## WordPress bridge (actions and filters)

For each event, the system also fires a WordPress action and applies a WordPress filter so you can interact without the PHP helpers.

* Action: `wpgraphql_logging_event_{event_name}` (fires after subscribers run)
* Filter: `wpgraphql_logging_filter_{event_name}` (used when core transforms a payload)

Examples:

```php
<?php
// Observe the raw pre-request payload via WordPress action
add_action( 'wpgraphql_logging_event_do_graphql_request', function( array $payload ) {
    // e.g., send metrics to an external system
}, 10, 1 );

// Inject extra context before the response is logged
add_filter( 'wpgraphql_logging_filter_graphql_return_response', function( array $payload ) {
    $payload['context']['trace_id']  = uniqid( 'trace_', true );
    $payload['context']['app_name']  = 'headless-site';
    return $payload;
}, 10, 1 );
```

## Practical example - Send data to external service

```php
<?php
use WPGraphQL\Logging\Plugin;
use WPGraphQL\Logging\Events\Events;

Plugin::on(Events::BEFORE_RESPONSE_RETURNED, function(array $payload): void {
	$context = $payload['context'];
	if (array_key_exists('errors', $context)) {
		$level = 400;
		$level_name = 'ERROR';
	} else {
		$level = 200;
		$level_name = 'INFO';
	}


	// Example call (replace values as needed)
	$result = send_log_to_external_api_endpoint( [
		'level'    => $level,
		'level_name' => $level_name,
		'query'    => $context['query'] ?? '',
		'context'  => $context,
		'message'  => 'Test'
	] );
}, 10);

```

> [!NOTE] > You can also add a custom handler if you want to log data to that service via the LoggerService.

## Troubleshooting

* If your transform isn’t taking effect, ensure you’re targeting the correct event and that your callable returns the modified array.
* If you only call `emit()`, transforms won’t run automatically; they only run where core calls `transform()`.
* Use priorities to control ordering with other plugins (`5` runs before `10`).
