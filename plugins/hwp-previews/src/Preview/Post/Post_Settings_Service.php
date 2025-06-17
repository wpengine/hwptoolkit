<?php

declare(strict_types=1);

namespace HWP\Previews\Preview\Post;

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
	public function get_option_key(): string {
		return (string) apply_filters( 'hwp_previews_settings_group_option_key', HWP_PREVIEWS_SETTINGS_KEY );
	}

	/**
	 * The settings group for the options.
	 */
	public function get_settings_group(): string {
		return (string) apply_filters( 'hwp_previews_settings_group_settings_group', HWP_PREVIEWS_SETTINGS_GROUP );
	}

	/**
	 * Setup the settings values by retrieving them from the database or cache.
	 * This method is called in the constructor to ensure settings are available.
	 */
	protected function setup(): void {
		$option_key     = $this->get_option_key();
		$settings_group = $this->get_settings_group();

		$value = wp_cache_get( $option_key, $settings_group );
		if ( is_array( $value ) ) {
			$this->settings_values = $value;

			return;
		}

		$this->settings_values = (array) get_option( $option_key, [] );
		wp_cache_set( $option_key, $value, $settings_group );
	}
}
