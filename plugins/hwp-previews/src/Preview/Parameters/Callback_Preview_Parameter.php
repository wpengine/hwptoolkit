<?php

declare( strict_types=1 );

namespace HWP\Previews\Preview\Parameters;

use InvalidArgumentException;
use WP_Post;

class Callback_Preview_Parameter extends Abstract_Preview_Parameter {

	/**
	 * @var callable $callback
	 */
	private $callback;

	public function __construct( string $name, callable $callback, string $description ) {
		parent::__construct( $name, $description );
		$this->callback = $callback;
	}

	public function get_value( WP_Post $post ): string {
		$value = call_user_func( $this->callback, $post );

		if ( ! is_string( $value ) ) {
			throw new InvalidArgumentException( 'Callback must return a string.' );
		}

		return $value;
	}
}