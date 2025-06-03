<?php
namespace WPGraphQL\Webhooks\Events\Interfaces;

/**
 * Interface EventManager
 *
 * Defines the contract for managing and registering event hooks in the WPGraphQL Webhooks system.
 * Implementations should set up the necessary WordPress hooks to listen for relevant events and trigger webhooks.
 *
 * @package WPGraphQL\Webhooks\Events\Interfaces
 */
interface EventManager {
    
    /**
     * Register WordPress action and filter hooks for webhook events.
     *
     * This method should bind handlers to the desired WordPress events that 
     * the webhook system listens to and dispatches payloads for.
     *
     * @return void
     */
    public function register_hooks(): void;
}