=== WPGraphQL Logging ===
Contributors: ahuseyn, colin-murphy, joefusco, thdespou, wpengine
Tags: GraphQL, Headless, WPGraphQL, React, Rest, Logging, Performance, Debugging, Monitoring
Requires at least: 6.5
Tested up to: 6.8.2
Requires PHP: 8.1.2
Requires WPGraphQL: 2.3.0
Stable tag: 1.0.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

WPGraphQL Logging plugin provides observability and visibility into the GraphQL request and event lifecycle. This allows users to quickly identify and resolve performance issues and bottlenecks within their headless WordPress application.

== Description ==

**WPGraphQL Logging** is a comprehensive logging solution for WPGraphQL that tracks and records GraphQL query execution, providing developers with detailed insights into query performance, errors, and request lifecycle events.

= Key Features =

**GraphQL Request Lifecycle Logging**
* Pre Request (`do_graphql_request`): Captures query text, variables, and operation name
* Before Execution (`graphql_before_execute`): Records request parameter snapshot
* After Execution (`graphql_execute`): Logs response data, schema context, and request details
* Before Response (`graphql_return_response`): Inspects response for errors and automatically escalates log level when errors are present

**Advanced Admin Interface**
* View and filter all logged GraphQL requests in the WordPress admin
* Search and sort by date, query name, log level, or custom criteria
* Export logs to CSV for offline analysis
* Configurable data retention policies with automatic cleanup

**Flexible Event System**
* Built-in pub/sub architecture for subscribing to logging events
* Priority-based event handling
* Transform and mutate log payloads before storage
* WordPress action/filter bridges: `wpgraphql_logging_event_{event}` and `wpgraphql_logging_filter_{event}`

**Powerful Storage & Context**
* Built on Monolog for reliable, industry-standard logging
* Default database handler stores logs in WordPress table (`{$wpdb->prefix}wpgraphql_logging`)
* Extensible processor system includes:
  * Memory usage tracking
  * Web request context
  * Process ID tracking
  * GraphQL-specific request metadata

**Customization Options**
* Add custom log processors via filters
* Implement custom storage handlers
* Define custom logging rules and conditions
* Extend the admin interface with custom fields and tabs

= Use Cases =

* **Performance Monitoring**: Identify slow queries and execution bottlenecks
* **Error Tracking**: Monitor and debug GraphQL errors in production
* **Development & Testing**: Track query behavior during development
* **Compliance & Auditing**: Maintain records of API access and usage
* **Analytics**: Analyze query patterns and usage trends

= Requirements =

* WordPress 6.5 or higher
* PHP 8.1.2 or higher
* WPGraphQL 2.3.0 or higher

= Documentation =

For detailed documentation, guides, and examples, visit the [GitHub repository](https://github.com/wpengine/hwptoolkit/tree/main/plugins/wpgraphql-logging).

== Installation ==

1. Upload the plugin files to `/wp-content/plugins/wpgraphql-logging/`, or install the plugin through the WordPress plugins screen directly
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Navigate to GraphQL Logs -> Settings in the WordPress admin to configure settings
4. View logged queries can be found in GraphQL Logs -> All Logs.

== Frequently Asked Questions ==

= Is this plugin production-ready? =

Yes. We recommend thorough testing on a local or staging before deploying to production. By default the plugin will only log 10% of the initial requests.

= Does this plugin affect GraphQL query performance? =

The plugin is designed to have minimal performance impact. Logging operations are performed asynchronously where possible, and you can configure data retention policies to manage database size.

= Can I export my logs? =

Yes! The admin interface includes CSV export functionality for all filtered log entries.

= Can I customize what gets logged? =

Yes, the plugin provides extensive hooks and filters to customize logging behavior, add custom processors, and implement custom storage solutions.

= How do I delete old logs? =

The plugin includes configurable data retention settings. You can set automatic cleanup rules to delete logs older than a specified number of days.

= Where can I find documentation? =
For detailed usage instructions, developer references, and examples, please visit the [Documentation](https://github.com/wpengine/hwptoolkit/blob/main/docs/plugins/wpgraphql-logging/index.md) folder included with this plugin.


== Changelog ==

= 1.0.0 - 2025-11-14 =
* Initial release
* Core logging functionality for WPGraphQL request lifecycle
* Admin interface for viewing and filtering logs
* CSV export functionality
* Configurable data retention and cleanup
* Extensible event system with pub/sub architecture
* Monolog-based storage with custom processors

== Upgrade Notice ==

= 1.0.0 =
Initial release.

= 0.1.0 =
Initial BETA release.

== Support ==

For support, feature requests, or bug reports, please visit our [GitHub issues page](https://github.com/wpengine/hwptoolkit/issues).
