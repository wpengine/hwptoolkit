<?php
namespace WPGraphQL\Webhooks\Events;

use WPGraphQL\Webhooks\Events\Interfaces\EventRegistry;
use WPGraphQL\Webhooks\Events\Interfaces\EventDispatcher;


/**
 * Class GraphQLEventRegistry
 * 
 * Registers and manages WordPress events for the WPGraphQL Webhooks system
 */
class GraphQLEventRegistry implements EventRegistry {

	/**
     * Registered events keyed by event name.
     *
     * @var Event[]
     */
    private array $events = [];

	/**
	 * Event dispatcher instance.
	 *
	 * @var EventDispatcher
	 */
	private EventDispatcher $dispatcher;

	/**
	 * Constructor.
	 *
	 * @param EventDispatcher $dispatcher Event dispatcher to handle event execution.
	 */
	public function __construct( EventDispatcher $dispatcher ) {
		$this->dispatcher = $dispatcher;
	}

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
	 * @param Event $event Event object containing event metadata.
	 *
	 * @return bool True if event was registered; false if event with same name exists.
	 */
	public function register_event( Event $event ): bool {
		if ( isset( $this->events[ $event->name ] ) ) {
			return false;
		}

		$this->events[ $event->name ] = $event;
		return true;
	}

	/**
	 * Attach registered event callbacks to WordPress actions.
	 */
	public function attach_events(): void {
		foreach ($this->events as $event) {
            add_action(
                $event->hookName,
                function (...$args) use ($event) {
                    $this->dispatcher->dispatch($event, $args);
                },
                $event->priority,
                $event->argCount
            );
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
	public function get_event( string $eventName ): ?Event {
		return $this->events[ $eventName ] ?? null;
	}
}