<?php
/**
 * RootQuery fields for WPGraphQL Webhooks.
 *
 * @package WPGraphQL\Webhooks\Fields
 */

namespace WPGraphQL\Webhooks\Fields;

use GraphQL\Type\Definition\Type;

/**
 * Class RootQuery
 *
 * Registers custom fields to the GraphQL RootQuery type.
 */
class RootQuery {

    /**
     * Registers the webhooks and webhook fields on the GraphQL RootQuery.
     *
     * @return void
     */
    public static function register(): void {
        // Register the "webhooks" field (list of webhooks)
        register_graphql_field( 'RootQuery', 'webhooks', [
            'type'        => [ 'list_of' => 'Webhook' ],
            'description' => __( 'List all registered webhooks.', 'wpgraphql-webhooks' ),
            'resolve'     => function() {
                $query = new \WP_Query([
                    'post_type'      => 'graphql_webhook',
                    'posts_per_page' => -1,
                    'post_status'    => [ 'publish', 'draft', 'private' ],
                ]);

                return $query->posts;
            },
        ]);

        // Register the "webhook" field (fetch single webhook by ID)
        register_graphql_field( 'RootQuery', 'webhook', [
            'type'        => 'Webhook',
            'description' => __( 'Fetch a webhook by ID.', 'wpgraphql-webhooks' ),
            'args'        => [
                'id' => [
                    'type'        => Type::nonNull( Type::id() ),
                    'description' => __( 'The global ID of the webhook to retrieve.', 'wpgraphql-webhooks' ),
                ],
            ],
            'resolve'     => function( $root, $args ) {
                $post = get_post( $args['id'] );
                if ( $post && $post->post_status !== 'trash' ) {
                    return $post;
                }
                return null;
            },
        ]);
    }
}