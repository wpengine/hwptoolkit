# HWP Frontend Links

[![Version](https://img.shields.io/github/v/release/wpengine/hwptoolkit?include_prereleases&label=version&filter=hwp-frontend-links-*)](https://github.com/wpengine/hwptoolkit/releases)
[![License](https://img.shields.io/badge/license-GPLv2%2B-green)](https://www.gnu.org/licenses/gpl-2.0.html)
![GitHub forks](https://img.shields.io/github/forks/wpengine/hwptoolkit?style=social)
![GitHub stars](https://img.shields.io/github/stars/wpengine/hwptoolkit?style=social)

Adds "View on Frontend" links to WordPress admin interface for headless WordPress sites.

## Purpose

Provides quick access to view content on the headless frontend application from within the WordPress admin dashboard.

## Requirements

- WordPress 6.0+
- PHP 7.4+

## Configuration

### Single Frontend

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

### Multiple Frontends

For projects with multiple frontend environments (staging, production, etc.), use the `HWP_FRONTEND_LINKS` constant to define multiple targets:

```php
define( 'HWP_FRONTEND_LINKS', [
  [ 'label' => 'Production', 'url' => 'https://example.com' ],
  [ 'label' => 'Staging', 'url' => 'https://staging.example.com' ],
  [ 'label' => 'Local Dev', 'url' => 'http://localhost:3000' ]
] );
```

Each frontend link will appear as a separate "View in [Label]" option in both the admin bar and row actions.

## Features

### Admin Bar Link

Adds "View on Frontend" link to the WordPress admin bar when viewing a post or page. Opens in a new tab.

### Post/Page Row Actions

Adds "View on Frontend" link to the row actions in the Posts and Pages list tables.

### URL Construction

By default, constructs frontend URLs using the post slug:
```
{HEADLESS_FRONTEND_URL}/{post_name}
```

## Customization

### Custom URL Paths

Filter the path construction for specific post types or custom logic:

```php
add_filter( 'hwp_frontend_links_post_path', function( $path, $post ) {
    // Custom path for 'product' post type
    if ( $post->post_type === 'product' ) {
        return '/shop/' . $post->post_name;
    }

    return $path;
}, 10, 2 );
```

### Example Patterns

```php
// Use post ID instead of slug
add_filter( 'hwp_frontend_links_post_path', function( $path, $post ) {
    return '/post/' . $post->ID;
}, 10, 2 );

// Include post type in path
add_filter( 'hwp_frontend_links_post_path', function( $path, $post ) {
    return '/' . $post->post_type . '/' . $post->post_name;
}, 10, 2 );
```

## Installation

### Via wp-env

Add to `.wp-env.json`:

```json
{
  "plugins": [
    "./path/to/hwp-frontend-links"
  ]
}
```

### Manual Installation

1. Copy the plugin directory to `wp-content/plugins/`
2. Activate from WordPress admin

## Behavior

- Does not display links if `HEADLESS_FRONTEND_URL` is not defined
- Only shows admin bar link on singular post/page views
- Row actions appear on all posts and pages list screens

## License

GPL-2.0-or-later
