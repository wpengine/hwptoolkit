<?php

declare(strict_types=1);

namespace HWP\Previews\Integration;

use HWP\Previews\Admin\Settings\Fields\Settings_Field_Collection;
use HWP\Previews\Preview\Post\Post_Preview_Service;
use HWP\Previews\Preview\Post\Post_Settings_Service;
use function WPE\FaustWP\Settings\faustwp_get_setting;

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
	public bool $faust_enabled = false;

	/**
	 * Whether Faust is enabled.
	 */
	public bool $faust_configured = false;

	/**
	 * The instance of the Faust integration.
	 *
	 * @var \HWP\Previews\Integration\Faust_Integration|null
	 */
	protected static ?Faust_Integration $instance = null;

	/**
	 * Faust_Integration constructor.
	 *
	 * Initializes the Faust integration if Faust is enabled.
	 */
	public function __construct() {
		$this->faust_enabled = $this->is_faust_enabled();
		$this->configure_faust();
	}

	/**
	 * Initialize the hooks for the preview functionality.
	 */
	public static function init(): Faust_Integration {
		if ( ! isset( self::$instance ) || ! ( is_a( self::$instance, self::class ) ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Checks if Faust is enabled.
	 */
	public function is_faust_enabled(): bool {
		return is_plugin_active( 'faustwp/faustwp.php' );
	}

	/**
	 * Get the Faust enabled status.
	 */
	public function get_faust_enabled(): bool {
		return $this->faust_enabled;
	}

	/**
	 * Get the Faust configured status.
	 */
	public function get_faust_configured(): bool {
		return $this->faust_configured;
	}

	/**
	 * Returns the Faust frontend URL from settings or a default value.
	 */
	public function get_faust_frontend_url(): string {
		$default_value = 'http://localhost:3000';

		if ( $this->get_faust_enabled() && function_exists( '\WPE\FaustWP\Settings\faustwp_get_setting' ) ) {
			$frontend_uri = faustwp_get_setting( 'frontend_uri', '' );

			if ( ! empty( $frontend_uri ) ) {
				return $frontend_uri;
			}
		}

		return $default_value;
	}

	/**
	 * Get default preview URL for Faust.
	 */
	public function get_faust_preview_url(): string {
		return self::get_faust_frontend_url() . '/preview?p={ID}&preview=true&previewPathname=p{ID}&typeName={type}';
	}

	/**
	 * Sets default Faust settings if there are no existing settings.
	 */
	public function set_default_faust_settings(): void {

		$settings_helper = new Post_Settings_Service();
		$plugin_settings = $settings_helper->get_settings_values();

		// If already configured, do not overwrite.
		if ( ! empty( $plugin_settings ) ) {
			return;
		}

		$post_preview_service = new Post_Preview_Service();
		$post_types           = $post_preview_service->get_post_types();

		$setting_preview_key = Settings_Field_Collection::PREVIEW_URL_FIELD_ID;
		$setting_enabled_key = Settings_Field_Collection::ENABLED_FIELD_ID;

		$default_settings = [];
		foreach ( $post_types as $type => $label ) {
			$default_settings[ $type ] = [
				$setting_enabled_key => true,
				$setting_preview_key => self::get_faust_preview_url(),
			];
		}

		update_option( HWP_PREVIEWS_SETTINGS_KEY, $default_settings );
	}

	/**
	 * Register admin notice to inform users about Faust integration.
	 */
	public function register_faust_admin_notice(): void {
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
				window.addEventListener('load', function () {
					const dismissBtn = document.querySelector('#<?php echo esc_attr( self::FAUST_NOTICE_KEY ); ?> .notice-dismiss');

					dismissBtn?.addEventListener('click', function (event) {
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
		}, 10, 0 );
	}

	/**
	 * Dismiss the Faust admin notice.
	 */
	public static function dismiss_faust_admin_notice(): void {
		update_user_meta( get_current_user_id(), self::FAUST_NOTICE_KEY, 1 );
	}

	/**
	 * Configure Faust settings and remove conflicting filters.
	 */
	protected function configure_faust(): void {
		if ( ! $this->get_faust_enabled() ) {
			return;
		}
		$this->faust_configured = true;

		$this->set_default_faust_settings();

		// Remove FaustWP post preview link filter to avoid conflicts with our custom preview link generation.
		remove_filter( 'preview_post_link', 'WPE\FaustWP\Replacement\post_preview_link', 1000 );

		$this->display_faust_admin_notice();
	}

	/**
	 * If Faust is enabled, show an admin notice about the migration on the settings page.
	 */
	protected function display_faust_admin_notice(): void {
		$is_dismissed = get_user_meta( get_current_user_id(), self::FAUST_NOTICE_KEY, true );

		// Exit if Faust is not enabled or if the notice has been dismissed.
		if ( ! $this->get_faust_enabled() || (bool) $is_dismissed ) {
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
