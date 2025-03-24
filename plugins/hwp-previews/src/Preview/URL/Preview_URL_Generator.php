<?php

declare( strict_types=1 );

namespace HWP\Previews\Preview\URL;

use HWP\Previews\Post\Status\Contracts\Post_Statuses_Config_Interface;
use HWP\Previews\Post\Type\Contracts\Post_Types_Config_Interface;
use HWP\Previews\Preview\URL\Contracts\Preview_URL_Generator_Interface;
use WP_Post;

class Preview_URL_Generator implements Preview_URL_Generator_Interface {

	protected Post_Types_Config_Interface $types;
	protected Post_Statuses_Config_Interface $statuses;

	public function __construct( Post_Types_Config_Interface $types, Post_Statuses_Config_Interface $statuses ) {
		$this->types    = $types;
		$this->statuses = $statuses;
	}

	public function generate_url(
		WP_Post $post,
		string $frontend_url,
		string $page_uri,
		array $args,
		string $draft_route = ''
	): string {
		if (
			! $frontend_url ||
			! $this->types->is_post_type_applicable( $post->post_type ) ||
			! $this->statuses->is_post_status_applicable( $post->post_status )
		) {
			return '';
		}

		// Format frontend URL to the draft route handler.
		if ( $draft_route ) {
			$frontend_url = trailingslashit( $frontend_url ) . $draft_route;
		} else {
			$frontend_url = trailingslashit( $frontend_url ) . $page_uri;
		}

		if ( $args ) {
			return add_query_arg( $args, $frontend_url );
		}

		return $frontend_url;
	}

}