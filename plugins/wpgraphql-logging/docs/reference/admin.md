## Admin Reference

The WPGraphQL Logging plugin provides several filters and actions in the Admin area that allow developers to extend and customize functionality. This reference documents all available hooks with real-world examples.

## Table of Contents

- [SettingsPage](#class-settingspage)
- [Settings\ConfigurationHelper](#class-settingsconfigurationhelper)
- [Settings\SettingsFormManager](#class-settingssettingsformmanager)
- [ViewLogsPage](#class-viewlogspage)
- [Settings\Templates\admin.php and View Templates](#class-settingstemplatesadminphp-and-view-templates)


---


### Class: `SettingsPage`
Source: <https://github.com/wpengine/hwptoolkit/blob/main/plugins/wpgraphql-logging/src/Admin/SettingsPage.php>

#### Action: `wpgraphql_logging_settings_init`
Fires once the Settings Page singleton is initialized.

Parameters:
- `$instance` (SettingsPage) Settings page instance

Example:
```php
add_action( 'wpgraphql_logging_settings_init', function( $settings_page ) {
    add_action( 'admin_notices', function() {
		echo '<div class="notice notice-warning"><p>Custom notice.</p>
    });
}, 10, 1 );
```

#### Action: `wpgraphql_logging_admin_enqueue_scripts`
Fires when scripts/styles are enqueued for the Settings page.

Parameters:
- `$hook_suffix` (string) Current admin page hook

Example:
```php
add_action( 'wpgraphql_logging_admin_enqueue_scripts', function( $hook_suffix ) {
	wp_enqueue_style( 'my-logging-admin', plugins_url( 'assets/css/admin.css', __FILE__ ), [], '1.0.0' );
}, 10, 1 );
```

#### Filter: `wpgraphql_logging_admin_template_path`
Filters the admin template path used to render the Settings page.

Parameters:
- `$template_path` (string) Default template path

Returns: string

Example:
```php
add_filter( 'wpgraphql_logging_admin_template_path', function( $template_path ) {
	return plugin_dir_path( __FILE__ ) . 'templates/custom-admin.php';
});
```

---

### Class: `Settings\Fields\SettingsFieldCollection`
Source: <https://github.com/wpengine/hwptoolkit/blob/main/plugins/wpgraphql-logging/src/Admin/Settings/Fields/SettingsFieldCollection.php>

#### Action: `wpgraphql_logging_settings_field_collection_init`
Allows developers to register additional settings tabs/fields.

Parameters:
- `$collection` (SettingsFieldCollection) The collection instance

Example:
```php
add_action( 'wpgraphql_logging_settings_field_collection_init', function( $collection ) {
	$collection->add_tab( new \MyPlugin\Admin\Settings\Fields\Tab\MyCustomTab() );
}, 10, 1 );
```

>[NOTE]
> See our how to guide [How to add a new Settings tab to WPGraphQL Logging](../how-to/admin_add_new_tab.md)


---

### Class: `Settings\Tab\BasicConfigurationTab`
Source: <https://github.com/wpengine/hwptoolkit/blob/main/plugins/wpgraphql-logging/src/Admin/Settings/Fields/Tab/BasicConfigurationTab.php>

#### Filter: `wpgraphql_logging_basic_configuration_fields`
Filters the field definitions for the Basic Configuration tab.

Parameters:
- `$fields` (array) Map of field id => field object

Returns: array

Example:
```php
add_filter( 'wpgraphql_logging_basic_configuration_fields', function( $fields ) {
	$fields['my_setting'] = new \WPGraphQL\Logging\Admin\Settings\Fields\Field\CheckboxField(
		'my_setting',
		'basic_configuration',
		__( 'My Setting', 'my-plugin' )
	);
	return $fields;
});
```

---

### Class: `Settings\Tab\DataManagementTab`
Source: <https://github.com/wpengine/hwptoolkit/blob/main/plugins/wpgraphql-logging/src/Admin/Settings/Fields/Tab/DataManagementTab.php>

#### Filter: `wpgraphql_logging_data_management_fields`
Filters the field definitions for the Data Management tab.

Parameters:
- `$fields` (array) Map of field id => field object

Returns: array

Example:
```php
add_filter( 'wpgraphql_logging_data_management_fields', function( $fields ) {
	$fields['my_purge_days'] = new \WPGraphQL\Logging\Admin\Settings\Fields\Field\TextIntegerField(
		'my_purge_days',
		'data_management',
		__( 'Purge After (days)', 'my-plugin' )
	);
	return $fields;
});
```

---

### Class: `Settings\ConfigurationHelper`
Source: <https://github.com/wpengine/hwptoolkit/blob/main/plugins/wpgraphql-logging/src/Admin/Settings/ConfigurationHelper.php>

#### Filter: `wpgraphql_logging_settings_group_option_key`
Filters the option key used to store settings.

Parameters:
- `$option_key` (string)

Returns: string

Example:
```php
add_filter( 'wpgraphql_logging_settings_group_option_key', function( $option_key ) {
	return $option_key . '_' . wp_get_environment_type();
});
```

#### Filter: `wpgraphql_logging_settings_group_settings_group`
Filters the settings group name.

Parameters:
- `$settings_group` (string)

Returns: string

Example:
```php
add_filter( 'wpgraphql_logging_settings_group_settings_group', function( $group ) {
	return is_multisite() ? 'network_' . $group : $group;
});
```

---

### Class: `Settings\SettingsFormManager`
Source: <https://github.com/wpengine/hwptoolkit/blob/main/plugins/wpgraphql-logging/src/Admin/Settings/SettingsFormManager.php>

#### Action: `wpgraphql_logging_settings_form_manager_init`
Fires when the settings form manager is initialized.

Parameters:
- `$instance` (SettingsFormManager)

Example:
```php
add_action( 'wpgraphql_logging_settings_form_manager_init', function( $manager ) {
	// Place for validation/transform hooks tied to registration lifecycle
}, 10, 1 );
```

---

### Class: `ViewLogsPage`
Source: <https://github.com/wpengine/hwptoolkit/blob/main/plugins/wpgraphql-logging/src/Admin/ViewLogsPage.php>

#### Action: `wpgraphql_logging_view_logs_init`
Fires once the View Logs page singleton is initialized.

Parameters:
- `$instance` (ViewLogsPage)

Example:
```php
add_action( 'wpgraphql_logging_view_logs_init', function( $view_logs_page ) {
	// e.g. register custom columns or UI
}, 10, 1 );
```

#### Action: `wpgraphql_logging_view_logs_admin_enqueue_scripts`
Fires when scripts/styles are enqueued for the View Logs page.

Parameters:
- `$hook_suffix` (string)

Example:
```php
add_action( 'wpgraphql_logging_view_logs_admin_enqueue_scripts', function( $hook_suffix ) {
	wp_enqueue_script( 'my-view-logs', plugins_url( 'assets/js/view-logs.js', __FILE__ ), [ 'jquery' ], '1.0.0', true );
}, 10, 1 );
```

#### Filter: `wpgraphql_logging_filter_redirect_url`
Filters the redirect URL after submitting filters.

Parameters:
- `$redirect_url` (string)
- `$filters` (array)

Returns: string

Example:
```php
add_filter( 'wpgraphql_logging_filter_redirect_url', function( $redirect_url, $filters ) {
	return add_query_arg( 'my_flag', '1', $redirect_url );
}, 10, 2 );
```

#### Filter: `wpgraphql_logging_list_template`
Filters the template path for the logs list.

Parameters:
- `$template_path` (string)

Returns: string

Example:
```php
add_filter( 'wpgraphql_logging_list_template', function( $template_path ) {
	return plugin_dir_path( __FILE__ ) . 'templates/custom-list.php';
});
```

#### Filter: `wpgraphql_logging_view_template`
Filters the template path for the single log view.

Parameters:
- `$template_path` (string)

Returns: string

Example:
```php
add_filter( 'wpgraphql_logging_view_template', function( $template_path ) {
	return plugin_dir_path( __FILE__ ) . 'templates/custom-view.php';
});
```

---

### Class: `View\List\ListTable`
Source: <https://github.com/wpengine/hwptoolkit/blob/main/plugins/wpgraphql-logging/src/Admin/View/List/ListTable.php>

#### Filter: `wpgraphql_logging_logs_table_column_headers`
Filters the table columns and sorting metadata.

Parameters:
- `$column_headers` (array) [ columns, hidden, sortable, primary ]

Returns: array

Example:
```php
add_filter( 'wpgraphql_logging_logs_table_column_headers', function( $headers ) {
	$headers[0]['app_name'] = __( 'App', 'my-plugin' );
	return $headers;
});
```

#### Filter: `wpgraphql_logging_logs_table_query_args`
Filters the repository query args used to fetch logs.

Parameters:
- `$args` (array)

Returns: array

Example:
```php
add_filter( 'wpgraphql_logging_logs_table_query_args', function( $args ) {
	$args['where'][] = "JSON_EXTRACT(context, '$.app_id') IS NOT NULL";
	return $args;
});
```

#### Filter: `wpgraphql_logging_logs_table_column_value`
Filters the rendered value for each column.

Parameters:
- `$value` (mixed)
- `$item` (\WPGraphQL\Logging\Logger\Database\DatabaseEntity)
- `$column_name` (string)

Returns: mixed

Example:
```php
add_filter( 'wpgraphql_logging_logs_table_column_value', function( $value, $item, $column ) {
	if ( 'message' === $column ) {
		return wp_trim_words( (string) $value, 20 );
	}
	return $value;
}, 10, 3 );
```

#### Filter: `wpgraphql_logging_logs_table_where_clauses`
Filters the computed WHERE clauses before querying.

Parameters:
- `$where_clauses` (array)
- `$request` (array)

Returns: array

Example:
```php
add_filter( 'wpgraphql_logging_logs_table_where_clauses', function( $where, $request ) {
	if ( ! empty( $request['status_code'] ) ) {
		$code    = absint( $request['status_code'] );
		$where[] = "JSON_EXTRACT(context, '$.status_code') = {$code}";
	}
	return $where;
}, 10, 2 );
```

#### Filter: `wpgraphql_logging_filters_template`
Filters the template path for the filters UI.

Parameters:
- `$template_path` (string)

Returns: string

Example:
```php
add_filter( 'wpgraphql_logging_filters_template', function( $template_path ) {
	return plugin_dir_path( __FILE__ ) . 'templates/custom-filters.php';
});
```

---

### Class: `View\Download\DownloadLogService`
Source: <https://github.com/wpengine/hwptoolkit/blob/main/plugins/wpgraphql-logging/src/Admin/View/Download/DownloadLogService.php>

#### Filter: `wpgraphql_logging_csv_filename`
Filters the CSV filename used for a single log export.

Parameters:
- `$filename` (string)

Returns: string

Example:
```php
add_filter( 'wpgraphql_logging_csv_filename', function( $filename ) {
	return 'myapp_' . gmdate( 'Ymd_His' ) . '.csv';
});
```

#### Filter: `wpgraphql_logging_csv_headers`
Filters the CSV column headers.

Parameters:
- `$headers` (array)
- `$log_id` (int)
- `$log` (\WPGraphQL\Logging\Logger\Database\DatabaseEntity)

Returns: array

Example:
```php
add_filter( 'wpgraphql_logging_csv_headers', function( $headers ) {
	return array_merge( $headers, [ 'Environment', 'Endpoint' ] );
}, 10, 3 );
```

#### Filter: `wpgraphql_logging_csv_content`
Filters the CSV row values.

Parameters:
- `$content` (array)
- `$log_id` (int)
- `$log` (\WPGraphQL\Logging\Logger\Database\DatabaseEntity)

Returns: array

Example:
```php
add_filter( 'wpgraphql_logging_csv_content', function( $content, $log_id, $log ) {
	$context = $log->get_context();
	return array_merge( $content, [
		$context['environment'] ?? 'prod',
		$context['headless_endpoint'] ?? '',
	] );
}, 10, 3 );
```

---

### Class: `Settings\Templates\admin.php` and View Templates
Source: `src/Admin/Settings/Templates/admin.php`, `src/Admin/View/Templates/WPGraphQLLogger*.php`

These templates are referenced by the template path filters above and do not define hooks themselves.

**Template Files:**
- `WPGraphQLLoggerFilters.php` - Filter controls template
- `WPGraphQLLoggerList.php` - Logs list table template  
- `WPGraphQLLoggerView.php` - Single log detail view template
