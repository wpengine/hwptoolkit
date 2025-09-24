<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Admin\Settings;

use WPGraphQL\Logging\Admin\Settings\Fields\Tab\BasicConfigurationTab;
use WPGraphQL\Logging\Admin\Settings\Fields\Tab\DataManagementTab;

/**
 * Configuration Helper class
 *
 * This class provides a centralized and cached way to access WPGraphQL Logging configuration.
 * It implements a singleton pattern to ensure configuration is only loaded once per request
 * and provides convenient methods for accessing different configuration sections.
 *
 * @package WPGraphQL\Logging
 *
 * @since 0.0.1
 */
class ConfigurationHelper {
	/**
	 * Cache group for configuration.
	 *
	 * @var string
	 */
	public const CACHE_GROUP = 'wpgraphql_logging_config';

	/**
	 * Cache duration in seconds (1 hour).
	 *
	 * @var int
	 */
	public const CACHE_DURATION = 3600;

	/**
	 * The cached configuration values.
	 *
	 * @var array<string, mixed>|null
	 */
	protected ?array $config = null;

	/**
	 * The single instance of this class.
	 *
	 * @var \WPGraphQL\Logging\Admin\Settings\ConfigurationHelper|null
	 */
	protected static ?ConfigurationHelper $instance = null;

	/**
	 * Get the singleton instance.
	 */
	public static function get_instance(): ConfigurationHelper {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Get the full configuration array.
	 *
	 * @return array<string, mixed>
	 */
	public function get_config(): array {
		if ( null === $this->config ) {
			$this->load_config();
		}

		return $this->config ?? [];
	}

	/**
	 * Get configuration for a specific section (tab).
	 *
	 * @param string               $section The configuration section key.
	 * @param array<string, mixed> $default_value Default value if section not found.
	 *
	 * @return array<string, mixed>
	 */
	public function get_section_config( string $section, array $default_value = [] ): array {
		$config = $this->get_config();
		return $config[ $section ] ?? $default_value;
	}

	/**
	 * Get a specific setting value from a configuration section.
	 *
	 * @param string $section     The configuration section key.
	 * @param string $setting_key The setting key within the section.
	 * @param mixed  $default_value     Default value if setting not found.
	 */
	public function get_setting( string $section, string $setting_key, $default_value = null ): mixed {
		$section_config = $this->get_section_config( $section );
		return $section_config[ $setting_key ] ?? $default_value;
	}

	/**
	 * Get the basic configuration section.
	 *
	 * @return array<string, mixed>
	 */
	public function get_basic_config(): array {
		return $this->get_section_config( BasicConfigurationTab::get_name() );
	}

	/**
	 * Get the data management configuration section.
	 *
	 * @return array<string, mixed>
	 */
	public function get_data_management_config(): array {
		return $this->get_section_config( DataManagementTab::get_name() );
	}

	/**
	 * Check if a specific feature is enabled.
	 *
	 * @param string $section     The configuration section.
	 * @param string $setting_key The setting key for the feature.
	 */
	public function is_enabled( string $section, string $setting_key ): bool {
		return (bool) $this->get_setting( $section, $setting_key, false );
	}

	/**
	 * Clear the configuration cache.
	 * This forces a reload of the configuration on the next access.
	 */
	public function clear_cache(): void {
		$this->config = null;
		$option_key   = $this->get_option_key();
		wp_cache_delete( $option_key, self::CACHE_GROUP );
		wp_cache_delete( $option_key, $this->get_settings_group() );
	}

	/**
	 * Reload the configuration from the database.
	 * This bypasses any cache and forces a fresh load.
	 */
	public function reload_config(): void {
		$this->clear_cache();
		$this->load_config();
	}

	/**
	 * Get the option key for the settings.
	 */
	public function get_option_key(): string {
		return (string) apply_filters( 'wpgraphql_logging_settings_group_option_key', WPGRAPHQL_LOGGING_SETTINGS_KEY );
	}

	/**
	 * Get the settings group for caching.
	 */
	public function get_settings_group(): string {
		return (string) apply_filters( 'wpgraphql_logging_settings_group_settings_group', WPGRAPHQL_LOGGING_SETTINGS_GROUP );
	}

	/**
	 * Hook into WordPress to clear cache when settings are updated.
	 * This should be called during plugin initialization.
	 *
	 * @psalm-suppress PossiblyInvalidArgument
	 */
	public static function init_cache_hooks(): void {
		$instance   = self::get_instance();
		$option_key = $instance->get_option_key();

		// Clear cache when the option is updated.
		add_action( "update_option_{$option_key}", [ $instance, 'clear_cache' ] );
		add_action( "add_option_{$option_key}", [ $instance, 'clear_cache' ] );
		add_action( "delete_option_{$option_key}", [ $instance, 'clear_cache' ] );
	}

	/**
	 * Load the configuration from cache or database.
	 *
	 * @phpcs:disable WordPressVIPMinimum.Performance.LowExpiryCacheTime.CacheTimeUndetermined
	 */
	protected function load_config(): void {
		$option_key = $this->get_option_key();

		$cache_duration = self::CACHE_DURATION;

		// Try to get from wp_cache first (in-memory cache).
		$cached_config = wp_cache_get( $option_key, self::CACHE_GROUP );
		if ( is_array( $cached_config ) ) {
			$this->config = $cached_config;
			return;
		}

		// Try to get from the WordPress object cache (could be Redis, Memcached, etc.).
		$cached_config = wp_cache_get( $option_key, $this->get_settings_group() );
		if ( is_array( $cached_config ) ) {
			$this->config = $cached_config;
			// Store in our custom cache group for faster access next time.
			wp_cache_set( $option_key, $cached_config, self::CACHE_GROUP, $cache_duration );
			return;
		}

		// Load from database.
		$this->config = (array) get_option( $option_key, [] );

		// Cache the result in both cache groups.
		wp_cache_set( $option_key, $this->config, self::CACHE_GROUP, $cache_duration );
		wp_cache_set( $option_key, $this->config, $this->get_settings_group(), $cache_duration );
	}
}
