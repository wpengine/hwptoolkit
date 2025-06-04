<?php

namespace HWP\Previews\Integration;

use HWP\Previews\Admin\Settings\Helper\Settings_Group;
use HWP\Previews\Post\Type\Contracts\Post_Types_Config_Interface;
use HWP\Previews\Post\Type\Post_Types_Config_Registry;

class Faust_Integration {
	/**
	 * Whether Faust is enabled.
	 */
    protected static bool $faust_enabled = false;

	/**
	 * Initialize the hooks for the preview functionality.
	 */
	public static function init(): void {
		self::$faust_enabled = self::is_faust_enabled();

		self::configure_faust();
	}

	public static function configure_faust(): void {
        if(self::$faust_enabled) {
			self::set_default_faust_settings();

			// Remove FaustWP post preview link filter to avoid conflicts with our custom preview link generation.
			remove_filter('preview_post_link', 'WPE\FaustWP\Replacement\post_preview_link', 1000);

			self::faust_admin_notice();            
        }     
	}

	/**
	 * Checks if Faust is enabled.
	 */
	public static function is_faust_enabled(): bool {
		if ( function_exists( 'is_plugin_active' ) ) {
			return is_plugin_active( 'faustwp/faustwp.php' );
		}

		return false;
	}

	/**
	 * Returns the Faust frontend URL from settings or a default value.
	 */
	public static function get_faust_frontend_url(): string {
		$default_value = 'http://localhost:3000';

		if ( self::$faust_enabled && function_exists('\WPE\FaustWP\Settings\faustwp_get_setting') ) {
			$frontend_uri = \WPE\FaustWP\Settings\faustwp_get_setting('frontend_uri', '');

			if ( ! empty( $frontend_uri ) ) {
				return $frontend_uri;
			}
		}

		return $default_value;
	}

	/**
	 * Get default preview URL for Faust.
	 */
	public static function get_faust_preview_url(): string {
		return self::get_faust_frontend_url() . "/preview?p={ID}&preview=true&previewPathname=p{ID}&typeName={type}";
	}

	/**
	 * Sets default Faust settings if there are no existing settings.
	 */
	public static function set_default_faust_settings(): void {
		$settings_group = Settings_Group::get_instance();
		$types_config = apply_filters(
			'hwp_previews_hooks_post_type_config',
			Post_Types_Config_Registry::get_post_type_config()
		);


        $plugin_settings = $settings_group->get_cached_settings();

        if( empty($plugin_settings) ) {
            $setting_preview_key = $settings_group->get_settings_key_preview_url();
            $setting_enabled_key = $settings_group->get_settings_key_enabled();

            $default_settings = array();
            
            foreach ( $types_config->get_public_post_types() as $key => $label ) {
                $default_settings[$key] = array(
					$setting_enabled_key => true,
                    $setting_preview_key => self::get_faust_preview_url(),
                );
            }

            update_option(HWP_PREVIEWS_SETTINGS_KEY, $default_settings);
        }
	}

	/**
	 * If Faust is enabled, show an admin notice about the migration on the settings page.
	 * TODO make the notice dismissible.
	 */
	public static function faust_admin_notice(): void {

		// Exit if Faust is not enabled.
		if( ! self::$faust_enabled ) {
			return;
		}

		add_action( 'admin_notices', function(): void {
			$screen = get_current_screen();

			// Exit if not this plugin's settings page.
			if ( ! is_object( $screen ) || 'settings_page_hwp-previews' !== $screen->id ) {
				return;
			}
			?>

			<div class="notice notice-info"> 
				<p><?php esc_html_e( 'HWP Previews is automatically configured to support Faust previews on the front end. However, you can still customize it to fit your needs.', HWP_PREVIEWS_TEXT_DOMAIN ); ?></p>
			</div>

			<?php
		}, 10, 0);
	}
}
