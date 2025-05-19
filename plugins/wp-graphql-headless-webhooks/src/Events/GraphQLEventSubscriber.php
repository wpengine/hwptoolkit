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
        $registrations = [];
        
        foreach ($this->events as $event) {
            $registrations[] = [
                'name' => $event['name'],
                'hook_name' => $event['hook'],
                'callback' => [$this, $event['callback']],
                'priority' => $event['priority'] ?? 10,
                'arg_count' => $event['arg_count'] ?? 1,
            ];
        }
        
        return $registrations;
    }

    /**
     * Subscribes to WPGraphQL tracked events.
     *
     * Hooks into wpgraphql_event_tracked_{eventName} actions and dispatches to handler methods.
     */
    public function subscribe(): void {
        foreach ($this->events as $event) {    
            // Register the event for tracking
            $handlerMethodName = $this->getHandlerMethodName($event['name']);
            if (method_exists($this, $handlerMethodName)) {
                add_action(
                    "wpgraphql_event_tracked_{$event['name']}",
                    [$this, $handlerMethodName]
                );
            }
        }
    }

    /**
     * Converts an event name like 'post_saved' to handler method name 'handlePostSavedEvent'.
     *
     * @param string $eventName
     * @return string
     */
    protected function getHandlerMethodName(string $eventName): string {
        $parts = explode('_', $eventName);
        $camelCase = array_map('ucfirst', $parts);
        $eventNameCamel = implode('', $camelCase);
        
        return 'handle' . $eventNameCamel . 'Event';
    }
}