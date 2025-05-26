<?php
namespace WPGraphQL\Webhooks\Events\Interfaces;

interface EventSubscriber {
    /**
     * Subscribe to tracked events.
     *
     */
    public function subscribe(): void;
}