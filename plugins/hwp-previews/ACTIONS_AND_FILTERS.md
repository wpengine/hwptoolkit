# Actions & Filters

## PHP Actions

- `hwp_previews_activate` - Plugin activation hook
- `hwp_previews_deactivate` - Plugin deactivation hook
- `hwp_previews_init` - Plugin initialization hook

## PHP Filters

- `hwp_previews_register_parameters` - Allows modification of the URL parameters used for previews for the class `Preview_Parameter_Registry`
- `hwp_previews_template_path` - To use our own template for iframe previews
- `hwp_previews_core` - Register or unregister URL parameters, and adjust types/statuses
- `hwp_previews_filter_available_post_types` - Filter to modify the available post types for Previews.
- `hwp_previews_filter_available_post_statuses` - Filter for post statuses for previews for Previews
- `hwp_previews_filter_available_parent_post_statuses` - Filter for parent post statuses for Previews
- `hwp_previews_settings_group_option_key` - Filter to modify the settings group option key. Default is HWP_PREVIEWS_SETTINGS_KEY
- `hwp_previews_settings_group_settings_group` - Filter to modify the settings group name. Default is HWP_PREVIEWS_SETTINGS_GROUP
- `hwp_previews_settings_group_settings_config` - Filter to modify the settings array. See `Settings_Group`
- `hwp_previews_settings_group_cache_groups` - Filter to modify cache groups for `Settings_Group`
- `hwp_previews_get_post_types_config` - Filter for generating the instance of `Post_Types_Config_Interface`
- `hwp_previews_hooks_post_type_config` - Filter for post type config service for the Hook class
- `hwp_previews_hooks_post_status_config` - Filter for post status config service for the Hook class
- `hwp_previews_hooks_preview_link_service` - Filter for preview link service for the Hook class
- `hwp_previews_settings_fields` - Allows a user to register, modify, or remove settings fields for the settings page

## Usage Examples

@TODO - Redo


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
