# WPGraphQL Logging

A WPGraphQL logging plugin that provides visibility into request lifecycle to help quickly identify and resolve bottlenecks in your headless WordPress application.

> [!CAUTION]
> This plugin is currently in beta state and is not production ready but please feel free to test.

-----

[![Version](https://img.shields.io/github/v/release/wpengine/hwptoolkit?include_prereleases&label=wpgraphql-logging&filter=%40wpengine%2Fwpgraphql-logging-wordpress-plugin-*)](https://github.com/wpengine/hwptoolkit/releases)
[![License](https://img.shields.io/badge/license-GPLv2%2B-green)](https://www.gnu.org/licenses/gpl-2.0.html)
![GitHub forks](https://img.shields.io/github/forks/wpengine/hwptoolkit?style=social)
![GitHub stars](https://img.shields.io/github/stars/wpengine/hwptoolkit?style=social)
[![Testing Integration](https://img.shields.io/github/check-runs/wpengine/hwptoolkit/main?checkName=wpgraphql-logging%20codeception%20tests&label=Automated%20Tests)](https://github.com/wpengine/hwptoolkit/actions)
[![Code Coverage](https://img.shields.io/badge/coverage-%3E90%25-brightgreen?label=Code%20Coverage)](https://github.com/wpengine/hwptoolkit/actions)
[![Code Quality](https://img.shields.io/github/check-runs/wpengine/hwptoolkit/main?checkName=wpgraphql-logging%20php%20code%20quality%20checks&label=Code%20Quality%20Checks)](https://github.com/wpengine/hwptoolkit/actions)


-----

## Overview


WPGraphQL Logging is a plugin that integrates directly with the WPGraphQL Query Lifecycle, capturing detailed information about each GraphQL request.

### Key Features
*   **Granular Control**: Choose which events in the request lifecycle to log, giving you precise control over the data you capture.
*   **Developer-Friendly Extensibility**: Built with developers in mind, it features a pub/sub system that allows you to hook into the logging pipeline, transform event data, and trigger custom actions.
*   **Flexible Log Handling**: Leverages the powerful Monolog logging library, enabling developers to add custom processors and handlers to route logs to various destinations like Slack, files, or external services.


---

## Requirements

- WordPress 6.0+
- WPGraphQL 2.0.0+
- PHP 8.1.2+
- WPGraphQL 2.3+


## Installation

### Option 1: Plugin Zip

You can get the latest release from <https://github.com/wpengine/hwptoolkit/releases?q=wpgraphql-logging&expanded=true>.

### Option 2: Composer

To install, you need to follow our guide here to install the plugin via composer - <https://github.com/wpengine/hwptoolkit/blob/main/docs/how-to/install-toolkit-plugins/index.md>

Once you have the composer repository setup, please run `composer req wpengine/wpgraphql-logging:*` to install the plugin.

Once installed and configured, the plugin should begin to log uncached WPGraphQL logs into the database.

---

## Documentation

For detailed usage instructions, developer references, and examples, please visit the [Documentation](docs/index.md) folder included with this plugin.


## License
WP GPL 2
