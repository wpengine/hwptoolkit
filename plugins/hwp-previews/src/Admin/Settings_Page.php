<?php

declare(strict_types=1);

namespace HWP\Previews\Admin;

use HWP\Previews\Admin\Settings\Fields\Checkbox_Field;
use HWP\Previews\Admin\Settings\Fields\Text_Input_Field;
use HWP\Previews\Admin\Settings\Helper\Settings_Helper;
use HWP\Previews\Admin\Settings\Menu\Menu_Page;
use HWP\Previews\Admin\Settings\Settings_Section;
use HWP\Previews\Admin\Settings\Tabbed_Settings;
use HWP\Previews\Post\Type\Contracts\Post_Types_Config_Interface;
use HWP\Previews\Post\Type\Post_Types_Config_Registry;
use HWP\Previews\Preview\Parameter\Preview_Parameter_Registry;

class Settings_Page {
	/**
	 * @var string The slug for the plugin menu.
	 */
	public const PLUGIN_MENU_SLUG = 'hwp-previews';

	/**
	 * @var \HWP\Previews\Preview\Parameter\Preview_Parameter_Registry|null  The registry of preview parameters.
	 */
	protected static ?Preview_Parameter_Registry $parameters = null;

	/**
	 * @var \HWP\Previews\Post\Type\Contracts\Post_Types_Config_Interface|null The post types available for previews..
	 */
	protected static ?Post_Types_Config_Interface $types_config = null;

	/**
	 * Initializes the settings page and registers the necessary properties, configuration and hooks.
	 */
	public static function init(): void {
		self::init_class_properties();
		self::register_settings_pages();
		self::register_settings_fields();
		self::load_scripts_styles();
	}

	/**
	 * Initializes the class properties.
	 */
	public static function init_class_properties(): void {

		self::$parameters   = Preview_Parameter_Registry::get_instance();
		self::$types_config = Post_Types_Config_Registry::get_post_type_config();
	}

	/**
	 * Registers the settings page.
	 */
	public static function register_settings_pages(): void {
		add_action( 'admin_menu', static function (): void {

			$post_types = ( null === self::$types_config ) ? [] : self::$types_config->get_public_post_types();

			/**
			 * Array of post types where key is the post type slug and value is the label.
			 *
			 * @var array<string, string> $post_types
			 */
			$post_types = apply_filters( 'hwp_previews_filter_post_type_setting', $post_types );
			self::create_settings_page( $post_types )->register_page();
		} );
	}

	/**
	 * Registers the settings fields for each post type.
	 */
	public static function register_settings_fields(): void {
		add_action( 'admin_init', static function (): void {

			$post_types = ( null === self::$types_config ) ? [] : self::$types_config->get_public_post_types();

			/**
			 * Array of post types where key is the post type slug and value is the label.
			 *
			 * @var array<string, string> $post_types
			 */
			$post_types = apply_filters( 'hwp_previews_filter_post_type_setting', $post_types );

			/**
			 * Register setting itself.
			 */
			self::create_tabbed_settings( $post_types )->register_settings();

			/**
			 * Register settings sections and fields for each post type.
			 */
			foreach ( $post_types as $post_type => $label ) {
				self::create_setting_section( $post_type, $label )->register_section( HWP_PREVIEWS_SETTINGS_KEY, $post_type, "hwp-previews-{$post_type}" );
			}
		}, 10, 0 );
	}

	/**
	 * Enqueues the JavaScript and the CSS file for the plugin admin area.
	 */
	public static function load_scripts_styles(): void {
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

	/**
	 * Creates the settings page.
	 *
	 * @param array<string> $post_types The post types to be used in the settings page.
	 */
	public static function create_settings_page( array $post_types ): Menu_Page {

		$descriptions = ( null === self::$parameters ) ? [] : self::$parameters->get_descriptions();


		return new Menu_Page(
			__( 'HWP Previews Settings', 'hwp-previews' ),
			'HWP Previews',
			self::PLUGIN_MENU_SLUG,
			trailingslashit( HWP_PREVIEWS_TEMPLATE_DIR ) . 'settings-page-main.php',
			[
				'hwp_previews_main_page_config' => [
					'tabs'        => $post_types,
					'current_tab' => self::get_current_tab( $post_types ),
					'params'      => $descriptions,
				],
			],
		);
	}

	/**
	 * Creates the settings section for a specific post type.
	 *
	 * @param string $post_type The post type slug.
	 * @param string $label The label for the post type.
	 */
	public static function create_setting_section( string $post_type, string $label ): Settings_Section {
		return new Settings_Section(
			'hwp_previews_section_' . $post_type,
			'',
			'hwp-previews-' . $post_type,
			self::create_settings_fields( $post_type, $label, is_post_type_hierarchical( $post_type ) )
		);
	}

	/**
	 * Creates the settings fields for a specific post type.
	 *
	 * @param string $post_type The post type slug.
	 * @param string $label The label for the post type.
	 * @param bool   $is_hierarchical Whether the post type is hierarchical.
	 *
	 * @return array<\HWP\Previews\Admin\Settings\Fields\Abstract_Settings_Field>
	 */
	public static function create_settings_fields( string $post_type, string $label, bool $is_hierarchical ): array {
		$fields   = [];
		$fields[] = new Checkbox_Field(
			'enabled',
			// translators: %s is the label of the post type.
			sprintf( __( 'Enable HWP Previews for %s', 'hwp-previews' ), $label ),
			__( 'Turn preview functionality on or off for this public post type.', 'hwp-previews' )
		);

		if ( $is_hierarchical ) {
			$fields[] = new Checkbox_Field(
				'post_statuses_as_parent',
				__( 'Allow all post statuses in parents option', 'hwp-previews' ),
				__( 'By default WordPress only allows published posts to be parents. This option allows posts of all statuses to be used as parent within hierarchical post types.', 'hwp-previews' )
			);
		}

		$fields[] = new Checkbox_Field(
			'in_iframe',
			sprintf( __( 'Load previews in iframe', 'hwp-previews' ), $label ),
			__( 'With this option enabled, headless previews will be displayed inside an iframe on the preview page, without leaving WordPress.', 'hwp-previews' )
		);
		$fields[] = new Text_Input_Field(
			'preview_url',
			// translators: %s is the label of the post type.
			sprintf( __( 'Preview URL for %s', 'hwp-previews' ), $label ),
			__( 'Construct your preview URL using the tags on the right. You can add any parameters needed to support headless previews.', 'hwp-previews' ),
			"https://localhost:3000/{$post_type}?preview=true&post_id={ID}&name={slug}",
			'code hwp-previews-url' // The class is being used as a query for the JS.
		);

		return $fields;
	}

	/**
	 * Creates the tabbed settings object.
	 *
	 * @param array<string> $post_types Post Types as a tabs.
	 */
	public static function create_tabbed_settings( array $post_types ): Tabbed_Settings {
		$helper = Settings_Helper::get_instance();

		return new Tabbed_Settings(
			HWP_PREVIEWS_SETTINGS_GROUP,
			HWP_PREVIEWS_SETTINGS_KEY,
			array_keys( $post_types ),
			$helper->get_settings_config()
		);
	}

	/**
	 * Get the current tab for the settings page.
	 *
	 * @param array<string> $post_types The post types to be used in the settings page.
	 * @param string        $tab The name of the tab.
	 */
	public static function get_current_tab( $post_types, string $tab = 'tab' ): string {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET[ $tab ] ) && is_string( $_GET[ $tab ] ) ) {
			return sanitize_key( $_GET[ $tab ] );
		}

		return ! empty( $post_types ) ? (string) key( $post_types ) : '';
	}
}
