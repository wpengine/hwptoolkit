***

title: How To Guide: Add new settings field
description: Learn how to add custom settings fields to the WPGraphQL Logging plugin admin interface and retrieve their values programmatically.
------------------------------------------------------------------------------------------------------------------------------------------------

## Overview

This guide shows how to add custom fields to the WPGraphQL Logging settings and how to read or query those values.

![WPGraphQL Logging Settings Page](../screenshots/admin_how_to_add_field.png)
*The WPGraphQL Logging settings page with Basic Configuration and Data Management tabs where custom fields can be added*

### Step 1 — Add a field via filter

Add a field to an existing tab using the provided filters. Common tabs are `basic_configuration` and `data_management`.

Example: add a checkbox to Basic Configuration

```php
add_filter( 'wpgraphql_logging_basic_configuration_fields', function( $fields ) {
    $fields['my_feature_enabled'] = new \WPGraphQL\Logging\Admin\Settings\Fields\Field\CheckboxField(
        'my_feature_enabled',
        'basic_configuration',
        __( 'Enable My Feature', 'my-plugin' )
    );
    return $fields;
});
```

Example: add a text input to Data Management

```php
add_filter( 'wpgraphql_logging_data_management_fields', function( $fields ) {
    $fields['my_data_region'] = new \WPGraphQL\Logging\Admin\Settings\Fields\Field\TextInputField(
        'my_data_region',
        'data_management',
        __( 'Data Region', 'my-plugin' ),
        '',
        __( 'e.g., us-east-1', 'my-plugin' ),
        __( 'us-east-1', 'my-plugin' )
    );
    return $fields;
});
```

Notes:

* Field classes available: `CheckboxField`, `TextInputField`, `SelectField`, `TextIntegerField`.
* The second argument is the tab key (use the tab’s `get_name()`), not the option key.

### Step 2 — Where the value is stored

Values are saved to the option key `wpgraphql_logging_settings` under the tab key and field id, for example:

```php
$options = get_option( 'wpgraphql_logging_settings', [] );
// Example structure
// [
//   'basic_configuration' => [ 'my_feature_enabled' => true ],
//   'data_management'     => [ 'my_data_region' => 'us-east-1' ],
// ]
```

### Step 3 — Read the value in PHP

```php
$options          = get_option( 'wpgraphql_logging_settings', [] );
$is_enabled       = ! empty( $options['basic_configuration']['my_feature_enabled'] );
$my_data_region   = $options['data_management']['my_data_region'] ?? '';
```
