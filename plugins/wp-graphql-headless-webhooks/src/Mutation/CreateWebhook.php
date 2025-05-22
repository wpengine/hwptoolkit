<?php
/**
 * GraphQL Mutation: createWebhook
 *
 * @package WPGraphQL\Webhooks\Mutation
 */

namespace WPGraphQL\Webhooks\Mutation;

use GraphQL\Error\UserError;

/**
 * Class CreateWebhook
 *
 * Registers the createWebhook GraphQL mutation.
 */
class CreateWebhook {

    /**
     * Registers the createWebhook mutation with the GraphQL schema.
     *
     * @return void
     */
    public static function register(): void {
        register_graphql_mutation( 'createWebhook', [
            'inputFields'         => [
                'clientMutationId' => [
                    'type'        => 'String',
                    'description' => __( 'An identifier for the client performing the mutation.', 'wp-graphql-headless-webhooks' ),
                ],
                'type'          => [
                    'type'        => 'String',
                    'description' => __( 'The type of the webhook.', 'wp-graphql-headless-webhooks' ),
                ],
                'configuration' => [
                    'type'        => 'String',
                    'description' => __( 'The configuration payload for the webhook.', 'wp-graphql-headless-webhooks' ),
                ],
            ],
            'outputFields'        => [
                'clientMutationId' => [
                    'type'        => 'String',
                    'description' => __( 'The same clientMutationId that was provided in the mutation input.', 'wp-graphql-headless-webhooks' ),
                    'resolve'     => function( $payload ) {
                        return $payload['clientMutationId'] ?? null;
                    },
                ],
                'webhook' => [
                    'type'        => 'Webhook',
                    'description' => __( 'The newly created webhook.', 'wp-graphql-headless-webhooks' ),
                    'resolve'     => function( $payload ) {
                        return ! empty( $payload['webhook_id'] ) ? get_post( $payload['webhook_id'] ) : null;
                    },
                ],
            ],
            'mutateAndGetPayload' => function( $input ) {
                if ( ! current_user_can( 'publish_posts' ) ) {
                    throw new UserError( __( 'You do not have permission to create webhooks.', 'wp-graphql-headless-webhooks' ) );
                }
                $type          = sanitize_text_field( $input['type'] ?? '' );
                $configuration = sanitize_textarea_field( $input['configuration'] ?? '' );
                $clientMutationId   = sanitize_text_field( $input['clientMutationId'] ?? '' );

                $post_id = wp_insert_post([
                    'post_type'   => 'graphql_webhook',
                ]);

                if ( is_wp_error( $post_id ) ) {
                    throw new UserError( $post_id->get_error_message() );
                }
                if ( $type ) {
                    update_post_meta( $post_id, 'type', $type );
                }
                if ( $configuration ) {
                    update_post_meta( $post_id, 'configuration', $configuration );
                }

                return [
                    'webhook_id'       => $post_id,
                    'clientMutationId' => $clientMutationId,
                ];
            },
        ]);
    }
}