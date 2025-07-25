<?php

declare(strict_types=1);

namespace HWP\Previews\Preview\Post;

/**
 * Post-Settings Service class
 *
 * This class provides methods to retrieve and manage post-settings for the HWP Previews plugin.
 *
 * @package HWP\Previews
 *
 * @since 0.0.1
 */
class Post_Settings_Service {
	/**
	 * The settings value
	 *
	 * @var array<mixed>
	 */
	protected $settings_values = [];

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
	 * @param string $post_type
	 *
	 * @return array<mixed>|null
	 */
	public function get_post_type_config( string $post_type ): ?array {
		return $this->settings_values[ $post_type ] ?? null;
	}

	/**
	 * The option key for the settings group.
	 */
	public static function get_option_key(): string {
		return (string) apply_filters( 'hwp_previews_settings_group_option_key', HWP_PREVIEWS_SETTINGS_KEY );
	}

	/**
	 * The settings group for the options.
	 */
	public static function get_settings_group(): string {
		return (string) apply_filters( 'hwp_previews_settings_group_settings_group', HWP_PREVIEWS_SETTINGS_GROUP );
	}

	/**
	 * Set up the settings values it by retrieving them from the database or cache.
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
		wp_cache_set( $option_key, $value, $settings_group );
	}
}
