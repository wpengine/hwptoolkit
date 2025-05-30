<?php

declare(strict_types=1);

namespace HWP\Previews\Admin\Settings\Helper;

class Settings_Helper {
	/**
	 * The settings group.
	 *
	 * @var \HWP\Previews\Admin\Settings\Helper\Settings_Group
	 */
	protected Settings_Group $settings_group;

	/**
	 * The settings helper instance.
	 *
	 * @var \HWP\Previews\Admin\Settings\Helper\Settings_Helper|null
	 */
	protected static $instance = null;

	/**
	 * @param \HWP\Previews\Admin\Settings\Helper\Settings_Group $settings_group The settings group.
	 */
	public function __construct( Settings_Group $settings_group ) {
		$this->settings_group = $settings_group;
	}

	/**
	 * Get an instance of the Settings_Helper class.
	 *
	 * @return \HWP\Previews\Admin\Settings\Helper\Settings_Helper
	 */
	public static function get_instance(): self {

		$instance = self::$instance;

		if ( $instance instanceof self ) {
			return $instance;
		}

		self::$instance = new self( Settings_Group::get_instance() );

		return self::$instance;
	}

	/**
	 * Get the settings group.
	 *
	 * @return array<string, mixed>
	 */
	public function get_settings_config(): array {
		return $this->settings_group->get_settings_config();
	}

	/**
	 * Get all post types that are enabled in the settings.
	 *
	 * @param array<string> $default_value Default post types to return if none are enabled.
	 *
	 * @return array<string>
	 */
	public function post_types_enabled( array $default_value = [] ): array {
		$settings = $this->settings_group->get_cached_settings();

		$enabled_key = $this->settings_group->get_settings_key_enabled();

		$enabled_post_types = [];
		foreach ( $settings as $key => $item ) {
			if ( (bool) ( $item[ $enabled_key ] ?? false ) ) {
				$enabled_post_types[] = $key;
			}
		}

		if ( ! empty( $enabled_post_types ) ) {
			return $enabled_post_types;
		}
		return $default_value;
	}

	/**
	 * Get Post Statuses as Parent setting value for the given post type.
	 *
	 * @param string $post_type The post type to get the setting for.
	 * @param bool   $default_value The default value to return if the setting is not set.
	 */
	public function post_statuses_as_parent( string $post_type, bool $default_value = false ): bool {
		$key = $this->settings_group->get_settings_key_post_statuses_as_parent();

		return $this->settings_group->get_post_type_boolean_value( $key, $post_type, $default_value );
	}

	/**
	 * Show In iframe value for the given post type.
	 *
	 * @param string $post_type The post type to get the setting for.
	 * @param bool   $default_value The default value to return if the setting is not set.
	 */
	public function in_iframe( string $post_type, bool $default_value = false ): bool {

		$key = $this->settings_group->get_settings_key_in_iframe();

		return $this->settings_group->get_post_type_boolean_value( $key, $post_type, $default_value );
	}

	/**
	 * URL template setting value for the given post type.
	 *
	 * @param string $post_type The post type to get the setting for.
	 * @param string $default_value The default value to return if the setting is not set.
	 */
	public function url_template( string $post_type, string $default_value = '' ): string {
		$key = $this->settings_group->get_settings_key_preview_url();

		return $this->settings_group->get_post_type_string_value( $key, $post_type, $default_value );
	}
}
