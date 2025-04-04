<?php

declare( strict_types=1 );

namespace HWP\Previews\Preview\Parameters;

use HWP\Previews\Preview\Parameters\Contracts\Preview_Parameter_Builder_Interface;
use WP_Post;

class Preview_Parameter_Builder implements Preview_Parameter_Builder_Interface {

	private Preview_Parameter_Names_Model $parameter_names;

	public function __construct( Preview_Parameter_Names_Model $parameter_names ) {
		$this->parameter_names = $parameter_names;
	}

	/**
	 * @param WP_Post $post
	 * @param string $token
	 *
	 * @return array<string, mixed>
	 */
	public function build_preview_args( WP_Post $post, string $page_uri, string $token ): array {
		// Add default preview param.
		$args = [
			$this->parameter_names->preview => 'true',
		];

		// Add post slug param.
		if ( $this->parameter_names->post_slug ) {
			$args[ $this->parameter_names->post_slug ] = $post->post_name;
		}

		// Add post id param.
		if ( $this->parameter_names->post_id ) {
			$args[ $this->parameter_names->post_id ] = $post->ID;
		}

		// Add post type param.
		if ( $this->parameter_names->post_type ) {
			$args[ $this->parameter_names->post_type ] = $post->post_type;
		}

		// Add graphql single name param.
		if ( $this->parameter_names->graphql_single ) {
			$post_type_object = get_post_type_object( $post->post_type );

			if ( ! empty( $post_type_object->graphql_single_name ) ) {
				$args[ $this->parameter_names->graphql_single ] = ucfirst( $post_type_object->graphql_single_name );
			}
		}

		// Add page uri param.
		if ( $this->parameter_names->post_uri ) {
			$page_uri = (string) get_page_uri( $post->ID );

			if ($page_uri) {
				$args[ $this->parameter_names->post_uri ] = $page_uri;
			}
		}

		// Add token param.
		if ( $this->parameter_names->token && $token ) {
			$args[ $this->parameter_names->token ] = $token;
		}

		return $args;
	}

}