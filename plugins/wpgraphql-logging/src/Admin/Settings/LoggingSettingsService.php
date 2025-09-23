<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Admin\Settings;

/**
 * Logging Settings Service class
 *
 * This class provides methods to retrieve and manage logging settings for the WPGraphQL Logging plugin.
 *
 * @package WPGraphQL\Logging
 *
 * @since 0.0.1
 */
class LoggingSettingsService {
	/**
	 * The settings value
	 *
	 * @var array<mixed>
	 */
	protected array $settings_values = [];

	/**
	 * Initialize the settings service.
	 */
	public function __construct() {
		$this->setup();
	}

	/**
	 * Get the settings values.
	 *
	 * @return array<mixed>
	 */
	public function get_settings_values(): array {
		return $this->settings_values;
	}

	/**
	 * Get the configuration for a specific tab.
	 *
	 * @param string $tab_key The tab key.
	 *
	 * @return array<mixed>|null
	 */
	public function get_tab_config( string $tab_key ): ?array {
		return $this->settings_values[ $tab_key ] ?? null;
	}

	/**
	 * Get a specific setting value from a tab.
	 *
	 * @param string $tab_key       The tab key.
	 * @param string $setting_key   The setting key.
	 * @param mixed  $default_value The default value if not found.
	 */
	public function get_setting( string $tab_key, string $setting_key, $default_value = null ): mixed {
		$tab_config = $this->get_tab_config( $tab_key );
		return $tab_config[ $setting_key ] ?? $default_value;
	}

	/**
	 * The option key for the settings group.
	 */
	public static function get_option_key(): string {
		return (string) apply_filters( 'wpgraphql_logging_settings_group_option_key', WPGRAPHQL_LOGGING_SETTINGS_KEY );
	}

	/**
	 * The settings group for the options.
	 */
	public static function get_settings_group(): string {
		return (string) apply_filters( 'wpgraphql_logging_settings_group_settings_group', WPGRAPHQL_LOGGING_SETTINGS_GROUP );
	}

	/**
	 * Set up the settings values by retrieving them from the database or cache.
	 * This method is called in the constructor to ensure settings are available.
	 */
	protected function setup(): void {
		$option_key     = self::get_option_key();
		$settings_group = self::get_settings_group();

		$value = wp_cache_get( $option_key, $settings_group );
		if ( is_array( $value ) ) {
			$this->settings_values = $value;

			return;
		}

		$this->settings_values = (array) get_option( $option_key, [] );
		wp_cache_set( $option_key, $this->settings_values, $settings_group );
	}
}
