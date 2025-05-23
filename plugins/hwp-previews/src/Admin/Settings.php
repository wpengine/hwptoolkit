<?php

namespace HWP\Previews\Admin;

use HWP\Previews\Admin\Settings\Fields\Checkbox_Field;
use HWP\Previews\Admin\Settings\Fields\Text_Input_Field;
use HWP\Previews\Admin\Settings\Menu\Menu_Page;
use HWP\Previews\Admin\Settings\Settings_Section;
use HWP\Previews\Admin\Settings\Tabbed_Settings;
use HWP\Previews\Plugin;
use HWP\Previews\Post\Type\Contracts\Post_Types_Config_Interface;
use HWP\Previews\Preview\Parameter\Preview_Parameter;
use HWP\Previews\Preview\Parameter\Preview_Parameter_Registry;
use WP_Post;

class Settings {

	/**
	 * @TODO move along with other functionaligyt into a Settings page class.
	 *
	 * Preview parameter registry.
	 *
	 * @var Preview_Parameter_Registry
	 */
	public static ?Preview_Parameter_Registry $parameters = null;

	/**
	 * @TODO - Refactor this dependency injection.
	 *
	 * @var Post_Types_Config_Interface|null
	 */
	public static ?Post_Types_Config_Interface $types_config = null;

	/**
	 * The slug for the plugin menu.
	 *
	 * @var string
	 */
	public const PLUGIN_MENU_SLUG = 'hwp-previews';

	public static function init(Post_Types_Config_Interface $types_config): void {
		self::$types_config = $types_config;
		self::register_parameters();
		self::register_settings_pages();
		self::register_settings_fields();
		self::load_scripts_styles();
	}

	public static function register_settings_pages(): void {
		// @TODO: Move under settings page class or WPGraphQL - https://github.com/wpengine/hwptoolkit/issues/205
		// Also see comment for load_scripts_styles
		add_action( 'admin_menu', function (): void {
			/**
			 * Array of post types where key is the post type slug and value is the label.
			 *
			 * @var array<string, string> $post_types
			 */
			$post_types = apply_filters( 'hwp_previews_filter_post_type_setting', self::$types_config->get_public_post_types() );

			self::create_settings_page( $post_types )->register_page();

		} );
	}

	public static function register_settings_fields(): void {
		add_action( 'admin_init', function (): void {

			/**
			 * Array of post types where key is the post type slug and value is the label.
			 *
			 * @var array<string, string> $post_types
			 */
			$post_types = apply_filters( 'hwp_previews_filter_post_type_setting', self::$types_config->get_public_post_types() );

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
		add_action( 'admin_enqueue_scripts', function ( string $hook ): void {
			// @TODO - Change as part of https://github.com/wpengine/hwptoolkit/issues/205
			if ( 'toplevel_page_' . self::PLUGIN_MENU_SLUG !== $hook ) {
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
		return new Menu_Page(
			__( 'HWP Previews Settings', HWP_PREVIEWS_TEXT_DOMAIN ),
			'HWP Previews',
			self::PLUGIN_MENU_SLUG,
			trailingslashit(HWP_PREVIEWS_PLUGIN_DIR) . '/src/Admin/Settings/Templates/Admin/settings-page-main.php',
			[
				'hwp_previews_main_page_config' => [
					'tabs'        => $post_types,
					'current_tab' => self::get_current_tab( $post_types ),
					'params'      => self::$parameters->get_descriptions(),
				],
			],
			'dashicons-welcome-view-site'
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
	 * @param bool $is_hierarchical Whether the post type is hierarchical.
	 *
	 * @return array<\HWP\Previews\Admin\Settings\Fields\Abstract_Settings_Field>
	 */
	public static function create_settings_fields( string $post_type, string $label, bool $is_hierarchical ): array {
		$fields   = [];
		$fields[] = new Checkbox_Field(
			'enabled',
			// translators: %s is the label of the post type.
			sprintf( __( 'Enable HWP Previews for %s', HWP_PREVIEWS_TEXT_DOMAIN ), $label ),
			__( 'Turn preview functionality on or off for this public post type.', HWP_PREVIEWS_TEXT_DOMAIN )
		);
		$fields[] = new Checkbox_Field(
			'unique_post_slugs',
			__( 'Enable unique post slugs for all post statuses', HWP_PREVIEWS_TEXT_DOMAIN ),
			__( 'By default WordPress adds unique post slugs to the published posts. This option enforces unique slugs for all post statuses.', HWP_PREVIEWS_TEXT_DOMAIN )
		);

		if ( $is_hierarchical ) {
			$fields[] = new Checkbox_Field(
				'post_statuses_as_parent',
				__( 'Allow all post statuses in parents option', HWP_PREVIEWS_TEXT_DOMAIN ),
				__( 'By default WordPress only allows published posts to be parents. This option allows posts of all statuses to be used as parent within hierarchical post types.', HWP_PREVIEWS_TEXT_DOMAIN )
			);
		}

		$fields[] = new Checkbox_Field(
			'in_iframe',
			sprintf( __( 'Load previews in iframe', HWP_PREVIEWS_TEXT_DOMAIN ), $label ),
			__( 'With this option enabled, headless previews will be displayed inside an iframe on the preview page, without leaving WordPress.', HWP_PREVIEWS_TEXT_DOMAIN )
		);
		$fields[] = new Text_Input_Field(
			'preview_url',
			// translators: %s is the label of the post type.
			sprintf( __( 'Preview URL for %s', HWP_PREVIEWS_TEXT_DOMAIN ), $label ),
			__( 'Construct your preview URL using the tags on the right. You can add any parameters needed to support headless previews.', HWP_PREVIEWS_TEXT_DOMAIN ),
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
		return new Tabbed_Settings(
			HWP_PREVIEWS_SETTINGS_GROUP,
			HWP_PREVIEWS_SETTINGS_KEY,
			array_keys( $post_types ),
			// @TODO Refactor as this is not the right approach
			// We shouldn't have these in a constant array in the Plugin class.
			[
				Plugin::ENABLED_FIELD                 => 'bool',
				Plugin::UNIQUE_POST_SLUGS_FIELD       => 'bool',
				Plugin::POST_STATUSES_AS_PARENT_FIELD => 'bool',
				Plugin::PREVIEW_URL_FIELD             => 'string',
				Plugin::IN_IFRAME_FIELD               => 'bool',
			]
		);
	}

	/**
	 * Get the current tab for the settings page.
	 *
	 * @param array<string> $post_types The post types to be used in the settings page.
	 * @param string $tab The name of the tab.
	 */
	public static function get_current_tab( $post_types, string $tab = 'tab' ): string {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET[ $tab ] ) && is_string( $_GET[ $tab ] ) ) {
			return sanitize_key( $_GET[ $tab ] );
		}

		return ! empty( $post_types ) ? (string) key( $post_types ) : '';
	}

	/**
	 * @TODO Refactor - Just getting this to work for now.
	 *
	 * @return Preview_Parameter_Registry
	 */
	public static function register_parameters(): void {

		$params = new Preview_Parameter_Registry();
		$params
			->register(
				new Preview_Parameter( 'ID', static fn( WP_Post $post ) => (string) $post->ID, __( 'Post ID.', HWP_PREVIEWS_TEXT_DOMAIN ) )
			)->register(
				new Preview_Parameter( 'author_ID', static fn( WP_Post $post ) => $post->post_author, __( 'ID of post author..', HWP_PREVIEWS_TEXT_DOMAIN ) )
			)->register(
				new Preview_Parameter( 'status', static fn( WP_Post $post ) => $post->post_status, __( 'The post\'s status..', HWP_PREVIEWS_TEXT_DOMAIN ) )
			)->register(
				new Preview_Parameter( 'slug', static fn( WP_Post $post ) => $post->post_name, __( 'The post\'s slug.', HWP_PREVIEWS_TEXT_DOMAIN ) )
			)->register(
				new Preview_Parameter( 'parent_ID', static fn( WP_Post $post ) => (string) $post->post_parent, __( 'ID of a post\'s parent post.', HWP_PREVIEWS_TEXT_DOMAIN ) )
			)->register(
				new Preview_Parameter( 'type', static fn( WP_Post $post ) => $post->post_type, __( 'The post\'s type, like post or page.', HWP_PREVIEWS_TEXT_DOMAIN ) )
			)->register(
				new Preview_Parameter( 'uri', static fn( WP_Post $post ) => (string) get_page_uri( $post ), __( 'The URI path for a page.', HWP_PREVIEWS_TEXT_DOMAIN ) )
			)->register(
				new Preview_Parameter( 'template', static fn( WP_Post $post ) => (string) get_page_template_slug( $post ), __( 'Specific template filename for a given post.', HWP_PREVIEWS_TEXT_DOMAIN ) )
			);

		self::$parameters = $params;
	}
}
