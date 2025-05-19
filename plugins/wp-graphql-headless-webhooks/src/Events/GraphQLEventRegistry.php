<?php
namespace WPGraphQL\Webhooks\Events;

use WPGraphQL\Webhooks\Events\Interfaces\EventRegistry;

/**
 * Class GraphQLEventRegistry
 * 
 * Registers and manages WordPress events for the WPGraphQL Webhooks system
 */
class GraphQLEventRegistry implements EventRegistry {

	/**
	 * Registered events
	 *
	 * @var array<string, array<string, mixed>> Array of events keyed by name.
	 */
	private array $events = [];

	/**
	 * Register an event with the registry
	 *
	 * @param string   $eventName  The name of the event.
	 * @param string   $hookName   The WordPress hook to listen to.
	 * @param callable|null $callback  The callback to execute when the hook fires.
	 * @param int      $priority   Hook priority.
	 * @param int      $argCount   Number of arguments to pass to the callback.
	 * 
	 * @return bool Whether the event was registered successfully.
	 */
	public function registerEvent( string $name, string $hook_name, ?callable $callback, int $priority = 10, int $arg_count = 1 ): bool {
		if ( isset( $this->events[ $name ] ) ) {
			return false;
		}

		// Store the event information
		$this->events[ $name ] = [ 
			'name' => $name,
			'hook_name' => $hook_name,
			'callback' => $callback,
			'priority' => $priority,
			'arg_count' => $arg_count,
		];

		// If a callback is provided, register it with WordPress
		if ( $callback !== null ) {
			$this->hookCallback( $name, $hook_name, $callback, $priority, $arg_count );
		}
		return true;
	}

	/**
	 * Hook a callback to a WordPress action
	 *
	 * @param string   $eventName  The name of the event.
	 * @param string   $hookName   The WordPress hook to listen to.
	 * @param callable $callback   The callback to execute when the hook fires.
	 * @param int      $priority   Hook priority.
	 * @param int      $argCount   Number of arguments to pass to the callback.
	 */
	private function hookCallback( string $eventName, string $hookName, callable $callback, int $priority, int $argCount ): void {
		if ( did_action( 'graphql_register_events' ) ) {
			_doing_it_wrong( 'registerEvent', __( 'Call this before EventRegistry::init()', 'wp-graphql-headless-webhooks' ), '1.0.0' );
			return;
		}
		if ( $callback === null ) {
			$callback = fn() => null;
		}
		// Assume https://github.com/wp-graphql/wp-graphql/pull/3376 is merged
		if ( function_exists( 'register_graphql_event' ) ) {
			register_graphql_event( $eventName, $hookName, $callback, $priority, $argCount );
		} else {
			error_log( 'register_graphql_event function does not exist.' );
		}
	}

	/**
	 * Get all registered events
	 *
	 * @return array<string, array<string, mixed>> Array of events keyed by name.
	 */
	public function getEvents(): array {
		return $this->events;
	}

	/**
	 * Get a specific event by name
	 *
	 * @param string $eventName The name of the event.
	 * @return array<string, mixed>|null Event information or null if not found.
	 */
	public function getEvent( string $eventName ): ?array {
		return $this->events[ $eventName ] ?? null;
	}
}