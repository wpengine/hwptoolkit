<?php

declare( strict_types=1 );

namespace HWP\Previews\Preview\Parameters;

use HWP\Previews\Preview\Parameters\Contracts\Preview_Parameter_Interface;

class Preview_Parameter_Factory {

	public function create_callback_parameter(
		string $name,
		callable $callback,
		string $description = ''
	): Preview_Parameter_Interface {
		return new Callback_Preview_Parameter( $name, $callback, $description );
	}

	public function create_post_property_parameter(
		string $name,
		string $property,
		string $description = ''
	): Preview_Parameter_Interface {
		return new WP_Post_Property_Parameter( $name, $property, $description );
	}

	private function format_field_name( string $name ): string {
		return ucwords( str_replace( '_', ' ', $name ) );
	}

	private function format_field_description( string $description, string $type ): string {
		if ( $description && $type ) {
			$description .= "\n" . __( 'Type:', 'hwp-previews' ) . ' ' . $type;
		}

		return $description;
	}

}