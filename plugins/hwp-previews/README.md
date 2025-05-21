# HWP Previews

**Headless Previews** solution for WordPress: fully configurable preview URLs via the settings page.

[![Version](https://img.shields.io/badge/version-0.0.1-blue)]() [![License](https://img.shields.io/badge/license-GPLv2%2B-lightgrey)]()

> [!CAUTION]
> This plugin is currently in an alpha state. It's still under active development, so you may encounter bugs or incomplete features. Updates will be rolled out regularly. Use with caution and provide feedback if possible.

---

## Table of Contents

* [Overview](#overview)
* [Features](#features)
* [Configuration](#configuration)
* [Hooks & Extensibility](#hooks--extensibility)
* [Integration](#integration)

## Overview

HWP Previews is a robust and extensible WordPress plugin that centralizes all preview configurations into a user-friendly settings interface.
It empowers site administrators and developers to tailor preview behaviors for each public post type independently, facilitating seamless headless or decoupled workflows.
With HWP Previews, you can define dynamic URL templates, enforce unique slugs for drafts, allow all post statuses be used as parent and extend functionality through flexible hooks and filters, ensuring a consistent and conflict-free preview experience across diverse environments.

---

## Features

* **Enable/Disable Previews**: Turn preview functionality on or off for each public post type (including custom types).
* **Custom URL Templates**: Define preview URLs using placeholder tokens for dynamic content. Default tokens include:

	* `{ID}` – Post ID
	* `{author_ID}` – Post author’s user ID
	* `{status}` – Post status slug
	* `{slug}` – Post slug
	* `{parent_ID}` – Parent post ID (hierarchical types)
	* `{type}` – Post type slug
	* `{uri}` – Page URI/path
	* `{template}` – Template filename

* **Unique Post Slugs**: Force unique slugs for all post statuses in the post status config.
* **Parent Status**: Allow posts of **all** statuses to be used as parent within hierarchical post types.
* **Default Post Statuses Config**: `publish`, `future`, `draft`, `pending`, `private`, `auto-draft` (modifiable via core hook).
* **Parameter Registry**: Register, unregister, or customize URL tokens through the `hwp_previews_core` action.
* **Iframe Template for Previews**: Allows enable previews in the iframe on the WP Admin side. User can override the iframe preview template via `hwp_previews_template_path` filter.

---

## Configuration

### Default Post Types Config: 
All public post types are enabled by default on the settings page. It is filterable via `hwp_previews_filter_post_type_setting` filter hook. 

### Default Post Statuses Config: 
Post statuses are `publish`, `future`, `draft`, `pending`, `private`, `auto-draft` (modifiable via core hook).

### Configure HWP Previews Plugin:
Navigate in WP Admin to **Settings › HWP Previews**. For each public post type, configure:

* **Enable HWP Previews** – Master switch
* **Unique Post Slugs** – Force unique slugs for all post statuses in the post status config.
* **Allow All Statuses as Parent** – (Hierarchical types only)
* **Preview URL Template** – Custom URL with tokens like `{ID}`, `{slug}`
* **Load Previews in Iframe** – Toggle iframe-based preview rendering

_Note: Retrieving of settings is cached for performance._

---

## Hooks & Extensibility

### Filter: Post Types List

Modify which post types appear in the settings UI:

```php
// Removes attachment post type from the settings page configuration.

add_filter( 'hwp_previews_filter_post_type_setting', 'hwp_previews_filter_post_type_setting_callback' );
function hwp_previews_filter_post_type_setting_callback( $post_types ) {
    if ( isset( $post_types['attachment'] ) ) {
        unset( $post_types['attachment'] );
    }
    return $post_types;
}
```

### Action: Core Registry

Register or unregister URL parameters, and adjust types/statuses:

```php
add_action( 'hwp_previews_core', 'modify_preview_url_parameters' );
function modify_preview_url_parameters( 
    \HWP\Previews\Preview\Parameter\Preview_Parameter_Registry $registry
) {
    // Remove default parameter
    $registry->unregister( 'author_ID' );

    // Add custom parameter
    $registry->register( new \HWP\Previews\Preview\Parameter\Preview_Parameter(
        'current_time',
        static fn( \WP_Post $post ) => (string) time(),
        __( 'Current Unix timestamp', 'your-domain' )
    ) );
}
```

Modify post types and statuses:

```php
add_action( 'hwp_previews_core', 'modify_post_types_and_statuses_configs', 10, 3 );
function modify_post_types_and_statuses_configs(
    \HWP\Previews\Preview\Parameter\Preview_Parameter_Registry $registry,
    \HWP\Previews\Post\Type\Post_Types_Config $types,
    \HWP\Previews\Post\Status\Post_Statuses_Config $statuses
) {
    // Limit to pages only
    $types->set_post_types( [ 'page' ] );
    // Only include drafts
    $statuses->set_post_statuses( [ 'draft' ] );
}
```

### Filter: Iframe Template Path

Use your own template for iframe previews:

```php
add_filter( 'hwp_previews_template_path', function( $default_path ) {
    return get_stylesheet_directory() . '/my-preview-template.php';
});
```

---

## Integration

HWP Previews is framework and API agnostic, meaning you can integrate it with any front-end application and with any data-fetching method (WPGraphQL, REST).

To get started quickly you can use our [example based on Next.js and WPGraphQL](https://github.com/wpengine/hwptoolkit/examples/next/hwp-preview-wpgraphql). This example uses the Draft Mode feature of Next.js.

To implement your own approach from scratch you can refer to the appropriate documentation pages for each framework. HWP Previews relies on custom preview URLs, allowing you to integrate any method. Below you can find the guides to implement framework-specific preview mode.

- [Next.js Draft Mode with Pages Router](https://nextjs.org/docs/pages/guides/draft-mode)
- [Next.js Draft Mode with App router](https://nextjs.org/docs/app/guides/draft-mode)
- [Nuxt usePreviewMode](https://nuxt.com/docs/api/composables/use-preview-mode)


---
