<?php
namespace WPGraphQL\Webhooks\Events\Interfaces;

use WPGraphQL\Webhooks\Events\Event;

/**
 * Interface EventRegistry
 *
 * Defines the contract for event registries managing webhook events.
 */
interface EventRegistry {

    /**
     * Initialize the event registry.
     *
     * Typically triggers event registration and attaches events to WordPress hooks.
     *
     * @return void
     */
    public function init(): void;

    /**
     * Register a GraphQL event.
     *
     * @param Event $event The event object containing metadata and callback.
     *
     * @return bool True if registration succeeded, false if event already exists.
     */
    public function register_event(Event $event): bool;

    /**
     * Get all registered events.
     *
     * @return Event[]
     */
    public function get_events(): array;

    /**
     * Get a specific event by name.
     *
     * @param string $eventName The unique name of the event.
     *
     * @return Event|null The event object if found, or null.
     */
    public function get_event(string $eventName): ?Event;
}