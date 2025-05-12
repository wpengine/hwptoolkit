<?php

declare(strict_types=1);

namespace HWP\Previews;

use HWP\Previews\Post\Data\Post_Data_Model;
use HWP\Previews\Post\Parent\Post_Parent_Manager;
use HWP\Previews\Post\Slug\Post_Slug_Manager;
use HWP\Previews\Post\Slug\Post_Slug_Repository;
use HWP\Previews\Post\Status\Contracts\Post_Statuses_Config_Interface;
use HWP\Previews\Post\Status\Post_Statuses_Config;
use HWP\Previews\Post\Type\Contracts\Post_Types_Config_Interface;
use HWP\Previews\Post\Type\Post_Types_Config;
use HWP\Previews\Preview\Link\Preview_Link_Placeholder_Resolver;
use HWP\Previews\Preview\Link\Preview_Link_Service;
use HWP\Previews\Preview\Parameter\Preview_Parameter;
use HWP\Previews\Preview\Parameter\Preview_Parameter_Registry;
use HWP\Previews\Preview\Template\Preview_Template_Resolver;
use HWP\Previews\Settings\Fields\Checkbox_Field;
use HWP\Previews\Settings\Fields\Text_Input_Field;
use HWP\Previews\Settings\Menu\Menu_Page;
use HWP\Previews\Settings\Menu\Submenu_Page;
use HWP\Previews\Settings\Preview_Settings;
use HWP\Previews\Settings\Settings_Cache_Group;
use HWP\Previews\Settings\Settings_Section;
use HWP\Previews\Settings\Tabbed_Settings;
use WP_Post;
use WP_Post_Type;
use WP_REST_Response;

/**
 * Plugin class for HWP Previews.
 *
 * This class serves as the main entry point for the plugin, handling initialization, action and filter hooks.
 *
 * @package HWP\Previews
 */
class Plugin {

	/**
	 * The slug for the plugin menu.
	 *
	 * @var string
	 */
	public const PLUGIN_MENU_SLUG = 'hwp-previews';

	/**
	 * The slug for the plugin settings page.
	 *
	 * @var string
	 */
	public const PLUGIN_JS_HANDLE = 'hwp-previews-js';

	/**
	 * The path to the JavaScript file for the plugin.
	 *
	 * @var string
	 */
	public const PLUGIN_JS_SRC = 'assets/js/hwp-previews.js';

	/**
	 * Settings group name used for the plugin.
	 *
	 * @var string
	 */
	public const SETTINGS_GROUP = 'hwp_previews_settings_group';

	/**
	 * Settings key used for the plugin.
	 *
	 * @var string
	 */
	public const SETTINGS_KEY = 'hwp_previews_settings';

	/**
	 * Settings arguments key used for setting query var when loading the template file.
	 *
	 * @var string
	 */
	public const SETTINGS_ARGS = 'hwp_previews_main_page_config';

	/**
	 * Settings field.
	 *
	 * @var string
	 */
	public const ENABLED_FIELD = 'enabled';

	/**
	 * Settings field.
	 *
	 * @var string
	 */
	public const UNIQUE_POST_SLUGS_FIELD = 'unique_post_slugs';

	/**
	 * Settings field.
	 *
	 * @var string
	 */
	public const POST_STATUSES_AS_PARENT_FIELD = 'post_statuses_as_parent';

	/**
	 * Settings field.
	 *
	 * @var string
	 */
	public const PREVIEW_URL_FIELD = 'preview_url';

	/**
	 * Settings field.
	 *
	 * @var string
	 */
	public const IN_IFRAME_FIELD = 'in_iframe';

	/**
	 * Settings fields and their types.
	 *
	 * @var array<string, string>
	 */
	public const SETTINGS_FIELDS = [
		self::ENABLED_FIELD                 => 'bool',
		self::UNIQUE_POST_SLUGS_FIELD       => 'bool',
		self::POST_STATUSES_AS_PARENT_FIELD => 'bool',
		self::PREVIEW_URL_FIELD             => 'string',
		self::IN_IFRAME_FIELD               => 'bool',
	];

	/**
	 * Settings object used for value retrieving.
	 *
	 * @var \HWP\Previews\Settings\Preview_Settings
	 */
	private Preview_Settings $settings;

	/**
	 * Post types configuration.
	 *
	 * @var \HWP\Previews\Post\Type\Contracts\Post_Types_Config_Interface
	 */
	private Post_Types_Config_Interface $types_config;

	/**
	 * Post statuses configuration.
	 *
	 * @var \HWP\Previews\Post\Status\Contracts\Post_Statuses_Config_Interface
	 */
	private Post_Statuses_Config_Interface $statuses_config;

	/**
	 * Preview parameter registry.
	 *
	 * @var \HWP\Previews\Preview\Parameter\Preview_Parameter_Registry
	 */
	private Preview_Parameter_Registry $parameters;

	/**
	 * Preview link service class that handles the generation of preview links.
	 *
	 * @var \HWP\Previews\Preview\Link\Preview_Link_Service
	 */
	private Preview_Link_Service $link_service;

	/**
	 * The version of the plugin.
	 *
	 * @var string
	 */
	private string $version;

	/**
	 * The directory path of the plugin.
	 *
	 * @var string
	 */
	private string $dir_path;

	/**
	 * The URL of the plugin.
	 *
	 * @var string
	 */
	private string $plugin_url;

	/**
	 * The instance of the plugin.
	 *
	 * @var \HWP\Previews\Plugin|null
	 */
	private static ?Plugin $instance = null;

	/**
	 * Constructor.
	 *
	 * @param string $version The version of the plugin.
	 * @param string $dir_path The directory path of the plugin.
	 * @param string $plugin_url The URL of the plugin.
	 */
	private function __construct( string $version, string $dir_path, string $plugin_url ) {
		$this->version    = $version;
		$this->dir_path   = $dir_path;
		$this->plugin_url = $plugin_url;

		// Initialize the settings object with a cache group.
		$this->settings = new Preview_Settings(
			new Settings_Cache_Group( self::SETTINGS_KEY, self::SETTINGS_GROUP, self::SETTINGS_FIELDS )
		);

		// Initialize the post types and statuses configurations.
		$this->types_config = ( new Post_Types_Config() )->set_post_types( $this->settings->post_types_enabled() );
		$this->statuses_config = ( new Post_Statuses_Config() )->set_post_statuses( [
			'publish',
			'future',
			'draft',
			'pending',
			'private',
			'auto-draft'
		] );

		// Initialize the preview parameter registry.
		$this->parameters = new Preview_Parameter_Registry();

		// Initialize the preview link service.
		$this->link_service = new Preview_Link_Service(
			$this->types_config,
			$this->statuses_config,
			new Preview_Link_Placeholder_Resolver( $this->parameters )
		);
	}

	/**
	 * Get the instance of this class. Passes the version, directory path, and plugin URL to the constructor.
	 *
	 * @param string $version The version of the plugin.
	 * @param string $dir_path The directory path of the plugin.
	 * @param string $plugin_url The URL of the plugin.
	 *
	 * @return \HWP\Previews\Plugin
	 */
	public static function get_instance( string $version, string $dir_path, string $plugin_url ): Plugin {
		if ( self::$instance === null ) {
			self::$instance = new self( $version, $dir_path, $plugin_url );
		}

		return self::$instance;
	}

	/**
	 * Initialize the plugin functionality.
	 */
	public function init(): void {
		// Init core functionality.
		$this->init_core_functionality();

		// Settings.
		$this->register_settings_pages();
		$this->register_settings_fields();

		// JS.
		$this->enqueue_plugin_js();

		// Functionality.
		$this->enable_unique_post_slug();
		$this->enable_post_statuses_as_parent();
		$this->enable_preview_in_iframe();
		$this->enable_preview_functionality();
	}

	/**
	 * Enqueues the JavaScript file for the plugin admin area.
	 *
	 * @return void
	 */
	public function enqueue_plugin_js(): void {
		add_action( 'admin_enqueue_scripts', function ( string $hook ) {
			if ( $hook !== 'toplevel_page_' . self::PLUGIN_MENU_SLUG ) {
				return;
			}

			wp_enqueue_script(
				self::PLUGIN_JS_HANDLE,
				trailingslashit( $this->plugin_url ) . self::PLUGIN_JS_SRC,
				[],
				$this->version,
				true
			);
		} );
	}

	/**
	 * Enable unique post slugs for post statuses specified in the post statuses config.
	 *
	 * @return void
	 */
	public function enable_unique_post_slug(): void {
		$post_slug_manger = new Post_Slug_Manager(
			$this->types_config,
			$this->statuses_config,
			new Post_Slug_Repository()
		);

		add_filter( 'wp_insert_post_data', function ( $data, $postarr ) use ( $post_slug_manger ) {
			$post = new WP_Post( new Post_Data_Model( $data, (int) ( $postarr['ID'] ?? 0 ) ) );

			// Check if the correspondent setting is enabled.
			if ( ! $this->settings->unique_post_slugs( $post->post_type ) ) {
				return $data;
			}

			$post_slug = $post_slug_manger->force_unique_post_slug( $post );

			if ( ! empty( $post_slug ) ) {
				$data['post_name'] = $post_slug;
			}

			return $data;
		}, 10, 2 );
	}

	/**
	 * Replace the preview link in the REST response.
	 *
	 * @param \WP_REST_Response $response The REST response object.
	 * @param \WP_Post          $post    The post object.
	 *
	 * @return \WP_REST_Response
	 */
	public function filter_rest_prepare_link( WP_REST_Response $response, WP_Post $post ): WP_REST_Response {
		if ( $this->settings->in_iframe( $post->post_type ) ) {
			return $response;
		}

		$preview_url = $this->generate_preview_url( $post );
		if ( ! empty( $preview_url ) ) {
			$response->data['link'] = $preview_url;
		}

		return $response;
	}

	/**
	 * Setups default preview parameters on the 'init' hook.
	 * Creates custom action hook 'hwp_previews_core'.
	 *
	 * @return void
	 */
	private function init_core_functionality(): void {
		add_action( 'init', function (): void {

			// Register default preview parameters.
			$this->setup_default_preview_parameters();


			/**
			 * Allows access to the parameters registry, types config, statuses config.
			 */
			do_action( 'hwp_previews_core', $this->parameters, $this->types_config, $this->statuses_config );
		}, 5, 0 );
	}

	/**
	 * Registers default preview parameters on the init hook.
	 * Uses 'hwp_previews_parameters_registry' action to allow modification of the parameters registry.
	 *
	 * @return void
	 */
	private function setup_default_preview_parameters(): void {
		$this->parameters
			->register(
				new Preview_Parameter( 'ID', static fn( WP_Post $post ) => (string) $post->ID, __( 'Post ID.', 'hwp-previews' ) )
			)->register(
				new Preview_Parameter( 'author_ID', static fn( WP_Post $post ) => $post->post_author, __( 'ID of post author..', 'hwp-previews' ) )
			)->register(
				new Preview_Parameter( 'status', static fn( WP_Post $post ) => $post->post_status, __( 'The post\'s status..', 'hwp-previews' ) )
			)->register(
				new Preview_Parameter( 'slug', static fn( WP_Post $post ) => $post->post_name, __( 'The post\'s slug.', 'hwp-previews' ) )
			)->register(
				new Preview_Parameter( 'parent_ID', static fn( WP_Post $post ) => (string) $post->post_parent, __( 'ID of a post\'s parent post.', 'hwp-previews' ) )
			)->register(
				new Preview_Parameter( 'type', static fn( WP_Post $post ) => $post->post_type, __( 'The post\'s type, like post or page.', 'hwp-previews' ) )
			)->register(
				new Preview_Parameter( 'uri', static fn( WP_Post $post ) => (string) get_page_uri( $post ), __( 'The URI path for a page.', 'hwp-previews' ) )
			)->register(
				new Preview_Parameter( 'template', static fn( WP_Post $post ) => (string) get_page_template_slug( $post ), __( 'Specific template filename for a given post.', 'hwp-previews' ) )
			);
	}

	/**
	 * Registers settings pages and subpages.
	 *
	 * @return void
	 */
	private function register_settings_pages(): void {
		add_action( 'admin_menu', function (): void {
			/**
			 * Array of post types where key is the post type slug and value is the label.
			 *
			 * @var array<string, string> $post_types
			 */
			$post_types = apply_filters( 'hwp_previews_filter_post_type_setting', $this->types_config->get_public_post_types() );

			$this->create_settings_page( $post_types )->register_page();
			$this->create_settings_subpage()->register_page();
		} );
	}

	/**
	 * Registers settings fields.
	 *
	 * @return void
	 */
	private function register_settings_fields(): void {
		add_action( 'admin_init', function (): void {

			/**
			 * Array of post types where key is the post type slug and value is the label.
			 *
			 * @var array<string, string> $post_types
			 */
			$post_types = apply_filters( 'hwp_previews_filter_post_type_setting', $this->types_config->get_public_post_types() );

			/**
			 * Register setting itself.
			 */
			$this->create_tabbed_settings( $post_types )->register_settings();

			/**
			 * Register settings sections and fields for each post type.
			 */
			foreach ( $post_types as $post_type => $label ) {
				$this->create_setting_section( $post_type, $label )->register_section( self::SETTINGS_KEY, $post_type, "hwp-previews-{$post_type}" );
			}
		}, 10, 0 );
	}

	/**
	 * Enable post statuses specified in the post statuses config as parent for the post types specified in the post types config.
	 *
	 * @return void
	 */
	private function enable_post_statuses_as_parent(): void {
		$post_parent_manager = new Post_Parent_Manager( $this->types_config, $this->statuses_config );

		$post_parent_manager_callback = function ( array $args ) use ( $post_parent_manager ): array {
			if ( empty( $args['post_type'] ) ) {
				return $args;
			}

			// Check if the correspondent setting is enabled.
			if ( ! $this->settings->post_statuses_as_parent( (string) $args['post_type'] ) ) {
				return $args;
			}

			$post_type = get_post_type_object( (string) $args['post_type'] );
			if ( $post_type instanceof WP_Post_Type ) {
				$args['post_status'] = $post_parent_manager->get_post_statuses_as_parent( $post_type );
			}

			return $args;
		};

		add_filter( 'page_attributes_dropdown_pages_args', $post_parent_manager_callback );
		add_filter( 'quick_edit_dropdown_pages_args', $post_parent_manager_callback );

		// And for Gutenberg.
		foreach ( $this->types_config->get_post_types() as $post_type ) {
			$post_type_object = get_post_type_object( $post_type );
			if ( ! $post_type_object instanceof WP_Post_Type || ! $this->types_config->supports_gutenberg( $post_type_object ) ) {
				continue;
			}
			add_filter( 'rest_' . $post_type . '_query', $post_parent_manager_callback );
		}
	}

	/**
	 * Enable preview functionality in iframe.
	 *
	 * @return void
	 */
	private function enable_preview_in_iframe(): void {
		$template_resolver = new Preview_Template_Resolver( $this->types_config, $this->statuses_config );

		add_filter( 'template_include', function ( $template ) use ( $template_resolver ) {
			if ( ! is_preview() ) {
				return $template;
			}

			$post = get_post();
			if ( ! $post instanceof WP_Post ) {
				return $template;
			}

			// Check if the correspondent setting is enabled.
			if ( ! $this->settings->in_iframe( $post->post_type ) ) {
				return $template;
			}

			/**
			 * The filter 'hwp_previews_template_path' allows to change the template directory path.
			 */
			$template_dir_path = (string) apply_filters(
				'hwp_previews_template_path',
				$this->dir_path . 'templates/hwp-preview.php'
			);

			$preview_template = $template_resolver->resolve_template_path( $post, $template_dir_path );

			if ( empty( $preview_template ) ) {
				return $template;
			}

			set_query_var( $template_resolver::HWP_PREVIEWS_IFRAME_PREVIEW_URL, $this->generate_preview_url( $post ) );

			return $preview_template;
		}, 999 );
	}

	/**
	 * Swaps the preview link for the post types specified in the post types config.
	 * Is being enabled only if the preview is not in iframe. Otherwise preview functionality is resolved on the template redirect level.
	 *
	 * @return void
	 */
	private function enable_preview_functionality(): void {
		add_filter( 'preview_post_link', function ( $link, $post ) {
			// If iframe option is enabled, we need to resolve preview on the template redirect level.
			if ( $this->settings->in_iframe( $post->post_type ) ) {
				return $link;
			}

			$url = $this->generate_preview_url( $post );

			return ! empty( $url ) ? $url : $link;
		}, 10, 2 );

		/**
		 * Hack Function that changes the preview link for draft articles,
		 * this must be removed when properly fixed https://github.com/WordPress/gutenberg/issues/13998.
		 */
		foreach ( $this->types_config->get_public_post_types() as $key => $label ) {
			add_filter( 'rest_prepare_' . $key, [ $this, 'filter_rest_prepare_link' ], 10, 2 );
		}
	}

	/**
	 * Generates the preview URL for the given post based on the preview URL template provided in settings.
	 *
	 * @param \WP_Post $post The post object.
	 *
	 * @return string The generated preview URL.
	 */
	private function generate_preview_url( WP_Post $post ): string {
		$url = $this->settings->url_template( $post->post_type );

		if ( empty( $url ) ) {
			return '';
		}

		return (new Preview_Link_Service(
			$this->types_config,
			$this->statuses_config,
			new Preview_Link_Placeholder_Resolver( $this->parameters )
		))->generate_preview_post_link( $url, $post );
	}

	/**
	 * Creates the settings page.
	 *
	 * @param array<string> $post_types The post types to be used in the settings page.
	 *
	 * @return \HWP\Previews\Settings\Menu\Menu_Page
	 */
	private function create_settings_page( array $post_types ): Menu_Page {
		return new Menu_Page(
			__( 'HWP Previews Settings', 'hwp-previews' ),
			'HWP Previews',
			self::PLUGIN_MENU_SLUG,
			$this->dir_path . 'templates/admin/settings-page-main.php',
			[
				self::SETTINGS_ARGS => [
					'tabs'        => $post_types,
					'current_tab' => $this->get_current_tab( $post_types ),
					'params'      => $this->parameters->get_descriptions(),
				],
			],
			'dashicons-welcome-view-site'
		);
	}

	/**
	 * Get the current tab for the settings page.
	 *
	 * @param array<string> $post_types The post types to be used in the settings page.
	 * @param string        $tab The name of the tab.
	 *
	 * @return string
	 */
	private function get_current_tab( $post_types, string $tab = 'tab' ): string {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET[ $tab ] ) && is_string( $_GET[ $tab ] ) ) {
			return sanitize_key( $_GET[ $tab ] );
		}

		return ! empty( $post_types ) ? (string) key( $post_types ) : '';
	}

	/**
	 * Creates the settings subpage.
	 *
	 * @return \HWP\Previews\Settings\Menu\Submenu_Page
	 */
	private function create_settings_subpage(): Submenu_Page {
		return new Submenu_Page(
			self::PLUGIN_MENU_SLUG,
			__( 'Testing Tool', 'hwp-previews' ),
			'Testing Tool',
			'hwp-previews-testing-tool',
			$this->dir_path . 'templates/admin/settings-page-testing-tool.php'
		);
	}

	/**
	 * Creates the tabbed settings object.
	 *
	 * @param array<string> $post_types Post Types as a tabs.
	 *
	 * @return \HWP\Previews\Settings\Tabbed_Settings
	 */
	private function create_tabbed_settings( array $post_types ): Tabbed_Settings {
		return new Tabbed_Settings(
			self::SETTINGS_GROUP,
			self::SETTINGS_KEY,
			array_keys( $post_types ),
			self::SETTINGS_FIELDS
		);
	}

	/**
	 * Creates the settings section for a specific post type.
	 *
	 * @param string $post_type The post type slug.
	 * @param string $label     The label for the post type.
	 *
	 * @return \HWP\Previews\Settings\Settings_Section
	 */
	private function create_setting_section( string $post_type, string $label ): Settings_Section {
		return new Settings_Section(
			'hwp_previews_section_' . $post_type,
			'',
			'hwp-previews-' . $post_type,
			$this->create_settings_fields( $post_type, $label, is_post_type_hierarchical( $post_type ) )
		);
	}

	/**
	 * Creates the settings fields for a specific post type.
	 *
	 * @param string $post_type The post type slug.
	 * @param string $label    The label for the post type.
	 * @param bool   $is_hierarchical Whether the post type is hierarchical.
	 *
	 * @return array<\HWP\Previews\Settings\Fields\Abstract_Settings_Field>
	 */
	private function create_settings_fields( string $post_type, string $label, bool $is_hierarchical ): array {
		$fields = [];

		$fields[] = new Checkbox_Field(
			'enabled',
			// translators: %s is the label of the post type.
			sprintf( __( 'Enable HWP Previews for %s', 'hwp-previews' ), $label )
		);
		$fields[] = new Checkbox_Field( 'unique_post_slugs', __( 'Enable unique post slugs for all post statuses', 'hwp-previews' ) );

		if ( $is_hierarchical ) {
			$fields[] = new Checkbox_Field( 'post_statuses_as_parent', __( 'Allow all post statuses in parents option', 'hwp-previews' ) );
		}

		$fields[] = new Checkbox_Field( 'in_iframe', sprintf( __( 'Load previews in iframe', 'hwp-previews' ), $label ) );
		$fields[] = new Text_Input_Field(
			'preview_url',
			// translators: %s is the label of the post type.
			sprintf( __( 'Preview URL for %s', 'hwp-previews' ), $label ),
			"https://example.com/{$post_type}?preview=true&post_id={ID}&name={slug}",
			'large-text code hwp-previews-url' // The class is being used as a query for the JS.
		);

		return $fields;
	}

}
