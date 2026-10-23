<?php
/**
 * GraphQL Mutation: deleteWebhook
 *
 * @package WPGraphQL\Webhooks\Mutation
 */

namespace WPGraphQL\Webhooks\Mutation;

use GraphQL\Error\UserError;

/**
 * Class DeleteWebhook
 *
 * Registers the deleteWebhook GraphQL mutation.
 */
class DeleteWebhook {

	/**
	 * Registers the deleteWebhook mutation with the GraphQL schema.
	 *
	 * @return void
	 */
	public static function register(): void {
		register_graphql_mutation( 'deleteWebhook', [ 
			'inputFields' => [ 
				'id' => [ 
					'type' => 'ID',
					'description' => __( 'The ID of the webhook to delete', 'wpgraphql-webhooks' ),
				],
			],
			'outputFields' => [ 
				'deletedWebhookId' => [ 
					'type' => 'ID',
					'description' => __( 'The ID of the deleted webhook', 'wpgraphql-webhooks' ),
					'resolve' => function ($payload) {
						return $payload['deletedWebhookId'] ?? null;
					},
				],
				'success' => [ 
					'type' => 'Boolean',
					'description' => __( 'Whether the webhook was successfully deleted', 'wpgraphql-webhooks' ),
					'resolve' => function ($payload) {
						return $payload['success'] ?? false;
					},
				],
			],
			'mutateAndGetPayload' => function ($input, $context, $info) {
				// Capability check
				if ( ! current_user_can( 'manage_options' ) ) {
					throw new UserError( __( 'You do not have permission to delete webhooks.', 'wpgraphql-webhooks' ) );
				}

				if ( empty( $input['id'] ) ) {
					throw new UserError( __( 'The ID of the webhook to delete is required.', 'wpgraphql-webhooks' ) );
				}
				$post_id = is_numeric( $input['id'] ) ? (int) $input['id'] : 0;

				if ( $post_id <= 0 ) {
					throw new UserError( __( 'Invalid webhook ID.', 'wpgraphql-webhooks' ) );
				}

				$post = get_post( $post_id );
				if ( ! $post || $post->post_type !== 'graphql_webhook' ) {
					throw new UserError( __( 'Webhook not found.', 'wpgraphql-webhooks' ) );
				}

				// Delete the post (force delete to bypass trash)
				$deleted = wp_delete_post( $post_id, true );
				if ( ! $deleted ) {
					throw new UserError( __( 'Failed to delete webhook.', 'wpgraphql-webhooks' ) );
				}

				return [ 
					'deletedWebhookId' => $post_id,
					'success' => true,
				];
			},
		] );
	}
}