# WPGraphQL Logging

## Table of Contents

@TODO - Regenerate

- [Overview](#overview)
- [Getting Started](#getting-started)
- [Features](#features)
- [Usage](#usage)
- [Configuration](#configuration)
- [Admin & Settings](#admin--settings)
- [Extending the Functionality](#extending-the-functionality)
- [Testing](#testing)

---


## Project Structure

```text
wpgraphql-logging/
├── docs/                       # Docs for extending the plugin. Contains developer docs.
├── src/                        # Main plugin source code
│   ├── Admin/                  # Admin settings, menu, and settings page logic
│   	├── Settings/             # Admin settings functionality for displaying and saving data.
│   ├── Events/                 # Event logging, pub/sub event manager for extending the logging.
│   ├── Logger/                 # Logging logic, logger service, Monolog handlers & processors
│   	├── Database/            	# Database Entity and Helper
│   	├── Handlers/            	# Monolog WordPress Database Handler for logging data
│   	├── Processors/           # Monolog Processors for data sanitzation and adding request headers.
│   	├── Rules/            		# Rules and Rule Manager on whether we log a query
│   	├── Scheduler/            # Automated data cleanup and maintenance tasks
│   ├── Plugin.php              # Main plugin class (entry point)
│   └── Autoload.php            # PSR-4 autoloader
├── tests/                      # All test suites
│   ├── wpunit/                 # WPBrowser/Codeception unit tests
├── [wpgraphql-logging.php]
├── [activation.php]
├── [composer.json]
├── [deactivation.php]
├── [TESTING.md]
├── [README.md]
```

---

## Key Features

@TODO

- **Query event lifecycle logging**
  - **Pre Request** (`do_graphql_request`): captures `query`, `variables`, `operation_name`.
  - **Before Execution** (`graphql_before_execute`): includes a snapshot of request `params`.
  - **Before Response Returned** (`graphql_return_response`): inspects `response` and automatically upgrades level to Error when GraphQL `errors` are present (adds `errors` to context when found).

- **Built-in pub/sub event bus**
  - In-memory event manager with priorities: `subscribe(event, listener, priority)` and `publish(event, payload)`.
  - Transform pipeline: `transform(event, payload)` lets you mutate `context` and `level` before logging/publishing.
  - WordPress bridges: actions `wpgraphql_logging_event_{event}` and filters `wpgraphql_logging_filter_{event}` to integrate with standard hooks.

- **Monolog-powered logging pipeline**
  - Default handler: stores logs in a WordPress table (`{$wpdb->prefix}wpgraphql_logging`).

- **Automated data management**
  - **Daily cleanup scheduler**: Automatically removes old logs based on retention settings.
  - **Configurable retention period**: Set how many days to keep log data (default: 30 days).
  - **Manual cleanup**: Admin interface to trigger immediate cleanup of old logs.
  - **Data sanitization**: Remove sensitive fields from logged data for privacy compliance.

- **Simple developer API**
  - `Plugin::on()` to subscribe, `Plugin::emit()` to publish, `Plugin::transform()` to modify payloads.

- **Safe-by-default listeners/transforms**
  - Exceptions in listeners/transforms are caught and logged without breaking the pipeline.

---

## Setup

Once the plugin is activated, you can activate and configure the plugin under Settings -> WPGraphQL Logging

### Basic Configuration

![Basic Configuration](screenshots/admin_configuration_basic.png)

- **Enabled**: The master switch to turn logging on or off.
- **IP Restrictions**: A comma-separated list of IPv4/IPv6 addresses. When set, only requests originating from these IPs will be logged. This is particularly useful for developers who wish to log only their own queries.
- **Exclude Queries**: A comma-separated list of GraphQL query or mutation names to be excluded from logging. This helps reduce noise by ignoring frequent or uninteresting operations.
- **Admin User Logging**: A toggle to control whether queries made by users with administrative privileges are logged.
- **Data Sampling Rate**: A dropdown to select the percentage of requests that will be logged. This is useful for managing log volume on high-traffic sites by only capturing a sample of the total requests.
- **Log Points**: A multi-select field to choose the specific WPGraphQL lifecycle events for which data should be logged.
- **Log Response**: A toggle to determine whether the GraphQL response body should be included in the log. Disabling this can reduce the size of your log data.

>[Note]
> The configuration for these rules are set in a rule manager service which checks to see if a event should be logged, based on whether it passes all rules or not. More docs on the rule manager can be found here @TODO

You want to add a new rule. See our guide here @TODO


### Data Management

![Data Management](screenshots/admin_configuration_data_management.png)

- **Enable Data Deletion**: A toggle to enable a daily WP-Cron job that automatically deletes old log entries based on the retention period.
- **Log Retention Period**: Specify the number of days to keep log data before it is automatically deleted.
- **Enable Data Sanitization**: The master switch to turn data sanitization on or off. When enabled, sensitive data is cleaned from logs before being stored.
- **Data Sanitization Method**: Choose between two sanitization methods:
	- **Recommended Rules (Default)**: Uses pre-configured rules to automatically sanitize common sensitive fields in WordPress and WPGraphQL. The following fields are sanitized:
		- `request.app_context.viewer.data` (User data object)
		- `request.app_context.viewer.allcaps` (User capabilities)
		- `request.app_context.viewer.cap_key` (Capability keys)
		- `request.app_context.viewer.caps` (User capability array)
	- **Custom Rules**: Provides granular control over sanitization with the following options:
		- **Fields to Remove**: A comma-separated list of field paths (e.g., `request.app_context.viewer.data`) to completely remove from the log.
		- **Fields to Anonymize**: A comma-separated list of field paths whose values will be replaced with `***`.
		- **Fields to Truncate**: A comma-separated list of field paths whose string values will be truncated to 50 characters.


## Viewing Logs

Once configured to log data you can find logs under "GraphQL Logs" in the WordPress Menu.

![Admin View](screenshots/admin_view.png)

This extends the WordPress WP List Table class but you can do the following.

### Download the log

You can download the log as CSV format e.g.

```csv
ID,Date,Level,"Level Name",Message,Channel,Query,Context,Extra
5293,"2025-10-06 15:41:34",200,INFO,"WPGraphQL Response",wpgraphql_logging,"{ posts(first: 10) ...""memory_peak_usage"":""18 MB""}"
```


### Filtering Logs

You can filter the log by

1. Level
2. Start Date
3. End Date

![Admin View with Filters](screenshots/admin_view_filters.png)

>[Note]
> We only show the `info` and `error` levels as these are the only levels logged out of the box. If you need to change this, you can update the admin template. See @TODO


### Bulk Actions

Currently you can delete selected or all logs.

If you want to customize this. @TODO



## Uninstallation and Data Cleanup

By default, WPGraphQL Logging preserves all logged data when the plugin is deactivated to prevent accidental data loss. If you want to completely remove all plugin data (including database tables) when deactivating the plugin, you must explicitly enable this behavior.

### Enabling Database Cleanup on Deactivation

To enable automatic database cleanup when the plugin is deactivated, add the following constant to your `wp-config.php` file or in a must-use plugin:

```php
define( 'WP_GRAPHQL_LOGGING_UNINSTALL_PLUGIN', true );
```

> [!WARNING]
> **Data Loss Warning**: When `WP_GRAPHQL_LOGGING_UNINSTALL_PLUGIN` is defined as `true`, deactivating the plugin will permanently delete all logged data and drop the plugin's database tables. This action is irreversible.


## Reference

The plugin is developer focussed and can be extended in multiple ways.

## Admin - (admin configuration, view and functionality)

- [Actions/Filters](reference/admin.md)

@TODO - How to guides

## Events - (Event Management, Adding and extending events, Using the pub/sub)
- [Actions/Filters](reference/events.md)

@TODO - How to guides

## Logging -  (Logging Service, Monolog Handlers & Processors, Rule Manager, Data Management)
- [Actions/Filters](reference/logging.md)

@TODO - How to guides


---- 

# Old Notes

- Need to be refactored.






### Developer Hooks

Customize sanitization behavior using WordPress filters:

```php
// Enable/disable sanitization programmatically
add_filter( 'wpgraphql_logging_data_sanitization_enabled', function( $enabled ) {
	return current_user_can( 'manage_options' ) ? false : $enabled;
});

// Modify recommended rules
add_filter( 'wpgraphql_logging_data_sanitization_recommended_rules', function( $rules ) {
	$rules['custom.sensitive.field'] = 'remove';
	return $rules;
});

// Modify all sanitization rules
add_filter( 'wpgraphql_logging_data_sanitization_rules', function( $rules ) {
	// Add additional rules or modify existing ones
	$rules['request.custom_header'] = 'anonymize';
	return $rules;
});

// Modify the final log record after sanitization
add_filter( 'wpgraphql_logging_data_sanitization_record', function( $record ) {
	// Additional processing after sanitization
	return $record;
});
```

### Performance Considerations

- Sanitization runs on every log record when enabled
- Complex nested field paths may impact performance on high-traffic sites
- Consider using recommended rules for optimal performance
- Test custom rules thoroughly to ensure they target the intended fields

### Security Best Practices

1. **Review logs regularly** to ensure sanitization is working as expected
2. **Test field paths** in a development environment before applying to production
3. **Use remove over anonymize** for highly sensitive data
4. **Monitor performance impact** when implementing extensive custom rules
5. **Keep rules updated** as your GraphQL schema evolves

---

## Usage

WPGraphQL Logging Plugin is highly configurable and extendable and built with developers in mind to allow them to modify, change or add data, loggers etc to this plugin. Please read the docs below:


The following documentation is available in the `docs/` directory:

- [Events](docs/Events.md):
  Learn about the event system, available events, and how to subscribe, transform, or listen to WPGraphQL Logging events.

- [Logging](docs/Logging.md):
  Learn about the logging system, Monolog integration, handlers, processors, and how to use or extend the logger.

- [Admin](docs/admin.md):
  Learn how the admin settings page works, all available hooks, and how to add tabs/fields via actions and filters.

---



---

## Admin & Settings

See `docs/admin.md` for a full overview of the admin/settings architecture, hooks, and examples for adding tabs and fields.

## Testing

See [Testing.md](TESTING.md) for details on how to test the plugin.

## Screenshots

@TODO - When before BETA release.



### Manual Data Cleanup

If you prefer to manually clean up data without defining the constant, you can:

1. Use the plugin's admin interface to clear logs (when available)
2. Manually drop the database table: `{$wpdb->prefix}wpgraphql_logging`
3. Remove plugin options from the WordPress options table

---

@TODO add more info once we have configuration setup.

@TODO add more info once we have configuration setup.
