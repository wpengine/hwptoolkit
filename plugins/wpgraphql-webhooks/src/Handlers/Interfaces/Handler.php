<?php
namespace WPGraphQL\Webhooks\Handlers\Interfaces;

use WPGraphQL\Webhooks\Entity\Webhook;

/**
 * Interface Handler
 *
 * Defines the contract for event handlers in the WPGraphQL Webhooks system.
 * Implementations should process the given payload when an event is triggered.
 *
 * @package WPGraphQL\Webhooks\Handlers\Interfaces
 */
interface Handler {
    /**
     * Handle the event payload for a specific webhook.
     *
     * @param Webhook $webhook The Webhook entity instance.
     * @param array   $payload The event payload data.
     *
     * @return void
     */
    public function handle(Webhook $webhook, array $payload): void;
}