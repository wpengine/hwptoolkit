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
	 * The configuration helper instance.
	 *
	 * @var \WPGraphQL\Logging\Admin\Settings\ConfigurationHelper
	 */
	protected ConfigurationHelper $config_helper;

	/**
	 * Initialize the settings service.
	 */
	public function __construct() {
		$this->config_helper = ConfigurationHelper::get_instance();
	}

	/**
	 * Get the settings values.
	 *
	 * @return array<mixed>
	 */
	public function get_settings_values(): array {
		return $this->config_helper->get_config();
	}

	/**
	 * Get the configuration for a specific tab.
	 *
	 * @param string $tab_key The tab key.
	 *
	 * @return array<mixed>|null
	 */
	public function get_tab_config( string $tab_key ): ?array {
		$config = $this->config_helper->get_section_config( $tab_key );
		return empty( $config ) ? null : $config;
	}

	/**
	 * Get a specific setting value from a tab.
	 *
	 * @param string $tab_key       The tab key.
	 * @param string $setting_key   The setting key.
	 * @param mixed  $default_value The default value if not found.
	 */
	public function get_setting( string $tab_key, string $setting_key, $default_value = null ): mixed {
		return $this->config_helper->get_setting( $tab_key, $setting_key, $default_value );
	}

	/**
	 * The option key for the settings group.
	 */
	public static function get_option_key(): string {
		return ConfigurationHelper::get_instance()->get_option_key();
	}

	/**
	 * The settings group for the options.
	 */
	public static function get_settings_group(): string {
		return ConfigurationHelper::get_instance()->get_settings_group();
	}
}
