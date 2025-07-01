# WPGraphQL Webhooks — Actions & Filters Reference

## 1. Action: `graphql_webhooks_init`

**Description:**  
Fires once when the WPGraphQL Webhooks plugin singleton instance is initialized. Useful for hooking into the plugin setup process to add custom initialization logic.

**Parameters:**  
- `$instance` (object): The singleton instance of the WPGraphQL Webhooks plugin.

**Example Usage:**

```php
add_action( 'graphql_webhooks_init', function( $instance ) {
    error_log( 'WPGraphQL Webhooks plugin initialized.' );
    // Custom initialization logic here
});
```

## 2. Filter: `graphql_webhooks_allowed_events`

**Description:**  
Filters the list of allowed webhook events. By default, this is an associative array of event keys and their labels. Use this filter to add, remove, or modify the events your webhooks can listen to.

**Parameters:**  
- `$events` (array): Associative array of event keys and labels.

**Returns:**  
Modified array of allowed events.

**Example Usage:**

```php
add_filter( 'graphql_webhooks_allowed_events', function( $events ) {
    // Add a custom event
    $events['custom_event'] = 'Custom Event';
    return $events;
});
```

## 3. Filter: `graphql_webhooks_allowed_methods`

**Description:**  
Filters the list of allowed HTTP methods for sending webhook requests. Defaults to `['POST', 'GET']`.

**Parameters:**  
- `$methods` (array): Array of allowed HTTP methods.

**Returns:**  
Modified array of allowed HTTP methods.

**Example Usage:**

```php
add_filter( 'graphql_webhooks_allowed_methods', function( $methods ) {
    // Allow PUT as an additional HTTP method
    $methods[] = 'PUT';
    return $methods;
});
```
## 4. Filter: `graphql_webhooks_validate_data`

**Description:**  
Filters the result of webhook data validation. This filter allows you to add custom validation logic or override the default validation outcome.

**Parameters:**  
- `$is_valid` (bool|WP_Error): Current validation result (`true` if valid, `WP_Error` if invalid).  
- `$event` (string): Event key of the webhook.  
- `$url` (string): Target URL of the webhook.  
- `$method` (string): HTTP method of the webhook.

**Returns:**  
`true` if valid, or a `WP_Error` object if invalid.

**Example Usage:**

```php
add_filter( 'graphql_webhooks_validate_data', function( $is_valid, $event, $url, $method ) {
    if ( strpos( $url, 'https://' ) !== 0 ) {
        return new WP_Error( 'invalid_url_scheme', 'Webhook URL must use HTTPS.' );
    }
    return $is_valid;
}, 10, 4 );
```

## 5. Filter: `graphql_webhooks_payload`

**Description:**  
Filters the payload data sent to the webhook URL before the HTTP request is made. Use this to modify, enrich, or sanitize the webhook payload.

**Parameters:**  
- `$payload` (array): The current payload data.  
- `$webhook` (Webhook): The webhook entity instance.

**Returns:**  
Modified payload array.

**Example Usage:**

```php
add_filter( 'graphql_webhooks_payload', function( $payload, $webhook ) {
    // Add a custom field to the payload
    $payload['custom_field'] = 'Custom Value';
    return $payload;
}, 10, 2 );
```

## 6. Filter: `graphql_webhooks_timeout`

**Description:**  
Filters the timeout (in seconds) used for the HTTP request when sending the webhook. Default is 15 seconds.

**Parameters:**  
- `$timeout` (int): Current timeout value.  
- `$webhook` (Webhook): The webhook entity instance.

**Returns:**  
Modified timeout value.

**Example Usage:**
```php
add_filter( 'graphql_webhooks_timeout', function( $timeout, $webhook ) {
    // Increase timeout for specific webhook
    if ( $webhook->name === 'Slow Endpoint' ) {
        return 30;
    }
    return $timeout;
}, 10, 2 );
```

## 7. Filter: `graphql_webhooks_sslverify`

**Description:**  
Filters whether SSL verification should be enabled when sending the webhook HTTP request. Defaults to `true`.

**Parameters:**  
- `$sslverify` (bool): Current SSL verification setting.  
- `$webhook` (Webhook): The webhook entity instance.

**Returns:**  
Modified SSL verification setting.

**Example Usage:**

```php
add_filter( 'graphql_webhooks_sslverify', function( $sslverify, $webhook ) {
    // Disable SSL verification for local development webhook
    if ( strpos( $webhook->url, 'localhost' ) !== false ) {
        return false;
    }
    return $sslverify;
}, 10, 2 );
```

## 8. Filter: `graphql_webhooks_test_mode`

**Description:**  
Filters whether the webhook HTTP request should be sent in blocking mode. This is useful for debugging or testing webhook delivery.

**Parameters:**  
- `$test_mode` (bool): Whether test mode is enabled (default: `false`).  
- `$webhook` (Webhook): The webhook entity instance.

**Returns:**  
`true` to enable blocking mode, `false` otherwise.

**Example Usage:**

```php
add_filter( 'graphql_webhooks_test_mode', function( $test_mode, $webhook ) {
    // Enable test mode for a specific webhook
    if ( $webhook->name === 'Test Webhook' ) {
        return true;
    }
    return $test_mode;
}, 10, 2 );
```

## 9. Action: `graphql_webhooks_sent`

**Description:**  
Fires after a webhook HTTP request has been sent. Useful for logging, debugging, or triggering additional side effects.

**Parameters:**  
- `$webhook` (Webhook): The webhook entity instance.  
- `$payload` (array): The payload sent to the webhook.  
- `$response` (WP_HTTP_Response|WP_Error): The response or error returned from the HTTP request.

**Example Usage:**

```php
add_action( 'graphql_webhooks_sent', function( $webhook, $payload, $response ) {
    if ( is_wp_error( $response ) ) {
        error_log( "Webhook '{$webhook->name}' failed: " . $response->get_error_message() );
    } else {
        error_log( "Webhook '{$webhook->name}' sent successfully with response code: " . wp_remote_retrieve_response_code( $response ) );
    }
}, 10, 3 );
```

## 10. Action: `graphql_webhooks_before_trigger`

**Description:**  
Fires before webhooks are triggered for a specific event. Useful for modifying payload or short-circuiting webhook triggers.

**Parameters:**  
- `$event` (string): The event key being triggered.  
- `$payload` (array): The payload data for the event.

**Example Usage:**
```php
add_action( 'graphql_webhooks_before_trigger', function( $event, &$payload ) {
    if ( $event === 'post_published' ) {
        // Add extra data before triggering webhooks
        $payload['extra_info'] = 'Additional context';
    }
}, 10, 2 );
```

## 11. Action: `graphql_webhooks_after_trigger`

**Description:**  
Fires after webhooks have been triggered for a specific event. Useful for cleanup or logging.

**Parameters:**  
- `$event` (string): The event key that was triggered.  
- `$payload` (array): The payload data that was sent.

**Example Usage:**
```php
add_action( 'graphql_webhooks_after_trigger', function( $event, $payload ) {
    error_log( "Completed triggering webhooks for event: $event" );
}, 10, 2 );
```