<?php

namespace WPGraphQL\Webhooks\Events;

use WPGraphQL\Webhooks\Events\Interfaces\EventSubscriber;

/**
 * Abstract base class for GraphQL event subscribers.
 *
 * Subclasses declare events with their callbacks and handle tracked events.
 */
abstract class GraphQLEventSubscriber implements EventSubscriber {

    /**
     * Array of event registrations.
     *
     * Each event must specify:
     *  - 'name' (string): GraphQL event name (matches wpgraphql_event_tracked_{name})
     *  - 'hook' (string): WordPress hook name (e.g. 'save_post')
     *  - 'callback' (callable): Callback hooked to the native WP hook
     *  - 'priority' (int, optional): Hook priority (default 10)
     *  - 'arg_count' (int, optional): Number of hook arguments (default 1)
     *
     * Example:
     * [
     *     [
     *         'name' => 'post_saved',
     *         'hook' => 'save_post',
     *         'callback' => [ $this, 'onSavePost' ],
     *         'priority' => 10,
     *         'arg_count' => 3,
     *     ],
     * ]
     *
     * @var array<int, array<string, mixed>>
     */
    protected array $events = [];

    /**
     * Returns the event registrations with callbacks.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getEventRegistrations(): array {
        return $this->events;
    }

    /**
     * Subscribes to WPGraphQL tracked events.
     *
     * Hooks into wpgraphql_event_tracked_{eventName} actions and dispatches to handler methods.
     */
    public function subscribe(): void {
        foreach ($this->events as $event) {
            if (!isset($event['name'])) {
                continue;
            }

            $eventName = $event['name'];
            add_action("wpgraphql_event_tracked_{$eventName}", function ($payload) use ($eventName) {
                $this->handleEvent($eventName, $payload);
            });
        }
    }

    /**
     * Dispatch the tracked event payload to the corresponding handler method.
     *
     * Handler methods are named `handle{StudlyEventName}`, e.g. 'handlePostSaved'.
     *
     * @param string $eventName Name of the event.
     * @param mixed  $payload   Payload passed from the event callback.
     */
    protected function handleEvent(string $eventName, $payload): void {
        $handlerMethod = $this->getHandlerMethodName($eventName);
        var_dump($handlerMethod);
        if (method_exists($this, $handlerMethod)) {
            $this->{$handlerMethod}($payload);
        }
    }

    /**
     * Converts an event name like 'post_saved' to handler method name 'handlePostSavedEvent'.
     *
     * @param string $eventName
     * @return string
     */
    protected function getHandlerMethodName(string $eventName): string {
        $studly = str_replace(' ', '', ucwords(str_replace('_', ' ', $eventName)));
        return 'handle' . $studly . 'Event';
    }
}