<?php

declare(strict_types=1);

namespace HWP\Previews\Shared;

use Exception;

abstract class Model {

	/**
	 * TODO: anything else that can allow object usage experience.
	 *
	 * @param string $name
	 * @param mixed  $value
	 *
	 * @return mixed
	 * @throws Exception
	 */
	public function __set( string $name, $value ) {
		throw new Exception( "Cannot modify readonly property: {$name}" ); // TODO: improve.
	}

}