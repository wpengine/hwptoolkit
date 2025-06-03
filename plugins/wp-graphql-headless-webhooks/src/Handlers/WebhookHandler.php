<?php
namespace WPGraphQL\Webhooks\Handlers;

use WPGraphQL\Webhooks\Entity\Webhook;
use WPGraphQL\Webhooks\Handlers\Interfaces\Handler;

/**
 * Class WebhookHandler
 *
 * Sends the webhook to the configured URL when an event is triggered.
 */
class WebhookHandler implements Handler {

    /**
     * Handle the event payload for a specific webhook.
     *
     * @param Webhook $webhook The Webhook entity instance.
     * @param array   $payload The event payload data.
     *
     * @return void
     */
    public function handle( Webhook $webhook, array $payload ): void {
        $args = [
            'headers'  => $webhook->headers ?: [ 'Content-Type' => 'application/json' ],
            'timeout'  => 5,
            'blocking' => false,
        ];

        if ( strtoupper( $webhook->method ) === 'GET' ) {
            $url = add_query_arg( $payload, $webhook->url );
            $args['method'] = 'GET';
        } else {
            $url = $webhook->url;
            $args['method'] = 'POST';
            $args['body'] = wp_json_encode( $payload );
            if ( empty( $args['headers']['Content-Type'] ) ) {
                $args['headers']['Content-Type'] = 'application/json';
            }
        }

        /**
         * Filter the payload before sending.
         */
        $payload = apply_filters( 'graphql_webhooks_payload', $payload, $webhook );
        wp_remote_request( $url, $args );
        do_action( 'graphql_webhooks_sent', $webhook, $payload );
    }
}