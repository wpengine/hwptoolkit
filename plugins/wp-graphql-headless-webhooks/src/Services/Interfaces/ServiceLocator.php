<?php
namespace WPGraphQL\Webhooks\Services\Interfaces;

interface ServiceLocator {
	/**
	 * Registers a service factory callable for a given name.
	 *
	 * @param string $name
	 * @param callable $factory
	 */
	public function set( string $name, callable $factory ): void;

	/**
	 * Checks if a service is registered.
	 *
	 * @param string $name
	 * @return bool
	 */
	public function has( string $name ): bool;

	/**
	 * Retrieves a service instance by name.
	 *
	 * @param string $name
	 * @return object
	 */
	public function get( string $name );
}