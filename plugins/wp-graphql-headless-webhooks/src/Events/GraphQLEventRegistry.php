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
	 * Registered events.
	 *
	 * @var array<string, array<string, mixed>>
	 */
	private array $events = [];

	/**
	 * Initialize the registry — triggers the registration action and attaches events.
	 */
	public function init(): void {
		do_action( 'graphql_webhooks_register_events', $this );
		$this->events = apply_filters( 'graphql_webhooks_registered_events', $this->events, $this );
		$this->attach_events();	
	}

	/**
	 * Register an event with the registry.
	 *
	 * @param string        $name
	 * @param string        $hook_name
	 * @param callable|null $callback
	 * @param int           $priority
	 * @param int           $arg_count
	 *
	 * @return bool
	 */
	public function register_event(string $name, string $hook_name, callable|null $callback, int $priority = 10, int $arg_count = 1): bool {
		if ( isset( $this->events[ $name ] ) ) {
			return false;
		}

		$this->events[ $name ] = [ 
			'name' => $name,
			'hook_name' => $hook_name,
			'callback' => $callback,
			'priority' => $priority,
			'arg_count' => $arg_count,
		];
		return true;
	}

	/**
	 * Attach registered event callbacks to WordPress actions.
	 */
	public function attach_events(): void {
		foreach ( $this->events as $config ) {
			// Use provided callback, or default noop if none
			$callback = $config['callback'] ?? fn() => null;

			add_action( $config['hook_name'], function (...$hook_args) use ($config, $callback) {
				// Allow skipping via filter
				$maybe_skip = apply_filters( "graphql_webhooks_event_should_handle_{$config['name']}", null, ...$hook_args );
				if ( null !== $maybe_skip && false === $maybe_skip ) {
					return;
				}

				// Execute callback
				$payload = call_user_func( $callback, ...$hook_args );

				if ( is_wp_error( $payload ) ) {
					do_action( "graphql_webhooks_event_error_{$config['name']}", $payload, $hook_args );
					return;
				}

				// Filter payload before tracking
				$payload = apply_filters( "graphql_webhooks_event_payload_{$config['name']}", $payload, $hook_args );

				// Track via EventMonitor
				EventMonitor::track( $config['name'], $payload );

			}, $config['priority'], $config['arg_count'] );
		}
	}

	/**
	 * Get all registered events.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	public function get_events(): array {
		return $this->events;
	}

	/**
	 * Get a specific event by name.
	 *
	 * @param string $eventName
	 *
	 * @return array<string, mixed>|null
	 */
	public function get_event( string $eventName ): ?array {
		return $this->events[ $eventName ] ?? null;
	}
}