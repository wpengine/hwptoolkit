<?php
/**
 * Webhook Object Type for WPGraphQL.
 *
 * @package WPGraphQL\Webhooks\Type
 */

namespace WPGraphQL\Webhooks\Type;

/**
 * Class Webhook
 *
 * Defines the GraphQL object type for a Webhook.
 */
class Webhook {

    /**
     * Registers the Webhook object type with the WPGraphQL schema.
     *
     * @return void
     */
    public static function register(): void {
        register_graphql_object_type( 'Webhook', [
            'description' => __( 'A Webhook configuration object.', 'wp-graphql-headless-webhooks' ),
            'fields'      => [
                'id'            => [
                    'type'        => 'ID',
                    'description' => __( 'The global ID of the webhook.', 'wp-graphql-headless-webhooks' ),
                    'resolve'     => function( $webhook ) {
                        return (string) $webhook->ID;
                    },
                ],
                'eventName'            => [
                    'type'        => 'ID',
                    'description' => __( 'The global ID of the webhook.', 'wp-graphql-headless-webhooks' ),
                    'resolve'     => function( $webhook ) {
                        return (string) $webhook->ID;
                    },
                ],
                'title'         => [
                    'type'        => 'String',
                    'description' => __( 'The title of the webhook.', 'wp-graphql-headless-webhooks' ),
                    'resolve'     => function( $webhook ) {
                        return get_the_title( $webhook );
                    },
                ],
                'status'        => [
                    'type'        => 'String',
                    'description' => __( 'The publication status of the webhook.', 'wp-graphql-headless-webhooks' ),
                    'resolve'     => function( $webhook ) {
                        return get_post_status( $webhook );
                    },
                ],
                'type'          => [
                    'type'        => 'String',
                    'description' => __( 'The type of the webhook.', 'wp-graphql-headless-webhooks' ),
                    'resolve'     => function( $webhook ) {
                        return get_post_meta( $webhook->ID, 'type', true );
                    },
                ],
                'configuration' => [
                    'type'        => 'String',
                    'description' => __( 'The configuration payload for the webhook.', 'wp-graphql-headless-webhooks' ),
                    'resolve'     => function( $webhook ) {
                        return get_post_meta( $webhook->ID, 'configuration', true );
                    },
                ],
            ],
        ]);
    }
}