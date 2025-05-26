<?php

declare(strict_types=1);

namespace HWP\Previews;

use HWP\Previews\Admin\Settings;
use HWP\Previews\Admin\Settings\Preview_Settings;
use HWP\Previews\Admin\Settings\Settings_Cache_Group;
use HWP\Previews\Post\Data\Post_Data_Model;
use HWP\Previews\Post\Parent\Post_Parent_Manager;
use HWP\Previews\Post\Slug\Post_Slug_Manager;
use HWP\Previews\Post\Slug\Post_Slug_Repository;
use HWP\Previews\Post\Status\Contracts\Post_Statuses_Config_Interface;
use HWP\Previews\Post\Status\Post_Statuses_Config;
use HWP\Previews\Post\Type\Contracts\Post_Types_Config_Interface;
use HWP\Previews\Post\Type\Post_Type_Inspector;
use HWP\Previews\Post\Type\Post_Types_Config;
use HWP\Previews\Preview\Link\Preview_Link_Placeholder_Resolver;
use HWP\Previews\Preview\Link\Preview_Link_Service;
use HWP\Previews\Preview\Parameter\Preview_Parameter_Registry;
use HWP\Previews\Preview\Template\Preview_Template_Resolver;
use WP_Post;
use WP_REST_Response;


if ( ! class_exists( 'HWP\Previews\Plugin' ) ) :

/**
 * Plugin class for HWP Previews.
 *
 * This class serves as the main entry point for the plugin, handling initialization, action and filter hooks.
 *
 * @package HWP\Previews
 */
final class Plugin {


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
	 * // @TODO - Remove
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
	 * @TODO get rid of
	 *
	 * Post statuses that are applicable for previews.
	 *
	 * @var array<string>
	 */
	public const POST_STATUSES = [
		'publish',
		'future',
		'draft',
		'pending',
		'private',
		'auto-draft',
	];

	/**
	 * Settings object used for value retrieving.
	 *
	 * @var \HWP\Previews\Admin\Settings\Preview_Settings
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
	 * Preview link service class that handles the generation of preview links.
	 *
	 * @var \HWP\Previews\Preview\Link\Preview_Link_Service
	 */
	private Preview_Link_Service $link_service;


	/**
	 * The instance of the plugin.
	 *
	 * @var \HWP\Previews\Plugin|null
	 */
	private static ?Plugin $instance = null;

	/**
	 * Constructor
	 */
	public static function instance(): self {
		if ( ! isset( self::$instance ) || ! ( is_a( self::$instance, self::class ) ) ) {
			self::$instance = new self();
			self::$instance->setup();
		}

		/**
		 * Fire off init action.
		 *
		 * @param self $instance the instance of the plugin class.
		 */
		do_action( 'hwp_previews_init', self::$instance );

		return self::$instance;
	}

	/**
	 * Initialize the plugin functionality.
	 */
	public function setup(): void {

		// @TODO Refactor
		// Move to Hooks class
		// Create
			// parameters
			// types
			// statuses
			// registry



		// @TODO Remove
		// Initialize the settings object with a cache group.
		$this->settings = new Preview_Settings(
			new Settings_Cache_Group( HWP_PREVIEWS_SETTINGS_KEY, HWP_PREVIEWS_SETTINGS_GROUP, self::SETTINGS_FIELDS )
		);

		// Initialize the post types and statuses configurations.
		$this->types_config    = ( new Post_Types_Config( new Post_Type_Inspector() ) )->set_post_types( $this->settings->post_types_enabled() );
		$this->statuses_config = ( new Post_Statuses_Config() )->set_post_statuses( self::POST_STATUSES );


		// Initialize the preview link service.
		$this->link_service = new Preview_Link_Service(
			$this->types_config,
			$this->statuses_config,
			new Preview_Link_Placeholder_Resolver(Preview_Parameter_Registry::get_instance())
		);


		// Settings.
		Settings::init($this->types_config);

		// Functionality.
		$this->enable_unique_post_slug();
		$this->enable_post_statuses_as_parent();
		$this->enable_preview_in_iframe();
		$this->enable_preview_functionality();
	}

	/**
	 * Enable unique post slugs for post statuses specified in the post statuses config.
	 */
	public function enable_unique_post_slug(): void {
		// @TODO Move its own class for actions and filters
		add_filter( 'wp_insert_post_data', function ( $data, $postarr ) {
			$post = new WP_Post( new Post_Data_Model( $data, (int) ( $postarr['ID'] ?? 0 ) ) );

			// Check if the correspondent setting is enabled.
			if ( ! $this->settings->unique_post_slugs( $post->post_type ) ) {
				return $data;
			}

			$post_slug = ( new Post_Slug_Manager(
				$this->types_config,
				$this->statuses_config,
				new Post_Slug_Repository()
			) )->force_unique_post_slug( $post );

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
	 */
	public function filter_rest_prepare_link( WP_REST_Response $response, WP_Post $post ): WP_REST_Response {
		// @TODO Move its own class for actions and filters
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
	 */
	private function init_core_functionality(): void {
		add_action( 'init', function (): void {


			// @TODO - Add back in.

			/**
			 * Allows access to the parameters registry, types config, statuses config.
			 */
//			do_action( 'hwp_previews_core', $this->parameters, $this->types_config, $this->statuses_config );
		}, 5, 0 );
	}


	/**
	 * Enable post statuses specified in the post statuses config as parent for the post types specified in the post types config.
	 */
	private function enable_post_statuses_as_parent(): void {
		$post_parent_manager = new Post_Parent_Manager( $this->types_config, $this->statuses_config );

		$post_parent_manager_callback = function ( array $args ) use ( $post_parent_manager ): array {
			if ( empty( $args['post_type'] ) ) {
				return $args;
			}

			$post_type = (string) $args['post_type'];

			// Check if the correspondent setting is enabled.
			if ( ! $this->settings->post_statuses_as_parent( $post_type ) ) {
				return $args;
			}

			$post_statuses = $post_parent_manager->get_post_statuses_as_parent( $post_type );
			if ( ! empty( $post_statuses ) ) {
				$args['post_status'] = $post_statuses;
			}

			return $args;
		};

		add_filter( 'page_attributes_dropdown_pages_args', $post_parent_manager_callback );
		add_filter( 'quick_edit_dropdown_pages_args', $post_parent_manager_callback );

		// And for Gutenberg.
		foreach ( $this->types_config->get_post_types() as $post_type ) {
			if ( ! $this->types_config->gutenberg_editor_enabled( $post_type ) ) {
				continue;
			}
			add_filter( 'rest_' . $post_type . '_query', $post_parent_manager_callback );
		}
	}

	/**
	 * Enable preview functionality in iframe.
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
				HWP_PREVIEWS_PLUGIN_DIR . 'templates/hwp-preview.php'
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
		// Check if the correspondent setting is enabled.
		$url = $this->settings->url_template( $post->post_type );

		if ( empty( $url ) ) {
			return '';
		}

		return $this->link_service->generate_preview_post_link( $url, $post );
	}

	/**
	 * Throw error on object clone.
	 * The whole idea of the singleton design pattern is that there is a single object
	 * therefore, we don't want the object to be cloned.
	 *
	 * @codeCoverageIgnore
	 *
	 * @return void
	 */
	public function __clone() {
		// Cloning instances of the class is forbidden.
		_doing_it_wrong( __FUNCTION__, esc_html__( 'The plugin Plugin class should not be cloned.', 'hwp-previews' ), HWP_PREVIEWS_VERSION );
	}

	/**
	 * Disable unserializing of the class.
	 *
	 * @codeCoverageIgnore
	 */
	public function __wakeup(): void {
		// De-serializing instances of the class is forbidden.
		_doing_it_wrong( __FUNCTION__, esc_html__( 'De-serializing instances of the plugin Main class is not allowed.', 'hwp-previews' ),  HWP_PREVIEWS_VERSION);
	}
}
endif;
