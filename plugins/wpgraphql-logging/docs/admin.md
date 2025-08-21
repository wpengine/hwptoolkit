### Admin and Settings

This document explains how the WPGraphQL Logging admin settings UI is built and how to extend it with your own tabs and fields.

---

## Architecture Overview

- **Settings page entry**: `WPGraphQL\Logging\Admin\Settings_Page`
  - Registers the submenu page and orchestrates fields and tabs
  - Hooks added: `init` (init fields), `admin_menu` (page), `admin_init` (fields), `admin_enqueue_scripts` (assets)
- **Menu page**: `WPGraphQL\Logging\Admin\Settings\Menu\Menu_Page`
  - Adds a submenu under Settings → WPGraphQL Logging (`wpgraphql-logging`)
  - Renders template `src/Admin/Settings/Templates/admin.php`
- **Form manager**: `WPGraphQL\Logging\Admin\Settings\Settings_Form_Manager`
  - Registers the settings (`register_setting`) and sections/fields per tab
  - Sanitizes and saves values per tab; unknown fields are pruned
- **Field collection**: `WPGraphQL\Logging\Admin\Settings\Fields\Settings_Field_Collection`
  - Holds all tabs and fields. A default `Basic_Configuration_Tab` is registered
- **Tabs**: Implement `Settings_Tab_Interface` with `get_name()`, `get_label()`, `get_fields()`
- **Fields**: Implement `Settings_Field_Interface` or use built-ins:
  - `Field\Checkbox_Field`
  - `Field\Text_Input_Field`
  - `Field\Select_Field`

Settings are stored in an array option. Keys are filterable:

- Option key: `wpgraphql_logging_settings` (filter `wpgraphql_logging_settings_group_option_key`)
- Settings group: `wpgraphql_logging_settings_group` (filter `wpgraphql_logging_settings_group_settings_group`)

To read values at runtime, use `WPGraphQL\Logging\Admin\Settings\Logging_Settings_Service`:

```php
use WPGraphQL\Logging\Admin\Settings\Logging_Settings_Service;

$settings = new Logging_Settings_Service();
$enabled = $settings->get_setting('basic_configuration', 'enabled', false);
```

---

## Hooks Reference (Admin)

- Action: `wpgraphql_logging_settings_init( Settings_Page $instance )`
  - Fired after the settings page is initialized
- Action: `wpgraphql_logging_settings_field_collection_init( Settings_Field_Collection $collection )`
  - Fired after default tabs/fields are registered; primary extension point to add tabs/fields
- Action: `wpgraphql_logging_settings_form_manager_init( Settings_Form_Manager $manager )`
  - Fired when the form manager is constructed
- Filter: `wpgraphql_logging_settings_group_option_key( string $option_key )`
  - Change the option key used to store settings
- Filter: `wpgraphql_logging_settings_group_settings_group( string $group )`
  - Change the settings group name used in `register_setting`

- Filter: `wpgraphql_logging_basic_configuration_fields( array $fields )`
  - Modify the default fields rendered in the `basic_configuration` tab. You can add, remove, or replace fields by returning a modified associative array of `field_id => Settings_Field_Interface`.
  - Example:
  ```php
  use WPGraphQL\Logging\Admin\Settings\Fields\Field\Checkbox_Field;

  add_filter('wpgraphql_logging_basic_configuration_fields', function(array $fields): array {
      // Add a custom toggle into the Basic Configuration tab
      $fields['enable_feature_x'] = new Checkbox_Field(
          'enable_feature_x',
          'basic_configuration',
          'Enable Feature X',
          '',
          'Turn on extra logging for Feature X.'
      );

      // Optionally remove an existing field
      // unset($fields[ WPGraphQL\Logging\Admin\Settings\Fields\Tab\Basic_Configuration_Tab::DATA_SAMPLING ]);

      return $fields;
  });
  ```

Related (non-admin) hooks for context:

- Action: `wpgraphql_logging_init( Plugin $instance )` (plugin initialized)
- Action: `wpgraphql_logging_activate` / `wpgraphql_logging_deactivate`

---

## Add a New Tab

Create a tab class implementing `Settings_Tab_Interface` and register it during `wpgraphql_logging_settings_field_collection_init`.

```php
<?php
namespace MyPlugin\WPGraphQLLogging;

use WPGraphQL\Logging\Admin\Settings\Fields\Settings_Field_Collection;
use WPGraphQL\Logging\Admin\Settings\Fields\Tab\Settings_Tab_Interface;
use WPGraphQL\Logging\Admin\Settings\Fields\Field\Text_Input_Field;

class My_Custom_Tab implements Settings_Tab_Interface {
    public static function get_name(): string {
        return 'my_custom_tab';
    }

    public static function get_label(): string {
        return 'My Custom Tab';
    }

    public function get_fields(): array {
        return [
            'my_setting' => new Text_Input_Field(
                'my_setting',
                self::get_name(),
                'My Setting',
                '',
                'Describe what this setting does.',
                'e.g., value'
            ),
        ];
    }
}

add_action('wpgraphql_logging_settings_field_collection_init', function (Settings_Field_Collection $collection): void {
    $collection->add_tab(new My_Custom_Tab());
});
```

Notes:

- `get_name()` must be a unique slug; it is used in the admin page URL (`tab` query arg) and section IDs
- Fields returned by `get_fields()` must set their `tab` to this slug so they render on the tab

---

## Add a Field to an Existing Tab

You can add fields directly to the shared field collection. Ensure the field’s `tab` matches the target tab name.

```php
<?php
namespace MyPlugin\WPGraphQLLogging;

use WPGraphQL\Logging\Admin\Settings\Fields\Settings_Field_Collection;
use WPGraphQL\Logging\Admin\Settings\Fields\Field\Checkbox_Field;

add_action('wpgraphql_logging_settings_field_collection_init', function (Settings_Field_Collection $collection): void {
    $collection->add_field(
        'enable_feature_x',
        new Checkbox_Field(
            'enable_feature_x',
            'basic_configuration', // target the built-in Basic Configuration tab
            'Enable Feature X',
            '',
            'Turn on extra logging for Feature X.'
        )
    );
});
```

Tips:

- Only fields present in the collection are saved; unknown keys are pruned during sanitize
- Field input names follow: `{$option_key}[{$tab}][{$field_id}]`

---

## Reading/Saving Behavior

- Each submit saves only the current tab’s fields
- Sanitization is delegated to each field via `sanitize_field($value)`
- Unknown fields or tabs are ignored/pruned

Example of reading a value elsewhere:

```php
use WPGraphQL\Logging\Admin\Settings\Logging_Settings_Service;

$settings = new Logging_Settings_Service();
$thresholdSeconds = (float) $settings->get_setting('basic_configuration', 'performance_metrics', '0');
```

---

## Common Use Cases

- Add organization-specific logging toggles (privacy, PII redaction)
- Integrate with other plugins by exposing their settings under a new tab
- Provide presets for log points (e.g., only log slow queries) via a custom select field

---

## Admin Page Details

- Menu: Settings → WPGraphQL Logging (`admin.php?page=wpgraphql-logging`)
- Tabs: `admin.php?page=wpgraphql-logging&tab={tab_slug}`
- Sections and fields are rendered with `do_settings_sections('wpgraphql-logging-{tab_slug}')`
