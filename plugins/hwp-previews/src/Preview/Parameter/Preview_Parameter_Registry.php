<?php

declare(strict_types=1);

namespace HWP\Previews\Preview\Parameter;

use HWP\Previews\Preview\Parameter\Contracts\Preview_Parameter_Interface;

/**
 * Class Preview_Parameter_Registry.
 *
 * This class is responsible for registering and managing preview parameters.
 */
class Preview_Parameter_Registry {
	/**
	 * Registered parameters.
	 *
	 * @var array<\HWP\Previews\Preview\Parameter\Contracts\Preview_Parameter_Interface>
	 */
	private array $parameters = [];

	/**
	 * Register a parameter.
	 *
	 * @param \HWP\Previews\Preview\Parameter\Contracts\Preview_Parameter_Interface $parameter The parameter object.
	 */
	public function register( Preview_Parameter_Interface $parameter ): self {
		$this->parameters[ $parameter->get_name() ] = $parameter;

		return $this;
	}

	/**
	 * Unregister a parameter.
	 *
	 * @param string $name The parameter name.
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
	 * @return array<string, \HWP\Previews\Preview\Parameter\Contracts\Preview_Parameter_Interface>
	 */
	public function get_all(): array {
		return $this->parameters;
	}

	/**
	 * Get all registered parameters as an array of their names and descriptions.
	 *
	 * @return array<string, string>
	 */
	public function get_descriptions(): array {
		$descriptions = [];
		foreach ( $this->parameters as $parameter ) {
			$descriptions[ $parameter->get_name() ] = $parameter->get_description();
		}

		return $descriptions;
	}

	/**
	 * Get a specific parameter by name. Returns null if not found.
	 *
	 * @param string $name The parameter name.
	 */
	public function get( string $name ): ?Preview_Parameter_Interface {
		return $this->parameters[ $name ] ?? null;
	}
}
