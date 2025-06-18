<?php

declare(strict_types=1);

namespace HWP\Previews\Preview\Helper;

class Settings_Helper {
	/**
	 * The settings group.
	 *
	 * @var \HWP\Previews\Preview\Helper\Settings_Group
	 */
	protected Settings_Group $settings_group;

	/**
	 * The settings helper instance.
	 *
	 * @var \HWP\Previews\Preview\Helper\Settings_Helper|null
	 */
	protected static $instance = null;

	/**
	 * @param \HWP\Previews\Preview\Helper\Settings_Group $settings_group The settings group.
	 */
	public function __construct( Settings_Group $settings_group ) {
		$this->settings_group = $settings_group;
	}

	/**
	 * Get an instance of the Settings_Helper class.
	 *
	 * @return \HWP\Previews\Preview\Helper\Settings_Helper
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
}
