<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Events;

/**
 * Request Context Service.
 *
 * Manages request context data that can be shared across the Query event lifecycle.
 * Provides a centralized way to store and retrieve context information for logging purposes.
 *
 * @package WPGraphQL\Logging
 *
 * @since 0.0.1
 */
class Request_Context_Service {

	/**
	 * The additional data for the context.
	 *
	 * @var array<string, mixed>
	 */
	protected array $additional_data = [];

	/**
	 * Constructor.
	 *
	 * @param string|null               $query          The GraphQL query string.
	 * @param string|null               $operation_name The operation name.
	 * @param array<string, mixed>|null $variables      The variables for the query.
	 */
	public function __construct(
		readonly ?string $query = null,
		readonly ?string $operation_name = null,
		readonly ?array $variables = null) {
	}

	/**
	 * Set additional data for the context.
	 *
	 * @param string $key   The key for the data.
	 * @param mixed  $value The value to store.
	 */
	public function set_data( string $key, mixed $value ): void {
		$this->additional_data[ $key ] = $value;
	}

	/**
	 * Get additional data from the context.
	 *
	 * @param string $key     The key for the data.
	 * @param mixed  $default The default value if key doesn't exist.
	 *
	 * @return mixed
	 */
	public function get_data( string $key, mixed $default = null ): mixed {
		return $this->additional_data[ $key ] ?? $default;
	}

	/**
	 * Convert the context to an array.
	 *
	 * @return array<string, mixed>
	 */
	public function to_array(): array {
		return [
			'query'           => $this->query,
			'operation_name'  => $this->operation_name,
			'variables'       => $this->variables,
			...$this->additional_data,
		];
	}

	/**
	 * Get all additional data.
	 *
	 * @return array<string, mixed>
	 */
	public function get_all_additional_data(): array {
		return $this->additional_data;
	}

	/**
	 * Remove additional data by key.
	 *
	 * @param string $key The key to remove.
	 */
	public function remove_data( string $key ): void {
		unset( $this->additional_data[ $key ] );
	}

	/**
	 * Clear all additional data.
	 */
	public function clear_additional_data(): void {
		$this->additional_data = [];
	}
}
