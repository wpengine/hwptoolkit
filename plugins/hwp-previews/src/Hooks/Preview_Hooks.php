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
use HWP\Previews\Post\Type\Post_Types_Config_Registry;
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
		self::init_class_properties();
		self::add_filters_actions();
	}

	public static function add_filters_actions() {
		// Enable the unique post slug functionality.
		add_filter( 'wp_insert_post_data', [ self::class, 'enable_unique_post_slug' ], 10, 2 );

		// Enable post statuses as parent for the post types specified in the post-types config.
		add_filter( 'page_attributes_dropdown_pages_args', [ self::class, 'enable_post_statuses_as_parent' ], 10, 1 );
		add_filter( 'quick_edit_dropdown_pages_args', [ self::class, 'enable_post_statuses_as_parent' ], 10, 1 );

		foreach ( self::$types_config->get_post_types() as $post_type ) {
			if ( ! self::$types_config->gutenberg_editor_enabled( $post_type ) ) {
				continue;
			}
			// @TODO - Add unit tests for this filter.
			add_filter( 'rest_' . $post_type . '_query', [ self::class, 'enable_post_statuses_as_parent' ], 10, 1 );
		}

		// iframe preview functionality.
		add_filter( 'template_include', [ self::class, 'add_iframe_preview_template' ], 10, 1 );

		// Preview link functionality.
		add_filter( 'preview_post_link', [ self::class, 'update_preview_post_link' ], 10, 2 );


		/**
		 * Hack Function that changes the preview link for draft articles,
		 * this must be removed when properly fixed https://github.com/WordPress/gutenberg/issues/13998.
		 */
		foreach ( self::$types_config->get_public_post_types() as $key => $label ) {
			add_filter( 'rest_prepare_' . $key, [ self::class, 'filter_rest_prepare_link' ], 10, 2 );
		}
	}

	public static function init_class_properties(): void {

		self::$settings_helper = Settings_Helper::get_instance();

		self::$types_config = apply_filters(
			'hwp_previews_hooks_post_type_config',
			Post_Types_Config_Registry::get_post_type_config()
		);

		// Initialize the post types and statuses configurations.
		self::$statuses_config = apply_filters(
			'hwp_previews_hooks_post_status_config',
			( new Post_Statuses_Config() )->set_post_statuses( self::get_post_statuses() )
		);

		// Initialize the preview link service.
		self::$link_service = apply_filters(
			'hwp_previews_hooks_preview_link_service',
			new Preview_Link_Service(
				self::$types_config,
				self::$statuses_config,
				new Preview_Link_Placeholder_Resolver( Preview_Parameter_Registry::get_instance() )
			)
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

		return apply_filters( 'hwp_previews_hooks_post_statuses', $post_statuses );
	}

	/**
	 * @TODO Remove as part of https://github.com/wpengine/hwptoolkit/issues/226
	 *
	 * @link https://developer.wordpress.org/reference/hooks/wp_insert_post_data/
	 *
	 * @param array $data
	 * @param array $postarr
	 *
	 * @return array
	 */
	public static function enable_unique_post_slug( array $data, array $postarr ): array {
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
	}


	/**
	 * Enable post statuses as parent for the post types specified in the post types config.
	 *
	 * @param array $args The arguments for the dropdown pages
	 *
	 * @return array The modified dropdown arguments with post statuses as parent if applicable.
	 * @link https://developer.wordpress.org/reference/hooks/page_attributes_dropdown_pages_args/.
	 *
	 * @link https://developer.wordpress.org/reference/hooks/quick_edit_dropdown_pages_args/
	 */
	public static function enable_post_statuses_as_parent( array $args ): array {
		$post_parent_manager = new Post_Parent_Manager( self::$types_config, self::$statuses_config );

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
	 * Enable preview functionality in iframe.
	 */
	public static function add_iframe_preview_template( string $template ): string {
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

		$template_resolver = new Preview_Template_Resolver( self::$types_config, self::$statuses_config );


		/**
		 * The filter 'hwp_previews_template_path' allows to change the template directory path.
		 */
		$template_dir_path = (string) apply_filters(
			'hwp_previews_template_path',
			trailingslashit( HWP_PREVIEWS_TEMPLATE_DIR ) . 'hwp-preview.php',
		);

		$preview_template = $template_resolver->resolve_template_path( $post, $template_dir_path );

		if ( empty( $preview_template ) ) {
			error_log( 'Preview template not found for post type' . (string) $post->post_type );

			return $template;
		}

		set_query_var( $template_resolver::HWP_PREVIEWS_IFRAME_PREVIEW_URL, self::generate_preview_url( $post ) );

		return $preview_template;
	}

	/**
	 * Enables preview functionality when iframe option is disabled.
	 *
	 * @link https://developer.wordpress.org/reference/hooks/preview_post_link/
	 *
	 * @return void
	 */
	public static function update_preview_post_link( string $preview_link, WP_Post $post ): string {

		// @TODO - Need to do more testing and add e2e tests for this filter.

		// If iframe option is enabled, we need to resolve preview on the template redirect level.
		if ( self::$settings_helper->in_iframe( $post->post_type ) ) {
			return $preview_link;
		}

		$url = self::generate_preview_url( $post );
		if ( empty( $url ) ) {
			return $preview_link;
		}

		return $url;
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
