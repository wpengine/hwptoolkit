# WPGraphQL Logging

A WPGraphQL logging plugin that provides visibility into request lifecycle to help quickly identify and resolve bottlenecks in your headless WordPress application.

* [Join the Headless WordPress community on Discord.](https://discord.gg/headless-wordpress-836253505944813629)
* [Documentation](#getting-started)

> [!CAUTION]
> This plugin is currently in alpha state and is not production ready but please feel free to test.

-----

@TODO

-----



## Table of Contents

- [Overview](#overview)
- [Getting Started](#getting-started)
- [Features](#features)
- [Usage](#usage)
- [Configuration](#configuration)
- [Admin & Settings](#admin--settings)
- [Extending the Functionality](#extending-the-functionality)
- [Testing](#testing)


-----

## Overview


WPGraphQL Logging is a plugin that integrates directly with the WPGraphQL Query Lifecycle, capturing detailed information about each GraphQL request. By leveraging the powerful [Monolog](https://github.com/Seldaek/monolog) logging library, it records events and metrics that help you quickly identify performance bottlenecks and other issues in your headless WordPress application.

Designed with extensibility in mind, developers can easily customize and extend the logging functionality to suit their specific needs, making it a valuable tool for debugging and optimizing WPGraphQL-powered sites.

---


## Getting Started

To install, you need to follow our guide here to install the plugin via composer - [https://github.com/wpengine/hwptoolkit/blob/main/docs/how-to/install-toolkit-plugins/index.md]

Once you have the composer repository setup, please run `composer req wpengine/wpgraphql-logging:*` to install the plugin.

Plugin should start logging data, once activated.

---

## Uninstallation and Data Cleanup

By default, WPGraphQL Logging preserves all logged data when the plugin is deactivated to prevent accidental data loss. If you want to completely remove all plugin data (including database tables) when deactivating the plugin, you must explicitly enable this behavior.

### Enabling Database Cleanup on Deactivation

To enable automatic database cleanup when the plugin is deactivated, add the following constant to your `wp-config.php` file or in a must-use plugin:

```php
define( 'WP_GRAPHQL_LOGGING_UNINSTALL_PLUGIN', true );
```

> [!WARNING]
> **Data Loss Warning**: When `WP_GRAPHQL_LOGGING_UNINSTALL_PLUGIN` is defined as `true`, deactivating the plugin will permanently delete all logged data and drop the plugin's database tables. This action is irreversible.

### Manual Data Cleanup

If you prefer to manually clean up data without defining the constant, you can:

1. Use the plugin's admin interface to clear logs (when available)
2. Manually drop the database table: `{$wpdb->prefix}wpgraphql_logging`
3. Remove plugin options from the WordPress options table

---

@TODO add more info once we have configuration setup.

@TODO add more info once we have configuration setup.


---

## Project Structure

```text
wpgraphql-logging/
├── docs/                       # Docs for extending the plugin.
├── src/                        # Main plugin source code
│   ├── Admin/                  # Admin settings, menu, and settings page logic
│   	├── Settings/             # Admin settings functionality for displaying and saving data.
│   ├── Events/                 # Event logging, pub/sub event manager for extending the logging.
│   ├── Logger/                 # Logging logic, logger service, Monolog handlers & processors
│   	├── Rules/            		# Rule Management on whether we log a query
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

## Features

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

## Data Sanitization

WPGraphQL Logging includes robust data sanitization capabilities to help you protect sensitive information while maintaining useful logs for debugging and monitoring. The sanitization system allows you to automatically clean, anonymize, or remove sensitive fields from log records before they are stored.

### Why Data Sanitization Matters

When logging GraphQL requests, context data often contains sensitive information such as:
- User authentication tokens
- Personal identification information (PII)
- Password fields
- Session data
- Internal system information

Data sanitization ensures compliance with privacy regulations (GDPR, CCPA) and security best practices while preserving the debugging value of your logs.

### Sanitization Methods

The plugin offers two sanitization approaches:

#### 1. Recommended Rules (Default)
Pre-configured rules that automatically sanitize common WordPress and WPGraphQL sensitive fields:
- `request.app_context.viewer.data` - User data object
- `request.app_context.viewer.allcaps` - User capabilities
- `request.app_context.viewer.cap_key` - Capability keys
- `request.app_context.viewer.caps` - User capability array

#### 2. Custom Rules
Define your own sanitization rules using dot notation to target specific fields:

**Field Path Examples:**
```
variables.password
request.headers.authorization
user.email
variables.input.creditCard
```

### Sanitization Actions

For each field, you can choose from three sanitization actions:

| Action | Description | Example |
|--------|-------------|---------|
| **Remove** | Completely removes the field from logs | `password: "secret123"` → *field removed* |
| **Anonymize** | Replaces value with `***` | `email: "user@example.com"` → `email: "***"` |
| **Truncate** | Limits string length to 47 characters + `...` | `longText: "Very long text..."` → `longText: "Very long text that gets cut off here and mo..."` |

### Configuration

Enable and configure data sanitization through the WordPress admin:

1. Navigate to **GraphQL Logging → Settings**
2. Click the **Data Management** tab
3. Enable **Data Sanitization**
4. Choose your sanitization method:
   - **Recommended**: Uses pre-configured rules for common sensitive fields
   - **Custom**: Define your own field-specific rules

#### Custom Configuration Fields

When using custom rules, configure the following fields:

- **Fields to Remove**: Comma-separated list of field paths to completely remove
- **Fields to Anonymize**: Comma-separated list of field paths to replace with `***`
- **Fields to Truncate**: Comma-separated list of field paths to limit length

**Example Configuration:**
```
Remove: variables.password, request.headers.authorization
Anonymize: user.email, variables.input.personalInfo
Truncate: query, variables.input.description
```

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



## Configuration

@TODO - When we integrate plugin configuration.

---

### Settings

@TODO - When we integrate plugin configuration.

---

## Admin & Settings

See `docs/admin.md` for a full overview of the admin/settings architecture, hooks, and examples for adding tabs and fields.

## Testing

See [Testing.md](TESTING.md) for details on how to test the plugin.

## Screenshots

@TODO - When before BETA release.
