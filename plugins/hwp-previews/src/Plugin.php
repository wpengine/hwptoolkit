<?php

declare( strict_types=1 );

namespace HWP\Previews;

use HWP\Previews\Post\Data\Post_Data_Model;
use HWP\Previews\Post\Parent\Post_Parent_Manager;
use HWP\Previews\Post\Slug\Contracts\Post_Slug_Repository_Interface;
use HWP\Previews\Post\Slug\Post_Slug_Manager;
use HWP\Previews\Post\Slug\Post_Slug_Repository;
use HWP\Previews\Post\Status\Contracts\Post_Statuses_Config_Interface;
use HWP\Previews\Post\Status\Post_Statuses_Config;
use HWP\Previews\Post\Type\Contracts\Post_Types_Config_Interface;
use HWP\Previews\Post\Type\Post_Types_Config;
use HWP\Previews\Preview\Parameters\Preview_Parameter_Names_Model;
use HWP\Previews\Preview\Template\Contracts\Preview_Template_Resolver_Interface;
use HWP\Previews\Preview\Template\Preview_Template_Resolver;
use HWP\Previews\Preview\URL\Contracts\Preview_Parameter_Builder_Interface;
use HWP\Previews\Preview\URL\Contracts\Preview_URL_Generator_Interface;
use HWP\Previews\Preview\URL\Preview_URL_Generator;
use HWP\Previews\Preview\URL\Preview_Parameter_Builder;
use HWP\Previews\Settings\Headless_Preview_Settings;
use HWP\Previews\Token\Auth\Contracts\Token_Auth_Interface;
use HWP\Previews\Token\Extractor\Contracts\Token_Extractor_Interface;
use HWP\Previews\Token\Generator\Contracts\Token_Generator_Interface;
use HWP\Previews\Token\Manager\Contracts\Token_Manager_Interface;
use HWP\Previews\Token\Verifier\Contracts\Token_Verifier_Interface;
use HWP\Previews\Token\Manager\JWT_Token_Manager;
use HWP\Previews\Token\Auth\Token_Auth;
use HWP\Previews\Token\Extractor\Token_Extractor;
use HWP\Previews\Token\Generator\Token_Generator;
use HWP\Previews\Token\REST\Token_REST_Controller;
use HWP\Previews\Token\Verifier\Token_Verifier;
use WP_Post;

class Plugin {

	/**
	 * @var Plugin|null
	 */
	private static ?Plugin $instance = null;

	private Post_Types_Config_Interface $types_config;
	private Post_Statuses_Config_Interface $statuses_config;
	private Post_Slug_Repository_Interface $slug_repository;
	private Preview_Template_Resolver_Interface $template_resolver;
	private Preview_URL_Generator_Interface $url_generator;
	private Token_Manager_Interface $jwt_token;
	private Preview_Parameter_Builder_Interface $parameter_builder;

	private Token_Generator_Interface $token_generator;
	private Token_Extractor_Interface $token_extractor;
	private Token_Verifier_Interface $token_verifier;
	private Token_Auth_Interface $token_auth;
	private Token_REST_Controller $token_rest_controller;
	private Headless_Preview_Settings $settings;

	/**
	 * Private constructor to enforce singleton usage.
	 */
	private function __construct() {
		$this->init_dependencies();
		$this->register_hooks();
	}

	/**
	 * Get the singleton instance of the Plugin.
	 *
	 * @return Plugin
	 */
	public static function get_instance(): Plugin {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Initialize all dependencies.
	 *
	 * @return void
	 */
	private function init_dependencies(): void {
		$this->settings = new Headless_Preview_Settings();

		$post_types    = $this->settings->get_setting( Headless_Preview_Settings::POST_TYPES, [] );
		$post_statuses = $this->settings->get_setting( Headless_Preview_Settings::POST_STATUSES, [] );

		if ( ! $post_types || ! $post_statuses ) {
			return; // TODO: actually do something here.
		}

		// Configs.
		$this->types_config    = new Post_Types_Config( $post_types );
		$this->statuses_config = new Post_Statuses_Config( $post_statuses );

		// Dependencies.
		$token_secret    = $this->settings->get_setting( Headless_Preview_Settings::TOKEN_SECRET, '' ); // is required.
		$parameter_names = $this->settings->get_setting( Headless_Preview_Settings::PREVIEW_PARAMETER_NAMES, [
			'preview'        => 'preview',
			'token'          => 'token',
			'post_slug'      => 'slug',
			'post_id'        => 'p',
			'post_type'      => 'type',
			'post_uri'       => 'uri',
			'graphql_single' => 'gql'
		] );

		$this->slug_repository   = new Post_Slug_Repository();
		$this->template_resolver = new Preview_Template_Resolver( $this->types_config, $this->statuses_config );
		$this->url_generator     = new Preview_URL_Generator( $this->types_config, $this->statuses_config );
		$this->parameter_builder = new Preview_Parameter_Builder(
			new Preview_Parameter_Names_Model( $parameter_names )
		);

		// Token related.
		$this->jwt_token             = new JWT_Token_Manager( $token_secret );
		$this->token_extractor       = new Token_Extractor();
		$this->token_generator       = new Token_Generator( $this->jwt_token );
		$this->token_verifier        = new Token_Verifier( $this->jwt_token );
		$this->token_auth            = new Token_Auth( $this->token_verifier );
		$this->token_rest_controller = new Token_Rest_Controller( $this->token_verifier, $this->token_auth::PREVIEW_NONCE_ACTION );
	}

	private function register_hooks(): void {
		// Todo: better way of doing settings, these a for the demo only.


		if ( $this->settings->get_setting( Headless_Preview_Settings::ENABLE_UNIQUE_POST_SLUG, true ) ) {
			$this->enable_unique_post_slug();
		}

		if ( $this->settings->get_setting( Headless_Preview_Settings::ENABLE_POST_STATUSES_AS_PARENT, true ) ) {
			$this->enable_post_statuses_as_parent();
		}

		if ( $this->settings->get_setting( Headless_Preview_Settings::GENERATE_PREVIEW_LINKS, false ) ) {
			$this->enable_generate_preview_url();
		}

		if ( $this->settings->get_setting( Headless_Preview_Settings::TOKEN_AUTH_ENABLED, true ) ) {
			$this->enable_user_auth_for_preview();
		}

		if ( $this->settings->get_setting( Headless_Preview_Settings::PREVIEW_IN_IFRAME, true ) ) {
			$this->enable_preview_in_iframe_functionality();
		}

		if ( $this->settings->get_setting( Headless_Preview_Settings::REST_TOKEN_VERIFICATION, true ) ) {
			$this->enable_rest_route_token_verification();
		}
	}

	private function enable_unique_post_slug(): void {
		$post_slug_manager = new Post_Slug_Manager( $this->types_config, $this->statuses_config, $this->slug_repository );

		add_filter( 'wp_insert_post_data', static function ( $data, $postarr ) use ( $post_slug_manager ) {
			global $wp_rewrite;

			$post_slug = $post_slug_manager->force_unique_post_slug(
				new WP_Post( new Post_Data_Model( $data, (int) ( $postarr['ID'] ?? 0 ) ) ),
				$wp_rewrite
			);

			if ( $post_slug ) {
				$data['post_name'] = $post_slug;
			}

			return $data;
		}, 10, 2 );
	}

	private function enable_post_statuses_as_parent(): void {
		$post_parent_manager = new Post_Parent_Manager( $this->types_config, $this->statuses_config );

		$post_parent_manager_callback = static function ( $args ) use ( $post_parent_manager ) {
			$post_type = ! empty( $args['post_type'] ) ? get_post_type_object( (string) $args['post_type'] ) : null;
			if ( $post_type ) {
				$args['post_status'] = $post_parent_manager->get_post_statuses_as_parent( $post_type );
			}

			return $args;
		};

		add_filter( 'page_attributes_dropdown_pages_args', $post_parent_manager_callback );
		add_filter( 'quick_edit_dropdown_pages_args', $post_parent_manager_callback );

		// And for Gutenberg.
		foreach ( $this->types_config->get_post_types() as $post_type ) {
			$post_type_object = get_post_type_object( $post_type );
			if ( ! $post_type_object || ! $this->types_config->supports_gutenberg( $post_type_object ) ) {
				continue;
			}
			add_filter( 'rest_' . $post_type . '_query', $post_parent_manager_callback );
		}
	}

	private function enable_generate_preview_url(): void {
		add_filter( 'preview_post_link', function ( $link, $post ) {
			return $this->generate_preview_url( $post ) ?: $link;
		}, 10, 2 );

		/**
		 * Hack Function that changes the preview link for draft articles,
		 * this must be removed when wordpress do the properly fix https://github.com/WordPress/gutenberg/issues/13998
		 */
		foreach ( $this->types_config->get_post_types() as $post_type ) {
			add_filter( 'rest_prepare_' . $post_type, function ( $response, $post ) {
				$preview_url = $this->generate_preview_url( $post );
				if ( $preview_url ) {
					$response->data['link'] = $preview_url;
				}

				return $response;
			}, 10, 2 );
		}
	}

	private function enable_user_auth_for_preview(): void {
		add_filter( 'determine_current_user', function ( $user_id ) {
			if ( $user_id ) {
				return $user_id;
			}

			$token = $this->token_extractor->get_token();
			if ( ! $token ) {
				return $user_id;
			}

			return $this->token_auth->determine_preview_user( $token ) ?: $user_id;
		} );
	}

	private function enable_preview_in_iframe_functionality(): void {
		add_filter( 'template_include', function ( $template ) {
			if ( ! is_preview() ) {
				return $template;
			}

			$post = get_post();
			if ( ! $post instanceof WP_Post ) {
				return $template;
			}

			$template_dir_path = (string) apply_filters(
				'hwp_previews_template_dir_path',
				plugin_dir_path( dirname( __FILE__ ) ) . 'templates'
			);

			$preview_template = $this->template_resolver->resolve_template_path( $post, $template_dir_path, true );

			if ( ! $preview_template ) { // TODO: Do something about it.
				return $template;
			}

			set_query_var( $this->template_resolver::HWP_PREVIEWS_IFRAME_PREVIEW_URL, $this->generate_preview_url( $post ) );

			return $preview_template;
		}, 999 );
	}

	private function generate_preview_url( WP_Post $post ): string {
		$preview_url = $this->settings->get_setting( Headless_Preview_Settings::PREVIEW_URL, '' );
		if ( ! $preview_url ) {
			return ''; // todo: maybe do something?
		}

		$token_auth  = $this->settings->get_setting( Headless_Preview_Settings::TOKEN_AUTH_ENABLED, true );
		$draft_route = $this->settings->get_setting( Headless_Preview_Settings::DRAFT_ROUTE, '' );

		$token = '';
		if ( $this->settings->get_setting( Headless_Preview_Settings::GENERATE_PREVIEW_TOKEN, true ) ) {
			$token = $this->token_generator->generate_token( $token_auth ? [ 'data' => [ 'user' => [ 'id' => get_current_user_id() ] ] ] : [], 'preview_url_nonce', 300 );
		}

		$args = (array) apply_filters( 'hwp_preview_args', $this->parameter_builder->build_preview_args( $post, $token ), $post );

		return $this->url_generator->generate_url( $post, $preview_url, $args, $draft_route );
	}

	public function enable_rest_route_token_verification(): void {
		add_action( 'rest_api_init', function () {
			$this->token_rest_controller->register_routes( 'hwp-previews/v1' );
		} );
	}

}
