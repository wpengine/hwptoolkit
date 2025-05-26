<?php

namespace HWP\Previews\Hooks;

use HWP\Previews\Admin\Settings\Helper\Settings_Helper;
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

class Preview_Hooks {

	/**
	 * Settings helper instance that provides access to plugin settings.
	 *
	 * @var Settings_Helper|null
	 */
	protected static ?Settings_Helper $settings_helper = null;


	/**
	 * Post types configuration.
	 *
	 * @var Post_Types_Config_Interface|null
	 */
	protected static ?Post_Types_Config_Interface $types_config = null;

	/**
	 * Post statuses configuration.
	 *
	 * @var Post_Statuses_Config_Interface|null
	 */
	protected static ?Post_Statuses_Config_Interface $statuses_config = null;


	/**
	 * Preview link service class that handles the generation of preview links.
	 *
	 * @var Preview_Link_Service|null
	 */
	protected static ?Preview_Link_Service $link_service = null;

	/**
	 * Initialize the hooks for the preview functionality.
	 */
	public static function init(): void {

		// @TODO - Extract out actions/filters to a method to make it more readable.
		self::init_class_properties();
		self::enable_unique_post_slug();
		self::enable_post_statuses_as_parent();
		self::enable_preview_in_iframe();
		self::enable_preview_functionality();
	}


	public static function init_class_properties(): void {

		// @TODO - Add more filters

		self::$settings_helper = Settings_Helper::get_instance();

		// @TODO - Make easier and remove duplication
		self::$types_config = ( new Post_Types_Config( new Post_Type_Inspector() ) )->set_post_types( self::$settings_helper->post_types_enabled() );

		// Initialize the post types and statuses configurations.
		self::$statuses_config = ( new Post_Statuses_Config() )->set_post_statuses( self::get_post_statuses() );


		// Initialize the preview link service.
		self::$link_service = new Preview_Link_Service(
			self::$types_config,
			self::$statuses_config,
			new Preview_Link_Placeholder_Resolver( Preview_Parameter_Registry::get_instance() )
		);
	}

	public static function get_post_statuses(): array {
		$post_statuses = [
			'publish',
			'future',
			'draft',
			'pending',
			'private',
			'auto-draft'
		];

		return apply_filters( 'hwp_previews_post_statuses', $post_statuses );
	}

	/**
	 * Enable unique post slugs for post statuses specified in the post statuses config.
	 */
	public static function enable_unique_post_slug(): void {
		// @TODO Move its own class for actions and filters
		add_filter( 'wp_insert_post_data', function ( $data, $postarr ) {
			$post = new WP_Post( new Post_Data_Model( $data, (int) ( $postarr['ID'] ?? 0 ) ) );

			// Check if the correspondent setting is enabled.
			if ( ! self::$settings_helper->unique_post_slugs( $post->post_type ) ) {
				return $data;
			}

			$post_slug = ( new Post_Slug_Manager(
				self::$types_config,
				self::$statuses_config,
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
	 * @param \WP_Post $post The post object.
	 */
	public static function filter_rest_prepare_link( WP_REST_Response $response, WP_Post $post ): WP_REST_Response {
		if ( self::$settings_helper->in_iframe( $post->post_type ) ) {
			return $response;
		}

		$preview_url = self::generate_preview_url( $post );
		if ( ! empty( $preview_url ) ) {
			$response->data['link'] = $preview_url;
		}

		return $response;
	}

	/**
	 * Enable post-statuses specified in the post-statuses config as parent for the post types specified in the post-types config.
	 */
	public static function enable_post_statuses_as_parent(): void {
		$post_parent_manager = new Post_Parent_Manager( self::$types_config, self::$statuses_config );

		$post_parent_manager_callback = function ( array $args ) use ( $post_parent_manager ): array {
			if ( empty( $args['post_type'] ) ) {
				return $args;
			}

			$post_type = (string) $args['post_type'];

			// Check if the correspondent setting is enabled.
			if ( ! self::$settings_helper->post_statuses_as_parent( $post_type ) ) {
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
		foreach ( self::$types_config->get_post_types() as $post_type ) {
			if ( ! self::$types_config->gutenberg_editor_enabled( $post_type ) ) {
				continue;
			}
			add_filter( 'rest_' . $post_type . '_query', $post_parent_manager_callback );
		}
	}

	/**
	 * Enable preview functionality in iframe.
	 */
	public static function enable_preview_in_iframe(): void {
		$template_resolver = new Preview_Template_Resolver( self::$types_config, self::$statuses_config );

		add_filter( 'template_include', function ( $template ) use ( $template_resolver ) {
			if ( ! is_preview() ) {
				return $template;
			}

			$post = get_post();
			if ( ! $post instanceof WP_Post ) {
				return $template;
			}

			// Check if the correspondent setting is enabled.
			if ( ! self::$settings_helper->in_iframe( $post->post_type ) ) {
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

			set_query_var( $template_resolver::HWP_PREVIEWS_IFRAME_PREVIEW_URL, self::generate_preview_url( $post ) );

			return $preview_template;
		}, 999 );
	}

	/**
	 * Swaps the preview link for the post types specified in the post types config.
	 * Is being enabled only if the preview is not in iframe. Otherwise preview functionality is resolved on the template redirect level.
	 */
	public static function enable_preview_functionality(): void {
		add_filter( 'preview_post_link', function ( $link, $post ) {
			// If iframe option is enabled, we need to resolve preview on the template redirect level.
			if ( self::$settings_helper->in_iframe( $post->post_type ) ) {
				return $link;
			}

			$url = self::generate_preview_url( $post );

			return ! empty( $url ) ? $url : $link;
		}, 10, 2 );

		/**
		 * Hack Function that changes the preview link for draft articles,
		 * this must be removed when properly fixed https://github.com/WordPress/gutenberg/issues/13998.
		 */
		foreach ( self::$types_config->get_public_post_types() as $key => $label ) {
			add_filter( 'rest_prepare_' . $key, [ self::class, 'filter_rest_prepare_link' ], 10, 2 );
		}
	}

	/**
	 * Generates the preview URL for the given post based on the preview URL template provided in settings.
	 *
	 * @param \WP_Post $post The post object.
	 *
	 * @return string The generated preview URL.
	 */
	public static function generate_preview_url( WP_Post $post ): string {
		// Check if the correspondent setting is enabled.
		$url = self::$settings_helper->url_template( $post->post_type );

		if ( empty( $url ) ) {
			return '';
		}

		return self::$link_service->generate_preview_post_link( $url, $post );
	}
}
