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

@TODO add more info once we have configuration setup.


---

## Project Structure

```text
wpgraphql-logging/
├── docs/                       # Docs for extending the plugin.
├── src/                        # Main plugin source code
│   ├── Admin/                  # Admin settings, menu, and settings page logic
│   ├── Events/                 # Event logging, pub/sub event manager for extending the logging.
│   ├── Logging/                # Logging logic, logger service, Monolog handlers & processors
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
  - Default processors: Memory usage, memory peak, web request, process ID, and `WPGraphQLQueryProcessor` (adds `wpgraphql_query`, `wpgraphql_operation_name`, `wpgraphql_variables`).

- **Simple developer API**
  - `Plugin::on()` to subscribe, `Plugin::emit()` to publish, `Plugin::transform()` to modify payloads.

- **Safe-by-default listeners/transforms**
  - Exceptions in listeners/transforms are caught and logged without breaking the pipeline.

---

## Usage

WPGraphQL Logging Plugin is highly configurable and extendable and built with developers in mind to allow them to modify, change or add data, loggers etc to this plugin. Please read the docs below:


The following documentation is available in the `docs/` directory:

- [Events](docs/Events.md):
  Learn about the event system, available events, and how to subscribe, transform, or listen to WPGraphQL Logging events.

- [Logging](docs/Logging.md):
  Learn about the logging system, Monolog integration, handlers, processors, and how to use or extend the logger.

---



## Configuration

@TODO - When we integrate plugin configuration.

---

### Settings

@TODO - When we integrate plugin configuration.

---

## Testing

See [Testing.md](TESTING.md) for details on how to test the plugin.

## Screenshots

@TODO - When before BETA release.
