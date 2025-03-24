<?php

declare( strict_types=1 );

namespace HWP\Previews\Preview\Parameters;

use HWP\Previews\Preview\Parameters\Contracts\Preview_Parameter_Interface;

class Preview_Parameter_Registry {

	/**
	 * Registered parameters.
	 *
	 * @var Preview_Parameter_Interface[]
	 */
	private array $parameters = [];

	/**
	 * Register a parameter.
	 *
	 * @param Preview_Parameter_Interface $parameter The parameter object.
	 *
	 * @return self
	 */
	public function register( Preview_Parameter_Interface $parameter ): self {
		$this->parameters[ $parameter->get_name() ] = $parameter;

		return $this;
	}

	/**
	 * Unregister a parameter.
	 *
	 * @param string $name The parameter name.
	 *
	 * @return self
	 */
	public function unregister( string $name ): self {
		if ( isset( $this->parameters[ $name ] ) ) {
			unset( $this->parameters[ $name ] );
		}

		return $this;
	}

	/**
	 * Get all registered parameters.
	 *
	 * @return Preview_Parameter_Interface[]
	 */
	public function get_all(): array {
		return $this->parameters;
	}

	/**
	 * Get a specific parameter by name.
	 *
	 * @param string $name The parameter name.
	 *
	 * @return Preview_Parameter_Interface|null
	 */
	public function get( string $name ): ?Preview_Parameter_Interface {
		return $this->parameters[ $name ] ?? null;
	}

}