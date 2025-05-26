<?php
/**
 * Event Dispatcher for WPGraphQL Webhooks.
 *
 * Responsible for handling the execution logic when registered events are triggered.
 *
 * @package WPGraphQL\Webhooks\Events
 */

namespace WPGraphQL\Webhooks\Events;

use WPGraphQL\Webhooks\Events\Interfaces\EventDispatcher;
use WPGraphQL\Webhooks\Events\Event;

/**
 * Class EventDispatcher
 *
 * Handles event callbacks, filtering, error handling, and tracking.
 */
class GraphQLEventDispatcher implements EventDispatcher {

    /**
     * Event monitor instance for tracking events.
     *
     * @var EventMonitor
     */
    private EventMonitor $monitor;

    /**
     * EventDispatcher constructor.
     *
     * @param EventMonitor $monitor Event monitor to track events.
     */
    public function __construct(EventMonitor $monitor) {
        $this->monitor = $monitor;
    }

    /**
     * Handle the given event with provided arguments.
     *
     * @param Event $event The event object containing metadata and callback.
     * @param array<int, mixed> $args Arguments passed from the WordPress hook.
     *
     * @return void
     */
    public function dispatch(Event $event, array $args): void {
        // Allow skipping event handling via filter
        $shouldHandle = apply_filters("graphql_webhooks_event_should_handle_{$event->name}", null, ...$args);
        if ($shouldHandle === false) {
            return;
        }
        $payload = null;
        if (is_callable($event->callback)) {
            $payload = call_user_func($event->callback, ...$args);
        }
        if (is_wp_error($payload)) {
            do_action("graphql_webhooks_event_error_{$event->name}", $payload, $args);
            return;
        }
        $payload = apply_filters("graphql_webhooks_event_payload_{$event->name}", $payload, $args);

        // Track the event payload via the EventMonitor
        $this->monitor->track($event->name, $payload);
    }
}