<?php

declare( strict_types=1 );

namespace HWP\Previews\Preview\Parameters;

use WP_Post;

class WP_Post_Property_Parameter extends Abstract_Preview_Parameter {

	private string $property;

	public function __construct( string $name, string $property, string $description) {
		parent::__construct( $name, $description );
		$this->property = $property;
	}

	public function get_value( WP_Post $post ): string {
		if ( empty( $post->{$this->property} ) ) {
			return '';
		}

		if ( is_array( $post->{$this->property} ) || is_object( $post->{$this->property} ) ) {
			return (string) wp_json_encode( $post->{$this->property} );
		}

		return (string) $post->{$this->property};
	}

}