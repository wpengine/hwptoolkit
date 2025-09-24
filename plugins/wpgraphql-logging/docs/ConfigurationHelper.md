# Configuration Helper Usage Examples

The `ConfigurationHelper` class provides a centralized and cached way to access WPGraphQL Logging configuration. This class implements a singleton pattern to ensure configuration is only loaded once per request and provides convenient methods for accessing different configuration sections.

## Basic Usage

### Getting the Configuration Helper Instance

```php
use WPGraphQL\Logging\Admin\Settings\ConfigurationHelper;

$config_helper = ConfigurationHelper::get_instance();
```

### Getting Full Configuration

```php
$config_helper = ConfigurationHelper::get_instance();
$full_config = $config_helper->get_config();
```

### Getting Configuration Sections

```php
$config_helper = ConfigurationHelper::get_instance();

// Get basic configuration
$basic_config = $config_helper->get_basic_config();

// Get data management configuration
$data_management_config = $config_helper->get_data_management_config();

// Get any custom section
$custom_section = $config_helper->get_section_config('custom_section', []);
```

### Getting Specific Settings

```php
$config_helper = ConfigurationHelper::get_instance();

// Get a specific setting from a section
$log_level = $config_helper->get_setting('basic_configuration', 'log_level', 'info');

// Check if a feature is enabled
$is_sanitization_enabled = $config_helper->is_enabled('data_management', 'data_sanitization_enabled');
```

## Migration from Direct get_option() Usage

### Before (old approach):
```php
$full_config = get_option( WPGRAPHQL_LOGGING_SETTINGS_KEY, [] );
$basic_config = $full_config['basic_configuration'] ?? [];
$log_level = $basic_config['log_level'] ?? 'info';
```

### After (using ConfigurationHelper):
```php
$config_helper = ConfigurationHelper::get_instance();
$log_level = $config_helper->get_setting('basic_configuration', 'log_level', 'info');
```

## Cache Management

### Clearing Cache
```php
$config_helper = ConfigurationHelper::get_instance();
$config_helper->clear_cache(); // Clears cache, next access will reload from DB
```

### Force Reload
```php
$config_helper = ConfigurationHelper::get_instance();
$config_helper->reload_config(); // Clears cache and immediately reloads
```

## Benefits

1. **Performance**: Configuration is cached in memory and only loaded once per request
2. **Consistency**: Centralized access point prevents inconsistent configuration retrieval
3. **Convenience**: Convenient methods for common access patterns
4. **Cache Management**: Automatic cache invalidation when settings are updated
5. **Type Safety**: Better type hints and documentation

## Automatic Cache Invalidation

The ConfigurationHelper automatically clears its cache when WordPress settings are updated. This is initialized in the main Plugin class:

```php
// This is already set up in src/Plugin.php
ConfigurationHelper::init_cache_hooks();
```

The cache hooks listen for:
- `update_option_{$option_key}`
- `add_option_{$option_key}`  
- `delete_option_{$option_key}`

## Performance Notes

- Configuration is cached using WordPress's `wp_cache_*` functions
- Multiple cache groups are used for optimal performance
- Cache duration is set to 1 hour by default
- In-memory caching ensures subsequent accesses within the same request are instant
