<?php

declare(strict_types=1);

namespace HWP\Previews\Shared;

use Exception;

/**
 * Abstract class for models.
 */
abstract class Abstract_Model {
	/**
	 * This is a very good example of the method.
	 *
	 * @param string                                         $name The name of the property.
	 * @param string|int|float|bool|array<mixed>|object|null $value The value to set.
	 *
	 * @throws \Exception When attempting to modify a readonly property.
	 */
	public function __set( string $name, $value ): void {
		throw new Exception( 'Cannot modify readonly property: ' . esc_html( $name ) );
	}
}
