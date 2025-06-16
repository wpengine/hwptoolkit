<?php

declare(strict_types=1);

namespace HWP\Previews\Admin;

use HWP\Previews\Admin\Settings\Fields\Settings_Field_Collection;
use HWP\Previews\Admin\Settings\Menu\Menu_Page;
use HWP\Previews\Admin\Settings\Settings_Form_Manager;
use HWP\Previews\Preview\Parameter\Preview_Parameter_Registry;
use HWP\Previews\Preview\Post\Type\Contracts\Post_Types_Config_Interface;
use HWP\Previews\Preview\Post\Type\Post_Types_Config_Registry;

class Settings_Page {
	/**
	 * @var string The slug for the plugin menu.
	 */
	public const PLUGIN_MENU_SLUG = 'hwp-previews';

	/**
	 * @var \HWP\Previews\Preview\Parameter\Preview_Parameter_Registry  The registry of preview parameters.
	 */
	protected Preview_Parameter_Registry $parameters;

	/**
	 * @var \HWP\Previews\Preview\Post\Type\Contracts\Post_Types_Config_Interface The post types available for previews.
	 */
	protected Post_Types_Config_Interface $types_config;

	/**
	 * The instance of the plugin.
	 *
	 * @var \HWP\Previews\Admin\Settings_Page|null
	 */
	protected static ?Settings_Page $instance = null;

	/**
	 * Constructor.
	 *
	 * Initializes the settings page, registers settings fields, and loads scripts and styles.
	 */
	public function __construct() {
		$this->parameters   = Preview_Parameter_Registry::get_instance();
		$this->types_config = Post_Types_Config_Registry::get_post_type_config();

		$this->register_settings_pages();
		$this->register_settings_fields();
		$this->load_scripts_styles();
	}

	/**
	 * Initializes the settings page.
	 */
	public static function init(): Settings_Page {
		if ( ! isset( self::$instance ) || ! ( is_a( self::$instance, self::class ) ) ) {
			self::$instance = new self();
		}

		/**
		 * Fire off init action.
		 *
		 * @param \HWP\Previews\Admin\Settings_Page $instance the instance of the plugin class.
		 */
		do_action( 'hwp_previews_init', self::$instance );

		return self::$instance;
	}

	/**
	 * Registers the settings page.
	 */
	public function register_settings_pages(): void {
		add_action( 'admin_menu', function (): void {

			// Note: We didn't initalize in the constructor because we need to ensure
			// the post types are registered before we can use them.
			$post_types = $this->types_config->get_public_post_types();

			$page = new Menu_Page(
				__( 'HWP Previews Settings', 'hwp-previews' ),
				'HWP Previews',
				self::PLUGIN_MENU_SLUG,
				trailingslashit( HWP_PREVIEWS_PLUGIN_DIR ) . 'src/Templates/admin.php',
				[
					'hwp_previews_main_page_config' => [
						'tabs'        => $post_types,
						'current_tab' => $this->get_current_tab( $post_types ),
						'params'      => $this->parameters->get_descriptions(),
					],
				],
			);

			$page->register_page();
		} );
	}

	/**
	 * Registers the settings fields for each post type.
	 */
	public function register_settings_fields(): void {
		add_action( 'admin_init', function (): void {
			$settings_manager = new Settings_Form_Manager(
				$this->types_config->get_public_post_types(),
				new Settings_Field_Collection()
			);
			$settings_manager->render_form();
		}, 10, 0 );
	}

	/**
	 * Get the current tab for the settings page.
	 *
	 * @param array<string> $post_types The post types to be used in the settings page.
	 * @param string        $tab The name of the tab.
	 */
	public function get_current_tab( array $post_types, string $tab = 'tab' ): string {
		if ( empty( $post_types ) ) {
			return '';
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Nonce verification not required for tab selection.
		$value = $_GET[ $tab ] ?? '';
		if ( ! is_string( $value ) || '' === $value ) {
			return (string) key( $post_types );
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce verification not required for tab selection.
		return sanitize_key( $value );
	}

	/**
	 * Enqueues the JavaScript and the CSS file for the plugin admin area.
	 */
	public function load_scripts_styles(): void {
		add_action( 'admin_enqueue_scripts', static function ( string $hook ): void {

			if ( 'settings_page_' . self::PLUGIN_MENU_SLUG !== $hook ) {
				return;
			}

			wp_enqueue_script(
				'hwp-previews-js',
				trailingslashit( HWP_PREVIEWS_PLUGIN_URL ) . 'assets/js/hwp-previews.js',
				[],
				HWP_PREVIEWS_VERSION,
				true
			);

			wp_enqueue_style(
				'hwp-previews-css',
				trailingslashit( HWP_PREVIEWS_PLUGIN_URL ) . 'assets/css/hwp-previews.css',
				[],
				HWP_PREVIEWS_VERSION
			);
		} );
	}
}
