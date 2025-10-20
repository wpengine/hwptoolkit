# HWP WP-Env Helpers

[![Version](https://img.shields.io/github/v/release/wpengine/hwptoolkit?include_prereleases&label=version&filter=hwp-wp-env-helpers-*)](https://github.com/wpengine/hwptoolkit/releases)
[![License](https://img.shields.io/badge/license-GPLv2%2B-green)](https://www.gnu.org/licenses/gpl-2.0.html)
![GitHub forks](https://img.shields.io/github/forks/wpengine/hwptoolkit?style=social)
![GitHub stars](https://img.shields.io/github/stars/wpengine/hwptoolkit?style=social)

Fixes WordPress environment quirks specific to wp-env development environments.

## Purpose

Resolves REST API routing issues in wp-env by forcing the use of `?rest_route=` query parameter format instead of permalink-based routing. This prevents .htaccess-related conflicts in Docker environments.

## Requirements

- WordPress 6.0+
- PHP 7.4+

## Behavior

- Only activates when `WP_ENVIRONMENT_TYPE` is `local`
- Forces all REST API URLs to use query parameter format: `http://localhost:8888/?rest_route=/wp/v2/posts`
- Prevents permalink-based format: `http://localhost:8888/wp-json/wp/v2/posts`
- Automatically applied to all `rest_url()` calls throughout WordPress

## Technical Details

Hooks into the `rest_url` filter to transform REST API URLs. This ensures consistent REST API access in wp-env environments where permalink routing may not function correctly due to Docker volume mount behavior with `.htaccess` files.

## Installation

### Via wp-env

Add to `.wp-env.json`:

```json
{
  "plugins": [
    "./path/to/hwp-wp-env-helpers"
  ]
}
```

### Manual Installation

1. Copy the plugin directory to `wp-content/plugins/` or `wp-content/mu-plugins/`
2. Activate from WordPress admin (if installed as regular plugin)

## License

GPL-2.0-or-later
