<?php

declare(strict_types=1);

namespace HWP\Previews\Hooks;

use HWP\Previews\Admin\Settings\Fields\Settings_Field_Collection;
use HWP\Previews\Preview\Parameter\Preview_Parameter_Registry;
use HWP\Previews\Preview\Post\Post_Preview_Service;
use HWP\Previews\Preview\Post\Post_Settings_Service;
use HWP\Previews\Preview\Post\Post_Type_Service;
use HWP\Previews\Preview\Post\Status\Contracts\Post_Statuses_Config_Interface;
use HWP\Previews\Preview\Post\Status\Post_Statuses_Config;
use HWP\Previews\Preview\Post\Type\Contracts\Post_Types_Config_Interface;
use HWP\Previews\Preview\Post\Type\Post_Types_Config_Registry;
use HWP\Previews\Preview\Template\Template_Resolver_Service;
use HWP\Previews\Preview\Url\Preview_Url_Resolver_Service;
use WP_Post;
use WP_REST_Response;

class Preview_Hooks {
	/**
	 * Post types configuration.
	 *
	 * @var \HWP\Previews\Preview\Post\Type\Contracts\Post_Types_Config_Interface
	 */
	protected Post_Types_Config_Interface $types_config;

	/**
	 * Post statuses configuration.
	 *
	 * @var \HWP\Previews\Preview\Post\Status\Contracts\Post_Statuses_Config_Interface
	 */
	protected Post_Statuses_Config_Interface $statuses_config;

	/**
	 * Post-settings service that provides access to post-settings.
	 *
	 * @var \HWP\Previews\Preview\Post\Post_Preview_Service
	 */
	protected Post_Preview_Service $post_preview_service;

	/**
	 * The instance of the Preview_Hooks class.
	 *
	 * @var \HWP\Previews\Hooks\Preview_Hooks|null
	 */
	protected static ?Preview_Hooks $instance = null;

	/**
	 * Post-settings service that provides access to post-settings.
	 *
	 * @var \HWP\Previews\Preview\Post\Post_Settings_Service
	 */
	private Post_Settings_Service $post_settings_service;

	/**
	 * Constructor for the Preview_Hooks class.
	 *
	 * Initializes the settings helper, post types and statuses configurations, and the preview link service.
	 */
	public function __construct() {

		// @TODO - Refactor to use a factory or service locator pattern and analyze what is is actually needed.

		$this->types_config = apply_filters(
			'hwp_previews_hooks_post_type_config',
			Post_Types_Config_Registry::get_post_type_config()
		);

		// Initialize the post types and statuses configurations.
		$this->statuses_config = apply_filters(
			'hwp_previews_hooks_post_status_config',
			( new Post_Statuses_Config() )->set_post_statuses( $this->get_post_statuses() )
		);


		$this->post_preview_service  = new Post_Preview_Service();
		$this->post_settings_service = new Post_Settings_Service();
	}

	/**
	 * Registers the hooks for the preview functionality.
	 */
	public function setup(): void {

		// Enable post statuses as parent for the post types specified in the post-types config.
		add_filter( 'page_attributes_dropdown_pages_args', [ $this, 'enable_post_statuses_as_parent' ], 10, 1 );
		add_filter( 'quick_edit_dropdown_pages_args', [ $this, 'enable_post_statuses_as_parent' ], 10, 1 );

		foreach ( $this->types_config->get_post_types() as $post_type ) {
			if ( ! $this->types_config->gutenberg_editor_enabled( $post_type ) ) {
				continue;
			}
			// @TODO - Add unit tests for this filter.
			add_filter( 'rest_' . $post_type . '_query', [ $this, 'enable_post_statuses_as_parent' ], 10, 1 );
		}

		// iframe preview functionality.
		add_filter( 'template_include', [ $this, 'add_iframe_preview_template' ], 10, 1 );

		// Preview link functionality. Extra priority to ensure it runs after the Faust preview link filter.
		add_filter( 'preview_post_link', [ $this, 'update_preview_post_link' ], 1001, 2 );


		/**
		 * Hack Function that changes the preview link for draft articles,
		 * this must be removed when properly fixed https://github.com/WordPress/gutenberg/issues/13998.
		 */
		foreach ( $this->types_config->get_public_post_types() as $key => $label ) {
			add_filter( 'rest_prepare_' . $key, [ $this, 'filter_rest_prepare_link' ], 10, 2 );
		}
	}

	/**
	 * Gets a list of available post statuses for the preview functionality..
	 *
	 * @return array<string>
	 */
	public function get_post_statuses(): array {
		// @TODO - Remove
		$post_statuses = [
			'publish',
			'future',
			'draft',
			'pending',
			'private',
			'auto-draft',
		];

		return apply_filters( 'hwp_previews_hooks_post_statuses', $post_statuses );
	}

	/**
	 * Enable post statuses as parent for the post types specified in the post types config.
	 *
	 * @param array<mixed> $args The arguments for the dropdown pages.
	 *
	 * @return array<mixed> The modified dropdown arguments with post statuses as parent if applicable.
	 *
	 * @link https://developer.wordpress.org/reference/hooks/page_attributes_dropdown_pages_args/.
	 *
	 * @link https://developer.wordpress.org/reference/hooks/quick_edit_dropdown_pages_args/
	 */
	public function enable_post_statuses_as_parent( array $args ): array {
		if ( ! $this->should_enable_post_statuses_as_parent( $args ) ) {
			return $args;
		}

		$parent_statuses = $this->post_preview_service->get_parent_post_statuses();
		$post_statuses   = $this->post_preview_service->get_post_statuses();
		$post_statuses   = array_intersect( $parent_statuses, $post_statuses );

		if ( empty( $post_statuses ) ) {
			return $args;
		}

		$args['post_status'] = $post_statuses;

		return $args;
	}

	/**
	 * Whether post-statuses should be enabled as parent for the given post-type.
	 *
	 * @param array<mixed> $args
	 */
	public function should_enable_post_statuses_as_parent( array $args ): bool {
		if ( empty( $args['post_type'] ) ) {
			return false;
		}

		$post_type = (string) $args['post_type'];

		if ( ! is_post_type_hierarchical( $post_type ) ) {
			return false;
		}

		$config = $this->post_settings_service->get_post_type_config( $post_type );
		if ( ! is_array( $config ) || empty( $config ) ) {
			return false;
		}

		$field_id = Settings_Field_Collection::POST_STATUSES_AS_PARENT_FIELD_ID;

		return isset( $config[ $field_id ] ) && (bool) $config[ $field_id ];
	}

	/**
	 * Replace the preview link in the REST response.
	 */
	public function filter_rest_prepare_link( WP_REST_Response $response, WP_Post $post ): WP_REST_Response {

		$post_type_service = new Post_Type_Service( $post, $this->post_preview_service, $this->post_settings_service );

		if ( ! $post_type_service->is_allowed_for_previews() ) {
			return $response;
		}

		if ( $post_type_service->is_iframe() ) {
			return $response;
		}

		$preview_url = $this->generate_preview_url( $post );
		if ( ! empty( $preview_url ) ) {
			$response->data['link'] = $preview_url;
		}

		return $response;
	}

	/**
	 * Enable preview functionality in iframe.
	 */
	public function add_iframe_preview_template( string $template ): string {

		if ( ! is_preview() ) {
			return $template;
		}

		$post = get_post();
		if ( ! ( $post instanceof WP_Post ) ) {
			return $template;
		}

		$post_type_service = new Post_Type_Service( $post, $this->post_preview_service, $this->post_settings_service );

		if (
			! $post_type_service->is_allowed_for_previews() ||
			! $post_type_service->is_iframe()
		) {
			return $template;
		}

		$template_resolver = new Template_Resolver_Service();
		$iframe_template   = $template_resolver->get_iframe_template();
		$url               = self::generate_preview_url( $post );

		if ( empty( $iframe_template ) || empty( $url ) ) {
			return $template;
		}

		$template_resolver->set_query_variable( $url );

		return $iframe_template;
	}

	/**
	 * Enables preview functionality when iframe option is disabled.
	 *
	 * @link https://developer.wordpress.org/reference/hooks/preview_post_link/
	 */
	public function update_preview_post_link( string $preview_link, WP_Post $post ): string {

		$post_type_service = new Post_Type_Service( $post, $this->post_preview_service, $this->post_settings_service );

		if ( ! $post_type_service->is_allowed_for_previews() ) {
			return $preview_link;
		}

		// If the iframe option is enabled, we need to resolve preview on the template redirect level.
		if ( $post_type_service->is_iframe() ) {
			return $preview_link;
		}

		$url = $this->generate_preview_url( $post );
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
	public function generate_preview_url( WP_Post $post ): string {

		$post_type_service = new Post_Type_Service( $post, $this->post_preview_service, $this->post_settings_service );

		if ( ! $post_type_service->is_allowed_for_previews() ) {
			return '';
		}

		$url = $post_type_service->get_preview_url();
		if ( empty( $url ) ) {
			return '';
		}

		$service = new Preview_Url_Resolver_Service( Preview_Parameter_Registry::get_instance() );
		$url     = $service->resolve( $post, $url );
		if ( empty( $url ) ) {
			return '';
		}

		return $url;
	}

	/**
	 * Initialize the hooks for the preview functionality.
	 */
	public static function init(): void {
		if ( ! isset( self::$instance ) || ! ( is_a( self::$instance, self::class ) ) ) {
			self::$instance = new self();
			self::$instance->setup();
		}
	}
}
