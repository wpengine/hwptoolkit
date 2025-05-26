<?php

namespace HWP\Previews\Admin\Settings\Helper;

class Settings_Group {

	/**
	 * The settings helper instance.
	 *
	 * @var Settings_Group|null
	 */
	protected static $instance = null;

	protected string $option_key;

	protected string $settings_group;

	protected array $settings_config = [];

	public function __construct() {
		$this->option_key      = $this->get_option_key();
		$this->settings_group  = $this->get_settings_group();
		$this->settings_config = $this->get_settings_config();
		$this->set_cache_group();
	}

	protected function get_option_key(): string {
		return apply_filters( 'hwp_previews_settings_group_option_key', HWP_PREVIEWS_SETTINGS_KEY );
	}

	protected function get_settings_group(): string {
		return apply_filters( 'hwp_previews_settings_group_settings_group', HWP_PREVIEWS_SETTINGS_GROUP );
	}


	public function get_settings_key_enabled(): string {
		return 'enabled';
	}

	public function get_settings_key_unique_post_slugs(): string {
		return 'unique_post_slugs';
	}

	public function get_settings_key_post_statuses_as_parent(): string {
		return 'post_statuses_as_parent';
	}

	public function get_settings_key_preview_url(): string {
		return 'preview_url';
	}

	public function get_settings_key_in_iframe(): string {
		return 'in_iframe';
	}

	public function get_settings_config(): array {
		return apply_filters( 'hwp_previews_settings_group_settings_config', [
			$this->get_settings_key_enabled()                 => 'bool',
			$this->get_settings_key_unique_post_slugs()       => 'bool',
			$this->get_settings_key_post_statuses_as_parent() => 'bool',
			$this->get_settings_key_preview_url()             => 'string',
			$this->get_settings_key_in_iframe()               => 'bool',
		] );
	}

	protected function set_cache_group(): void {
		$groups = apply_filters( 'hwp_previews_settings_group_cache_groups', [ $this->settings_group ] );
		wp_cache_add_non_persistent_groups( $groups );
	}


	public static function get_instance(): self {

		$instance = self::$instance;

		if ( $instance instanceof self ) {
			return $instance;
		}

		self::$instance = new self();

		return self::$instance;
	}

	/**
	 * Gets settings from the cache or database.
	 *
	 * @return array<string, mixed>
	 */
	public function get_cached_settings(): array {

		$value = wp_cache_get( $this->option_key, $this->settings_group );
		if ( is_array( $value ) ) {
			return $value;
		}

		$value = (array) get_option( $this->option_key, [] );
		wp_cache_set( $this->option_key, $value, $this->settings_group );

		return $value;
	}

	/**
	 * Gets a boolean value for a specific setting in a post type.
	 *
	 * @param string $name The setting name.
	 * @param string $post_type The post type slug.
	 * @param bool $default_value The default value to return if the setting is not found.
	 *
	 * @return bool
	 */
	public function get_post_type_boolean_value( string $name, string $post_type, bool $default_value = false ): bool {
		$settings = $this->get_cached_settings();
		$type     = $this->settings_config[ $name ] ?? null;
		if ( $type === 'bool' && isset( $settings[ $post_type ][ $name ] ) ) {
			return (bool) $settings[ $post_type ][ $name ];
		}

		return $default_value;
	}

	/**
	 *  Gets a string value for a specific setting in a post type.
	 *
	 * @param string $name The setting name.
	 * @param string $post_type The post type slug.
	 * @param string $default_value The default value to return if the setting is not found.
	 *
	 * @return string
	 */
	public function get_post_type_string_value( string $name, string $post_type, string $default_value = '' ): string {
		$settings = $this->get_cached_settings();
		$type     = $this->settings_config[ $name ] ?? null;
		if ( $type === 'string' && isset( $settings[ $post_type ][ $name ] ) ) {
			return (string) $settings[ $post_type ][ $name ];
		}

		return $default_value;
	}

}
