---
title: "How To Guide: Log WordPress REST API Requests"
description: "Turn on logging of WordPress REST API requests in the WPGraphQL Logging plugin, exclude routes, and tell REST entries apart from GraphQL entries."
---

The WPGraphQL Logging plugin can log WordPress REST API requests alongside GraphQL requests. REST entries go through the same Monolog pipeline (processors, handlers and data sanitization) and show up on the same admin screen.

REST logging is off by default. Turning it on does not change how GraphQL requests are logged.

> \[!NOTE]
> The plugin still requires WPGraphQL to be active, even if you only want to log REST requests.

## Turn on REST logging

1. Go to **GraphQL Logs -> Settings** and open the **Basic Configuration** tab.
2. Make sure **Enabled** is checked.
3. Check **Log REST API Requests**.
4. Optionally, list routes to skip in **Exclude REST Routes**.
5. Save the settings.

These Basic Configuration settings also apply to REST requests:

| Setting             | Effect on REST requests                                                     |
| ------------------- | --------------------------------------------------------------------------- |
| Enabled             | Master switch. REST requests are not logged when it is off.                 |
| IP Restrictions     | Only REST requests from these IPs are logged.                               |
| Data Sampling Rate  | Only this percentage of REST requests is logged.                            |
| Log Response        | Adds the response body to the entry. Error bodies are always logged.        |
| Exclude Queries     | Does not apply. Use **Exclude REST Routes** instead.                        |
| Log Points          | Does not apply. Each REST request produces one entry.                       |

## What gets logged

The plugin writes one entry for each REST request served over HTTP, just before the response is sent. CORS preflight (`OPTIONS`) requests are skipped.

Requests that are not served over HTTP are not logged. That covers PHP calls to `rest_do_request()`, embedded (`_embed`) sub-requests and block editor preloads. A batch request (`/batch/v1`) is logged as one entry.

Each entry has:

* **Channel**: `wpgraphql_logging_rest`. GraphQL entries use `wpgraphql_logging`.
* **Message**: the method and route, for example `WP REST GET /wp/v2/posts`. The **Event** column on the All Logs screen shows this message.
* **Level**: `INFO`, or `ERROR` when the response status is 400 or higher.
* **Context**:
  * `method`, `route` and `matched_route` (the registered route pattern, such as `/wp/v2/posts/(?P<id>[\d]+)`)
  * `params`: the parameters the client sent (URL, query string, form body and JSON body). Endpoint defaults are left out.
  * `status`: the HTTP status code
  * `duration_ms`: time from dispatch until the response is ready to send, in milliseconds
  * `errors`: the error body, when the status is 400 or higher
  * `response`: the response body, when **Log Response** is on and the request succeeded

The usual `extra` data (memory usage, request headers, process ID and so on) is added by the default processors.

## Exclude routes

**Exclude REST Routes** takes a comma-separated list of routes. Each entry matches that route and every route below it, ignoring case and leading or trailing slashes.

```text
/wp/v2/users, /wp-site-health/v1
```

With this value, `/wp/v2/users` and `/wp/v2/users/me` are skipped, but `/wp/v2/users-extra` and `/wp/v2/posts` are still logged.

## Sanitize sensitive data

When **Data Sanitization** uses the **Recommended** rules, the plugin anonymizes `params.username`, `params.password`, `params.email` and `response.password` in REST entries.

For anything else, choose **Custom** rules on the **Data Management** tab and use dot notation against the context. For example, `params.token` or `params.billing.phone`.

## Tell REST and GraphQL entries apart

The channel column is stored in the database and can be filtered on. To show only REST entries on the All Logs screen, add a where clause:

```php
add_filter( 'wpgraphql_logging_logs_table_where_clauses', function ( array $where ) {
	$where[] = [
		'column'   => 'channel',
		'operator' => '=',
		'value'    => \WPGraphQL\Logging\Events\RestRequestLogger::CHANNEL,
	];
	return $where;
} );
```

The channel is also included when you download a log as CSV.

## Customize which REST requests are logged

REST requests use their own rule set, filtered with `wpgraphql_logging_rest_rules`. Rules receive the REST route in place of the GraphQL query string. See [How to add or remove a rule](../logger-add-remove-rules/index.md) for how to write a rule.

```php
add_filter( 'wpgraphql_logging_rest_rules', function ( array $rules ) {
	$rules[] = new MyPlugin\Logging\Rules\OnlyCustomNamespaceRule();
	return $rules;
} );
```

The `wpgraphql_logging_is_enabled` filter runs for both GraphQL and REST requests.

## Change or listen to REST entries

REST entries go through the event system under `Events::REST_REQUEST_SERVED`, so you can transform or subscribe to them like GraphQL events. See [How to use the pub/sub event system](../event-pub-sub/index.md).

```php
use WPGraphQL\Logging\Events\Events;
use WPGraphQL\Logging\Plugin;

Plugin::transform( Events::REST_REQUEST_SERVED, function ( array $payload ): array {
	$payload['context']['site_region'] = 'eu-west';
	return $payload;
} );
```
