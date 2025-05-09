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
use HWP\Previews\Shared\Helpers;
use WP_Post;
use WP_Post_Type;

/**
 * Plugin class for HWP Previews.
 *
 * This class serves as the main entry point for the plugin, handling initialization, action and filter hooks.
 *
 * @package HWP\Previews
 */
class Plugin {

	/**
	 * @var string
	 */
	public const SETTINGS_GROUP = 'hwp_previews_settings_group';

	/**
	 * @var string
	 */
	public const SETTINGS_KEY = 'hwp_previews_settings';

	/**
	 * @var string
	 */
	public const SETTINGS_ARGS = 'hwp_previews_main_page_config';

	/**
	 * @var string
	 */
	public const ENABLED_FIELD = 'enabled';

	/**
	 * @var string
	 */
	public const UNIQUE_POST_SLUGS_FIELD = 'unique_post_slugs';

	/**
	 * @var string
	 */
	public const POST_STATUSES_AS_PARENT_FIELD = 'post_statuses_as_parent';

	/**
	 * @var string
	 */
	public const PREVIEW_URL_FIELD = 'preview_url';

	/**
	 * @var string
	 */
	public const IN_IFRAME_FIELD = 'in_iframe';

	/**
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
	 * @var \HWP\Previews\Settings\Preview_Settings
	 */
	private Preview_Settings $settings;

	private Post_Types_Config_Interface $types_config;
	private Post_Statuses_Config_Interface $statuses_config;
	private Preview_Parameter_Registry $parameters;

	/**
	 * @var \HWP\Previews\Preview\Link\Preview_Link_Service
	 */
	private Preview_Link_Service $link_service;
	private string $version; // Todo: use when enqueuing scripts.
	private string $dir_path;
	private static ?Plugin $instance = null;

	/**
	 * Constructor.
	 */
	private function __construct() {
		$this->settings = new Preview_Settings(
			new Settings_Cache_Group( self::SETTINGS_KEY, self::SETTINGS_GROUP, self::SETTINGS_FIELDS )
		);

		$this->types_config    = new Post_Types_Config();
		$this->statuses_config = new Post_Statuses_Config();
		$this->parameters      = new Preview_Parameter_Registry();

		$this->link_service = new Preview_Link_Service(
			$this->types_config,
			$this->statuses_config,
			new Preview_Link_Placeholder_Resolver( $this->parameters )
		);
	}

	/**
	 * Get the instance of this class.
	 */
	public static function get_instance(): Plugin {
		if ( self::$instance === null ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function init( string $version, string $dir_path ): void {
		$this->version  = $version;
		$this->dir_path = $dir_path;

		// Init core functionality.
		$this->init_core_functionality();

		// Settings.
		$this->register_settings_pages();
		$this->register_settings_fields();

		// Functionality.
		$this->enable_unique_post_slug();
		$this->enable_post_statuses_as_parent();
		$this->enable_preview_in_iframe();
		$this->enable_preview_functionality();
	}

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

	private function init_core_functionality(): void {
		add_action( 'init', function (): void {

			// Register default preview parameters.
			$this->setup_default_preview_parameters();

			// Setup post types configuration.
			$this->types_config->set_post_types(
				$this->settings->post_types_enabled()
			);

			// Setup post statuses configuration.
			$this->statuses_config->set_post_statuses(
				[ 'publish', 'future', 'draft', 'pending', 'private' ]
			);

			/**
			 * Allows modify parameters registry.
			 */
			do_action( 'hwp_previews_parameters_registry', $this->parameters );
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

	private function register_settings_pages(): void {
		add_action( 'admin_menu', function (): void {
			/**
			 * @var array<string, string> $post_types Array of post types where key is the post type slug and value is the label.
			 */
			$post_types = apply_filters( 'hwp_previews_filter_post_type_setting', Helpers::get_public_post_types() );

			$this->create_settings_page( $post_types )->register_page();
			$this->create_settings_subpage()->register_page();
		} );
	}

	private function register_settings_fields(): void {
		add_action( 'admin_init', function (): void {

			/**
			 * @var array<string, string> $post_types Array of post types where key is the post type slug and value is the label.
			 */
			$post_types = apply_filters( 'hwp_previews_filter_post_type_setting', Helpers::get_public_post_types() );

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
		} );
	}

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
			 * The filter 'hwp_previews_template_dir_path' allows to change the template directory path.
			 */
			$template_dir_path = (string) apply_filters(
				'hwp_previews_template_dir_path',
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

	private function enable_preview_functionality(): void {
		add_filter( 'preview_post_link', function ( $link, $post ) {
			// If iframe option is enabled, we need to resolve preview on the template redirect level.
			if ( $this->settings->in_iframe( $post->post_type ) ) {
				return $link;
			}

			return $this->generate_preview_url( $post ) ?: $link;
		}, 10, 2 );

		/**
		 * Hack Function that changes the preview link for draft articles,
		 * this must be removed when properly fixed https://github.com/WordPress/gutenberg/issues/13998.
		 */
		foreach ( $this->types_config->get_post_types() as $post_type ) {
			add_filter( 'rest_prepare_' . $post_type, function ( $response, $post ) {
				// If iframe option is enabled, we need to resolve preview on the template redirect level.
				if ( $this->settings->in_iframe( $post->post_type ) ) {
					return $response;
				}

				$preview_url = $this->generate_preview_url( $post );
				if ( $preview_url ) {
					$response->data['link'] = $preview_url;
				}

				return $response;
			}, 10, 2 );
		}
	}

	private function generate_preview_url( WP_Post $post ): string {
		$url = $this->settings->url_template( $post->post_type );

		if ( ! empty( $url ) ) {
			return $this->link_service->generate_preview_post_link( $url, $post );
		}

		return '';
	}

	/**
	 * @param array<string> $post_types
	 *
	 * @return \HWP\Previews\Settings\Menu\Menu_Page
	 */
	private function create_settings_page( array $post_types ): Menu_Page {
		return new Menu_Page(
			__( 'HWP Previews Settings', 'hwp-previews' ),
			'HWP Previews',
			'hwp-previews',
			$this->dir_path . 'templates/admin/settings-page-main.php',
			[
				self::SETTINGS_ARGS => [
					'tabs'        => $post_types,
					'current_tab' => isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : key( $post_types ),
					'params'      => $this->parameters->get_descriptions(),
				],
			],
			'dashicons-welcome-view-site'
		);
	}

	private function create_settings_subpage(): Submenu_Page {
		return new Submenu_Page(
			'hwp-previews',
			__( 'Testing Tool', 'hwp-previews' ),
			'Testing Tool',
			'hwp-previews-testing-tool',
			$this->dir_path . 'templates/admin/settings-page-testing-tool.php'
		);
	}

	/**
	 * @param array<string> $post_types
	 *
	 * @return \HWP\Previews\Settings\Tabbed_Settings
	 */
	private function create_tabbed_settings( array $post_types ): Tabbed_Settings {
		return new Tabbed_Settings(
			self::SETTINGS_GROUP,
			self::SETTINGS_KEY,
			array_keys( $post_types ), // Items allowed, represented as a tabs.
			self::SETTINGS_FIELDS
		);
	}

	private function create_setting_section( string $post_type, string $label ): Settings_Section {
		return new Settings_Section(
			'hwp_previews_section_' . $post_type,
			'',
			'hwp-previews-' . $post_type,
			$this->create_settings_fields( $post_type, $label, is_post_type_hierarchical( $post_type ) )
		);
	}

	/**
	 * @param string $post_type
	 * @param string $label
	 * @param bool   $is_hierarchical
	 *
	 * @return array<\HWP\Previews\Settings\Fields\Abstract_Settings_Field>
	 */
	private function create_settings_fields( string $post_type, string $label, bool $is_hierarchical ): array {
		$fields = [];

		foreach (
			[
				'enabled'                 => sprintf( __( 'Enable HWP Previews for %s', 'hwp-previews' ), $label ),
				'unique_post_slugs'       => __( 'Enable unique post slugs for all post statuses', 'hwp-previews' ),
				'post_statuses_as_parent' => __( 'Allow all post statuses in parents option', 'hwp-previews' ),
				'in_iframe'               => sprintf( __( 'Load previews in iframe', 'hwp-previews' ), $label ),
			] as $id => $description
		) {
			$fields[ $id ] = new Checkbox_Field( $id, $description );
		}

		// Remove the 'post_statuses_as_parent' field if the post type is not hierarchical.
		if ( ! $is_hierarchical ) {
			unset( $fields['post_statuses_as_parent'] );
		}

		// Preview URL field.
		$fields[] = new Text_Input_Field(
			'preview_url',
			sprintf( __( 'Preview URL for %s', 'hwp-previews' ), $label ),
			"https://example.com/{$post_type}?preview=true&post_id={ID}&name={slug}",
			'large-text code hwp-previews-url' // The class is being used as a query for the JS.
		);

		return $fields;
	}

}
