# Actions & Filters

## Table of Contents

- [PHP Actions](#php-actions)
- [PHP Filters](#php-filters)
- [Examples](#examples)
  - [Actions](#actions)
  - [Filters](#filters)
- [Contributing](#contributing)

---

This document lists the available PHP actions and filters provided by the HWP Previews plugin, along with explanations and usage examples. These hooks allow you to customize plugin behavior, settings, and integration with other plugins or your theme.

---

## PHP Actions

| Action Name                                   | Description                                                                                   |
|-----------------------------------------------|-----------------------------------------------------------------------------------------------|
| `hwp_previews_init`                           | Fired after the plugin is initialized.                                                        |
| `hwp_previews_activate`                       | Fired on plugin activation.                                                                   |
| `hwp_previews_deactivate`                     | Fired on plugin deactivation.                                                                 |
| `hwp_previews_settings_init`                  | Fired after the settings page is initialized.                                                 |
| `hwp_previews_settings_form_manager_init`     | Fired after the settings form manager is initialized.                                         |

---

## PHP Filters

| Filter Name                                         | Description                                                                                                 |
|-----------------------------------------------------|-------------------------------------------------------------------------------------------------------------|
| `hwp_previews_settings_fields`                      | Modify or add settings fields for the settings page.                                                        |
| `hwp_previews_settings_group_option_key`            | Change the settings group option key (default: `HWP_PREVIEWS_SETTINGS_KEY`).                                |
| `hwp_previews_settings_group_settings_group`        | Change the settings group name (default: `HWP_PREVIEWS_SETTINGS_GROUP`).                                    |
| `hwp_previews_register_parameters`                  | Allows users to modify or register parameters.                                |
| `hwp_previews_template_path`                        | Change the template file path for iframe previews.                                                          |
| `hwp_previews_filter_available_post_types`          | Filter the available post types for previews (affects settings UI and preview logic).                       |
| `hwp_previews_filter_available_post_statuses`       | Filter the available post statuses for previews.                                                            |

---

## Examples

Below is some examples of usage for these actions/filters.


#### `hwp_previews_filter_available_post_types`
**Description:**
Filter the available post types for previews (affects settings UI and preview logic).

```php
// Removes attachment post type from the settings page configuration.

add_filter( 'hwp_previews_filter_available_post_types', 'hwp_previews_filter_post_type_setting_callback' );
function hwp_previews_filter_post_type_setting_callback( $post_types ) {
    if ( isset( $post_types['attachment'] ) ) {
        unset( $post_types['attachment'] );
    }
    return $post_types;
}
```


#### `hwp_previews_template_path`
**Description:**
Change the template file path for iframe previews.

```php
add_filter( 'hwp_previews_template_path', function( $default_path ) {
    return get_stylesheet_directory() . '/my-preview-template.php';
});
```

#### `hwp_previews_settings_group_option_key` and `hwp_previews_settings_group_settings_group`
**Description:**
Change the settings group option key or group name.

```php
add_filter( 'hwp_previews_settings_group_option_key', function( $default_key ) {
    return 'my_custom_option_key';
});
add_filter( 'hwp_previews_settings_group_settings_group', function( $default_group ) {
    return 'my_custom_settings_group';
});
```

---

## Contributing

If you feel like something is missing or you want to add tests or testing documentation, we encourage you to contribute! Please check out our [Contributing Guide](https://github.com/wpengine/hwptoolkit/blob/main/CONTRIBUTING.md) for more details.
