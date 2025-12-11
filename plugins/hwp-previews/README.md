# HWP Previews

**Headless Previews** solution for WordPress: fully configurable preview URLs via the settings page which is framework agnostic.

* [Join the Headless WordPress community on Discord.](https://discord.gg/headless-wordpress-836253505944813629)
* [Documentation](../../docs/plugins/hwp-previews/)


-----

[![Version](https://img.shields.io/github/v/release/wpengine/hwptoolkit?include_prereleases&label=prerelease&filter=%40wpengine%2Fhwp-previews-wordpress-plugin-*)](https://github.com/wpengine/hwptoolkit/releases)
[![License](https://img.shields.io/badge/license-GPLv2%2B-green)](https://www.gnu.org/licenses/gpl-2.0.html)
![GitHub forks](https://img.shields.io/github/forks/wpengine/hwptoolkit?style=social)
![GitHub stars](https://img.shields.io/github/stars/wpengine/hwptoolkit?style=social)
[![Testing Integration](https://github.com/wpengine/hwptoolkit/actions/workflows/codeception.yml/badge.svg)](https://github.com/wpengine/hwptoolkit/actions/workflows/codeception.yml)
[![Code Coverage](https://img.shields.io/badge/coverage-%3E95%25-brightgreen?label=Code%20Coverage)](https://github.com/wpengine/hwptoolkit/actions)
[![Code Quality](https://github.com/wpengine/hwptoolkit/actions/workflows/code-quality.yml/badge.svg)](https://github.com/wpengine/hwptoolkit/actions/workflows/code-quality.yml)
[![End-to-End Tests](https://github.com/wpengine/hwptoolkit/actions/workflows/e2e-test.yml/badge.svg)](https://github.com/wpengine/hwptoolkit/actions/workflows/e2e-test.yml)

-----

## Overview

HWP Previews is a robust and extensible WordPress plugin that centralizes all preview configurations into a user-friendly settings interface.
It empowers site administrators and developers to tailor preview behaviors for each public post type independently, facilitating seamless headless or decoupled workflows.
With HWP Previews, you can define dynamic URL templates, allow posts of all statuses to be used as parents, and extend functionality through flexible hooks and filters, ensuring a consistent and conflict-free preview experience across diverse environments.

## Motivation
In traditional WordPress, previewing content is straightforward: clicking the "Preview" button shows you a draft version of your post on the same WordPress site. However, in headless WordPress architectures, where the front-end is decoupled from WordPress, this simple mechanism breaks down. The front-end application lives on a different domain, knows nothing about WordPress authentication, and cannot automatically access unpublished content.

This fundamental architectural shift creates what we call the "preview problem" in headless WordPress. HWP Previews was created to bridge this gap, providing a centralized, framework-agnostic solution to preview management.

## Features

- **Enable/Disable Previews**: Turn preview functionality on or off for each public post type (including custom types).
- **Custom URL Templates**: Define preview URLs using placeholder tokens for dynamic content.
- **Parent Status**: Allow posts of **all** statuses to be used as parents within hierarchical post types.
- **Highly Customizable**: Extend core behavior with a comprehensive set of actions and filters.
- **Faust Compatibility**: The plugin is compatible with [Faust.js](https://faustjs.org/) and the [FaustWP plugin](https://github.com/wpengine/faustjs/tree/canary/plugins/faustwp).


>[!NOTE]
> For Faust users, HWP Previews integrates seamlessly, automatically configuring settings to match Faust's preview system. This allows you to maintain your existing preview workflow without additional setup.

## Requirements

- WordPress 6.0+
- PHP 7.4+

## Installation

### Option 1: Plugin Zip

You can get the latest release here - <https://github.com/wpengine/hwptoolkit/releases/latest/download/hwp-previews.zip>

You can also download it from our release page - <https://github.com/wpengine/hwptoolkit/releases>

### Option 2: Composer

To install, you need to follow our guide here to install the plugin via composer - <https://github.com/wpengine/hwptoolkit/blob/main/docs/how-to/install-toolkit-plugins/index.md>

Once you have the composer repository setup, please run `composer req wpengine/hwp-previews:*` to install the plugin.

## Documentation

For detailed usage instructions, developer references, and examples, visit our comprehensive documentation:

- [Documentation](../../docs/plugins/hwp-previews/index.md)
- [Examples](./examples/)

## Testing

See [Testing.md](TESTING.md) for details on how to test the plugin.

## Uninstallation

By default, HWP Previews preserves all settings when the plugin is deactivated to prevent accidental data loss.

If you would like to remove all plugin settings and data, you must set the PHP constant before you uninstall the plugin:

```php
define( 'HWP_PREVIEWS_UNINSTALL_PLUGIN', true );
```

You can add this constant to your `wp-config.php` file if you want to enable automatic cleanup during uninstallation.

## Screenshots

<details>
<summary>Click to expand screenshots</summary>

![Custom Post Type Preview](./screenshots/settings_page.png)
*Preview settings page.*

![Custom Post Type Preview](./screenshots/cpt_preview.png)
*Preview settings for a custom post type.*

![Post Preview](./screenshots/post_preview.png)
*Preview button in the WordPress editor.*

![Post Preview in Iframe](./screenshots/post_preview_iframe.png)
*Preview loaded inside the WordPress editor using an iframe.*

![Preview Token](./screenshots/preview_token.png)
*Preview token parameter for secure preview URLs.*

![App Password](./screenshots/app_password.png)
*App password setup for authentication.*
</details>

## License
WP GPL 2