# HWP CORS Local

Enables CORS (Cross-Origin Resource Sharing) headers for local headless WordPress development environments.

## Purpose

Allows frontend applications running on different ports or domains to access WordPress REST API endpoints during local development.

## Requirements

- WordPress 6.0+
- PHP 7.4+

## Configuration

Define the frontend URL using the `HEADLESS_FRONTEND_URL` constant in `wp-config.php` or via wp-env:

```php
define( 'HEADLESS_FRONTEND_URL', 'http://localhost:3000' );
```

Or in `.wp-env.json`:

```json
{
  "config": {
    "HEADLESS_FRONTEND_URL": "http://localhost:3000"
  }
}
```

## Behavior

- Only activates when `WP_ENVIRONMENT_TYPE` is `local` or `WP_DEBUG` is `true`
- Does not apply CORS headers if `HEADLESS_FRONTEND_URL` is not defined
- Handles preflight OPTIONS requests automatically
- Allows credentials and common HTTP methods (GET, POST, OPTIONS, PUT, DELETE)

## Headers Added

- `Access-Control-Allow-Origin`: Set to `HEADLESS_FRONTEND_URL` value
- `Access-Control-Allow-Methods`: GET, POST, OPTIONS, PUT, DELETE
- `Access-Control-Allow-Credentials`: true
- `Access-Control-Allow-Headers`: Content-Type, Authorization, X-Requested-With

## Security

This plugin should only be used in local development environments. It is automatically disabled in production when:
- `WP_ENVIRONMENT_TYPE` is not `local`
- `WP_DEBUG` is `false`

## Installation

### Via wp-env

Add to `.wp-env.json`:

```json
{
  "plugins": [
    "./path/to/hwp-cors-local"
  ]
}
```

### Manual Installation

1. Copy the plugin directory to `wp-content/plugins/` or `wp-content/mu-plugins/`
2. Activate from WordPress admin (if installed as regular plugin)

## License

GPL-2.0-or-later
