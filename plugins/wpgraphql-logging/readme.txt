=== WPGraphQL Logging ===
Contributors: wpengine
Tags: GraphQL, Headless, WPGraphQL, React, Rest, Logging, Performance, Debugging, Monitoring
Requires at least: 6.5
Tested up to: 6.8,2
Requires PHP: 8.1
Requires WPGraphQL: 2.3.0
Stable tag: 0.1.0
License: GPL-2.0
License URI: https://www.gnu.org/licenses/gpl-2.0.html

== Description ==

A WPGraphQL logging plugin that provides visibility into request lifecycle to help quickly identify and resolve bottlenecks in your headless WordPress application.

= Features =

- Query event lifecycle logging:
  - Pre Request (`do_graphql_request`): logs query, variables, operation name
  - Before Execution (`graphql_before_execute`): logs request params snapshot
  - After Execution (`graphql_execute`): logs response, schema, request
  - Before Response Returned (`graphql_return_response`): inspects response errors and upgrades level to Error when present

- Pub/sub system:
  - Subscribe with priorities, publish events, and apply transforms to mutate payloads
  - WordPress bridges: `wpgraphql_logging_event_{event}` (action) and `wpgraphql_logging_filter_{event}` (filter)

- Monolog-based storage and context:
  - Default handler writes to a WordPress table (`{$wpdb->prefix}wpgraphql_logging`)
  - Processors include memory usage, web request, process ID, and GraphQL request details

== Upgrade Notice ==
== Frequently Asked Questions ==
== Screenshots ==
== Changelog ==
