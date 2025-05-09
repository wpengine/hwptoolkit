<?php

declare(strict_types=1);

namespace HWP\Previews\Settings;

class Settings_Cache_Group {

	/**
	 * Settings option name.
	 *
	 * @var string
	 */
	protected string $option;

	/**
	 * Settings group name.
	 *
	 * @var string
	 */
	private string $group;

	/**
	 * Array of settings config where keys are options and values are types.
	 *
	 * @var array<string, string>
	 */
	private array $settings_config;

	/**
	 * Constructor.
	 * Adds a settings group to the list of non-persistent groups.
	 *
	 * @param string                $option Option name.
	 * @param string                $group Group name.
	 * @param array<string, string> $settings_config Array of settings config where keys are allowed options and values are types.
	 */
	public function __construct( string $option, string $group, array $settings_config ) {
		$this->option          = $option;
		$this->group           = $group;
		$this->settings_config = $settings_config;

		wp_cache_add_non_persistent_groups( [ $this->group ] );
	}

	/**
	 * Gets settings from the cache or database.
	 *
	 * @return array<string, mixed>
	 */
	public function get_cache_settings(): array {

		$value = wp_cache_get( $this->option, $this->group );

		if ( $value === false ) {
			$value = (array) get_option( $this->option, [] );
			wp_cache_set( $this->option, $value, $this->group );
		}

		return $value;
	}

	/**
	 * Gets a setting value from the cache or database.
	 *
	 * @param string $name The name of a bool setting.
	 * @param string $post_type The post type slug.
	 * @param bool   $default_value The default value to return if the setting is not found.
	 *
	 * @return bool
	 */
	public function get_bool( string $name, string $post_type, bool $default_value = false ): bool {
		$value = $this->get_cache_settings();

		if ( ! $this->is_setting_of_type( $name, 'bool' ) || empty( $value[ $post_type ][ $name ] ) ) {
			return $default_value;
		}

		return (bool) $value[ $post_type ][ $name ];
	}

	/**
	 * Gets a setting value from the cache or database.
	 *
	 * @param string $name The name of a string setting.
	 * @param string $post_type The post type slug.
	 * @param string $default_value The default value to return if the setting is not found.
	 *
	 * @return string
	 */
	public function get_string( string $name, string $post_type, string $default_value = '' ): string {
		$value = $this->get_cache_settings();

		if ( ! $this->is_setting_of_type( $name, 'string' ) || empty( $value[ $post_type ][ $name ] ) ) {
			return $default_value;
		}

		return (string) $value[ $post_type ][ $name ];
	}

	/**
	 * Verifies if a setting allowed in the settings config and compares the type is correct.
	 *
	 * @param string $name The name of a setting.
	 * @param string $type The type of the setting.
	 *
	 * @return mixed
	 */
	private function is_setting_of_type( string $name, string $type ): bool {
		return array_key_exists( $name, $this->settings_config ) && $this->settings_config[ $name ] === $type;
	}

}
