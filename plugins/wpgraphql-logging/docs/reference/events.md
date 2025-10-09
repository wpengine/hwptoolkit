## Events Reference

The WPGraphQL Logging plugin exposes a lightweight pub/sub system for WPGraphQL lifecycle events and bridges them to standard WordPress actions/filters.

---

### Class: `Events\Events`
Source: <https://github.com/wpengine/hwptoolkit/blob/main/plugins/wpgraphql-logging/src/Events/Events.php>

Constants that map to WPGraphQL core hooks:

- `Events::PRE_REQUEST` → `do_graphql_request`
- `Events::BEFORE_GRAPHQL_EXECUTION` → `graphql_before_execute`
- `Events::BEFORE_RESPONSE_RETURNED` → `graphql_return_response`
- `Events::REQUEST_DATA` → `graphql_request_data` (filter)
- `Events::RESPONSE_HEADERS_TO_SEND` → `graphql_response_headers_to_send` (filter)
- `Events::REQUEST_RESULTS` → `graphql_request_results` (filter)

Use these with the `Plugin` helpers or the `EventManager` directly.

@TODO See the how to guide on logging a new event.

---

### Class: `Events\EventManager`
Source: <https://github.com/wpengine/hwptoolkit/blob/main/plugins/wpgraphql-logging/src/Events/EventManager.php>

#### Action: `wpgraphql_logging_event_{event_name}`
Bridged WordPress action fired whenever an internal event is published. (and data logged)

Parameters:
- `$payload` (array) Published payload, typically includes `context` and sometimes `level`

Example:
```php
add_action( 'wpgraphql_logging_event_do_graphql_request', function( array $payload ) {
	// Do something with the payload.
}, 10, 1 );
```

#### Filter: `wpgraphql_logging_filter_{event_name}`
Bridged WordPress filter applied whenever an internal event payload is transformed and allow you to log data.

Parameters:
- `$payload` (array) Mutable payload; return the updated array

Returns: array

Example:
```php
add_filter( 'wpgraphql_logging_filter_graphql_return_response', function( array $payload ) {
    $payload['context']['wpgraphql-content-blocks'] = ['no_of_blocks' => 100];
    return $payload;
}, 10, 1 );
```

Programmatic API:

- Subscribe:
```php
use WPGraphQL\Logging\Plugin;
use WPGraphQL\Logging\Events\Events;

Plugin::on( Events::PRE_REQUEST, function( array $payload ): void {
    // ...
}, 5 );
```

- Transform:
```php
use WPGraphQL\Logging\Plugin;
use WPGraphQL\Logging\Events\Events;
use Monolog\Level;

Plugin::transform( Events::BEFORE_RESPONSE_RETURNED, function( array $payload ): array {
    $payload['context']['custom_key'] = 'custom_value';
    $payload['level'] = Level::Debug;
    return $payload;
}, 10 );
```


---

### Class: `Events\QueryActionLogger`
Source: <https://github.com/wpengine/hwptoolkit/blob/main/plugins/wpgraphql-logging/src/Events/QueryActionLogger.php>

Hooks into WPGraphQL actions and publishes/records events via the logger service.

#### Action: `do_graphql_request` (mapped from `Events::PRE_REQUEST`)
Logged as “WPGraphQL Pre Request”.

Parameters:
- `$query` (string|null)
- `$operation_name` (string|null)
- `$variables` (array|null)

Example:
```php
add_action( 'init', function() {
    \WPGraphQL\Logging\Plugin::on( \WPGraphQL\Logging\Events\Events::PRE_REQUEST, function( array $payload ): void {
        $ctx = $payload['context'] ?? [];
        // ...
    } );
} );
```

---


### Quick Start

```php
use WPGraphQL\Logging\Plugin;
use WPGraphQL\Logging\Events\Events;

// Subscribe (read-only)
Plugin::on( Events::PRE_REQUEST, function( array $payload ): void {
    // Inspect $payload['context']
}, 10 );

// Transform (mutate payload before it is logged/emitted)
Plugin::transform( Events::PRE_REQUEST, function( array $payload ): array {
    $payload['context']['env'] = wp_get_environment_type();
    return $payload;
}, 10 );
```

---

### Further Reading

- [WPGraphQL Documentation](https://www.wpgraphql.com/docs/)
- [WPGraphQL Actions and Filters](https://www.wpgraphql.com/docs/actions-and-filters/)
- [Observer Pattern](https://en.wikipedia.org/wiki/Observer_pattern)
- [Publish-Subscribe Pattern](https://en.wikipedia.org/wiki/Publish%E2%80%93subscribe_pattern)
- [WordPress Plugin API (Actions & Filters)](https://developer.wordpress.org/plugins/hooks/)
