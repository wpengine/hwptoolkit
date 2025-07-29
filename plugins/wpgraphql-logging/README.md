# WPGraphQL Logging

A WPGraphQL logging plugin that provides visibility into request lifecycle to help quickly identify and resolve bottlenecks in your headless WordPress application.

* [Join the Headless WordPress community on Discord.](https://discord.gg/headless-wordpress-836253505944813629)
* [Documentation](#getting-started)

> [!CAUTION]
> This plugin is currently in development state and is not production ready.

-----

@TODO - Badges

-----



## Table of Contents

- [Overview](#overview)
- [Features](#features)
- [Getting Started](#getting-started)
- [Configuration](#configuration)
- [Extending the Functionality](#extending-the-functionality)
- [Testing](#testing)


-----

## Overview


WPGraphQL Logging is a plugin that integrates directly with the WPGraphQL Query Lifecycle, capturing detailed information about each GraphQL request. By leveraging the powerful [Monolog](https://github.com/Seldaek/monolog) logging library, it records events and metrics that help you quickly identify performance bottlenecks and other issues in your headless WordPress application.

Designed with extensibility in mind, developers can easily customize and extend the logging functionality to suit their specific needs, making it a valuable tool for debugging and optimizing WPGraphQL-powered sites.

---

## Features

@TODO


## Getting Started

@TODO


---

## Project Structure

```text
wpgraphql-logging/
├── src/                        # Main plugin source code
│   ├── Admin/                  # Admin settings, menu, and settings page logic
│   ├── Events/                 # Event definitions and event dispatcher logic
│   ├── Hooks/                  # WordPress hooks and filters
│   ├── Logging/                # Logging logic, logger service, Monolog handlers & processors
│   ├── Plugin.php              # Main plugin class (entry point)
│   └── Autoload.php            # PSR-4 autoloader
├── tests/                      # All test suites
│   ├── wpunit/                 # WPBrowser/Codeception unit tests
├── [wpgraphql-logging.php]
├── [activation.php]
├── [composer.json]
├── [deactivation.php]
├── [ACTIONS_AND_FILTERS.md]
├── [TESTING.md]
├── [README.md]
```

## Configuration

@TODO - When we integrate plugin configuration.

### Settings

@TODO - When we integrate plugin configuration.

---

## Actions & Filters

See the [Actions & Filters documentation](ACTIONS_AND_FILTERS.md) for a comprehensive list of available hooks and how to use them.

---

## Testing

See [Testing.md](TESTING.md) for details on how to test the plugin.

## Screenshots

@TODO - When before BETA release.
