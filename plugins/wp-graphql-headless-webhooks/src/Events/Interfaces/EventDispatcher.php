<?php
/**
 * Interface for dispatching webhook events.
 *
 * This interface defines the contract for classes responsible for handling
 * the execution logic when a registered event is triggered. Implementations
 * should handle event filtering, payload generation, error handling, and
 * tracking/reporting as needed.
 *
 * @package WPGraphQL\Webhooks\Events\Interfaces
 */

namespace WPGraphQL\Webhooks\Events\Interfaces;

use WPGraphQL\Webhooks\Events\Event;

/**
 * Interface EventDispatcher
 *
 * Responsible for processing and dispatching events registered in the system.
 */
interface EventDispatcher {

    /**
     * Handle the given event with provided arguments.
     *
     * This method is called when a WordPress hook tied to the event is fired.
     * Implementations should:
     * - Determine if the event should be handled (e.g., via filters).
     * - Execute the event's callback to generate a payload.
     * - Handle any errors returned by the callback.
     * - Apply filters to the payload before tracking.
     * - Track or dispatch the event payload as appropriate.
     *
     * @param Event $event The event object containing metadata and callback.
     * @param array<int, mixed> $args The arguments passed from the WordPress hook.
     *
     * @return void
     */
    public function dispatch(Event $event, array $args): void;
}