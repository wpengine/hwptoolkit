<?php

declare(strict_types=1);

namespace HWP\Previews\Integration;

use HWP\Previews\Post\Type\Post_Types_Config_Registry;
use HWP\Previews\Preview\Helper\Settings_Group;

class Faust_Integration {
	/**
	 * The key for the admin notice.
	 *
	 * @var string
	 */
	public const FAUST_NOTICE_KEY = 'hwp_previews_faust_notice';

	/**
	 * Whether Faust is enabled.
	 */
	public static bool $faust_enabled = false;

	/**
	 * Initialize the hooks for the preview functionality.
	 */
	public static function init(): void {
		self::$faust_enabled = self::is_faust_enabled();

		self::configure_faust();
	}

	/**
	 * Configure Faust settings and remove conflicting filters.
	 */
	public static function configure_faust(): void {
		if ( self::$faust_enabled ) {
			self::set_default_faust_settings();

			// Remove FaustWP post preview link filter to avoid conflicts with our custom preview link generation.
			remove_filter( 'preview_post_link', 'WPE\FaustWP\Replacement\post_preview_link', 1000 );

			self::display_faust_admin_notice();
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

		if ( self::$faust_enabled && function_exists( '\WPE\FaustWP\Settings\faustwp_get_setting' ) ) {
			$frontend_uri = \WPE\FaustWP\Settings\faustwp_get_setting( 'frontend_uri', '' );

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
		return self::get_faust_frontend_url() . '/preview?p={ID}&preview=true&previewPathname=p{ID}&typeName={type}';
	}

	/**
	 * Sets default Faust settings if there are no existing settings.
	 */
	public static function set_default_faust_settings(): void {
		$settings_group = Settings_Group::get_instance();
		$types_config   = apply_filters(
			'hwp_previews_hooks_post_type_config',
			Post_Types_Config_Registry::get_post_type_config()
		);


		$plugin_settings = $settings_group->get_cached_settings();

		if ( ! empty( $plugin_settings ) ) {
			return;
		}

		$setting_preview_key = $settings_group->get_settings_key_preview_url();
		$setting_enabled_key = $settings_group->get_settings_key_enabled();

		$default_settings = [];

		foreach ( $types_config->get_public_post_types() as $key => $label ) {
			$default_settings[ $key ] = [
				$setting_enabled_key => true,
				$setting_preview_key => self::get_faust_preview_url(),
			];
		}

		update_option( HWP_PREVIEWS_SETTINGS_KEY, $default_settings );
	}

	/**
	 * Dismiss the Faust admin notice.
	 */
	public static function dismiss_faust_admin_notice(): void {
		update_user_meta( get_current_user_id(), self::FAUST_NOTICE_KEY, 1 );
	}

	/**
	 * Register admin notice to inform users about Faust integration.
	 */
	public static function register_faust_admin_notice(): void {
		add_action( 'admin_notices', static function (): void {
			$screen = get_current_screen();

			// Exit if not this plugin's settings page.
			if ( ! is_object( $screen ) || 'settings_page_hwp-previews' !== $screen->id ) {
				return;
			}

			$ajax_nonce = wp_create_nonce( self::FAUST_NOTICE_KEY );
			?>

			<div id="<?php echo esc_attr( self::FAUST_NOTICE_KEY ); ?>" class="notice notice-info is-dismissible">
				<p><?php esc_html_e( 'HWP Previews is automatically configured to support Faust previews. However, you can still customize it to fit your needs.', 'hwp-previews' ); ?></p>
			</div>

			<script>
				window.addEventListener( 'load', function() {
					const dismissBtn = document.querySelector( '#<?php echo esc_attr( self::FAUST_NOTICE_KEY ); ?> .notice-dismiss' );

					dismissBtn?.addEventListener( 'click', function( event ) {
						let postData = new FormData();
						postData.append('action', '<?php echo esc_attr( self::FAUST_NOTICE_KEY ); ?>');
						postData.append('_ajax_nonce', '<?php echo esc_html( $ajax_nonce ); ?>');

						window.fetch('<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>', {
							method: 'POST',
							body: postData,
						})
					});
				});
			</script>

			<?php
		}, 10, 0);
	}

	/**
	 * If Faust is enabled, show an admin notice about the migration on the settings page.
	 */
	public static function display_faust_admin_notice(): void {
		$is_dismissed = get_user_meta( get_current_user_id(), self::FAUST_NOTICE_KEY, true );

		// Exit if Faust is not enabled or if the notice has been dismissed.
		if ( ! self::$faust_enabled || (bool) $is_dismissed ) {
			return;
		}

		self::register_faust_admin_notice();

		// Register the AJAX action for dismissing the notice.
		add_action( 'wp_ajax_' . self::FAUST_NOTICE_KEY, static function (): void {
			// Exit if the action is not set or does not match the expected key.
			if ( ! isset( $_POST['action'] ) || esc_attr( self::FAUST_NOTICE_KEY ) !== $_POST['action'] ) {
				return;
			}

			check_ajax_referer( self::FAUST_NOTICE_KEY );

			self::dismiss_faust_admin_notice();
		}, 10, 0 );
	}
}
