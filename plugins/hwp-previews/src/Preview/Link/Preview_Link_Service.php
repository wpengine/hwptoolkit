<?php

declare( strict_types=1 );

namespace HWP\Previews\Preview\Link;

use HWP\Previews\Post\Status\Contracts\Post_Statuses_Config_Interface;
use HWP\Previews\Post\Type\Contracts\Post_Types_Config_Interface;
use HWP\Previews\Preview\Parameters\Preview_Parameter_Registry;
use WP_Post;

class Preview_Link_Service {

	private Post_Types_Config_Interface $types;
	private Post_Statuses_Config_Interface $statuses;
	private Preview_Parameter_Registry $registry;

	public function __construct(
		Post_Types_Config_Interface $types,
		Post_Statuses_Config_Interface $statuses,
		Preview_Parameter_Registry $registry
	) {
		$this->types    = $types;
		$this->statuses = $statuses;
		$this->registry = $registry;
	}

	public function generate_preview_post_link( string $preview_url, WP_Post $post, string $route = '' ): string {
		if (
			! $preview_url ||
			! $this->types->is_post_type_applicable( $post->post_type ) ||
			! $this->statuses->is_post_status_applicable( $post->post_status )
		) {
			return '';
		}

		$parameters = [];

		foreach ( $this->registry->get_all() as $parameter ) {
			$value = $parameter->get_value( $post );
			if ( $value ) {
				$parameters[ $parameter->get_name() ] = urlencode( $value );
			}
		}

		if ( empty( $parameters ) ) {
			return $preview_url;
		}

		if ( $route ) {
			$preview_url = trailingslashit( $preview_url ) . $route;
		}

		return add_query_arg( $parameters, $preview_url );
	}

}