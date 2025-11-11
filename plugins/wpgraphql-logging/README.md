# WPGraphQL Logging

A logging plugin that provides visibility into request lifecycle to help quickly identify and resolve bottlenecks in your headless WordPress application.

-----

[![Version](https://img.shields.io/github/v/release/wpengine/hwptoolkit?include_prereleases&label=prerelease&filter=%40wpengine%2Fwpgraphql-logging-wordpress-plugin-*)](https://github.com/wpengine/hwptoolkit/releases)
[![License](https://img.shields.io/badge/license-GPLv2%2B-green)](https://www.gnu.org/licenses/gpl-2.0.html)
![GitHub forks](https://img.shields.io/github/forks/wpengine/hwptoolkit?style=social)
![GitHub stars](https://img.shields.io/github/stars/wpengine/hwptoolkit?style=social)
[![Testing Integration](https://github.com/wpengine/hwptoolkit/actions/workflows/codeception.yml/badge.svg)](https://github.com/wpengine/hwptoolkit/actions/workflows/codeception.yml)
[![Code Coverage](https://img.shields.io/badge/coverage-%3E90%25-brightgreen?label=Code%20Coverage)](https://github.com/wpengine/hwptoolkit/actions)
[![Code Quality](https://github.com/wpengine/hwptoolkit/actions/workflows/code-quality.yml/badge.svg)](https://github.com/wpengine/hwptoolkit/actions/workflows/code-quality.yml)


-----

> [!CAUTION]
> This plugin is currently in a beta state. It's still under active development, so you may encounter bugs or incomplete features. Updates will be rolled out regularly. Use with caution and provide feedback if possible. You can create an issue at [https://github.com/wpengine/hwptoolkit/issues](https://github.com/wpengine/hwptoolkit/issues)

-----

## Overview


WPGraphQL Logging plugin provides observability and visibility into the GraphQL request and event lifecycle. This capability gives users the understandability needed to quickly identify and resolve performance issues and bottlenecks within their headless WordPress application.

### Key Features
*   **Granular Control**: Choose which events in the request lifecycle to log, giving you precise control over the data you capture.
*   **Developer-Friendly Extensibility**: Built with developers in mind, it features a pub/sub system that allows you to hook into the logging pipeline, transform event data, and trigger custom actions. 
*   **Flexible Log Handling**: Leverages the powerful Monolog logging library, enabling developers to add custom processors and handlers to route logs to various destinations like Slack, files, or external services.

>[!IMPORTANT]
>For detailed developer guides and examples, see our [How-To Guides](https://github.com/wpengine/hwptoolkit/blob/main/docs/plugins/wpgraphql-logging/index.md#how-to-guides).

---

## Requirements

- WordPress 6.5+
- WPGraphQL 2.3+
- PHP 8.1.2+


## Installation

### Option 1: Plugin Zip

You can get the latest release from <https://github.com/wpengine/hwptoolkit/releases?q=wpgraphql-logging&expanded=true>.

### Option 2: Composer

To install, you need to follow our guide here to install the plugin via composer - <https://github.com/wpengine/hwptoolkit/blob/main/docs/how-to/install-toolkit-plugins/index.md>

Once you have the composer repository setup, please run `composer req wpengine/wpgraphql-logging:*` to install the plugin.

Once installed and configured, the plugin should begin to log uncached WPGraphQL logs into the database.

---

## Documentation

For detailed usage instructions, developer references, and examples, please visit the [Documentation](https://github.com/wpengine/hwptoolkit/blob/main/docs/plugins/wpgraphql-logging/index.md) folder included with this plugin.


## License
WP GPL 2
