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
					'description' => __( 'The ID of the webhook to delete', 'wp-graphql-webhooks' ),
				],
			],
			'outputFields' => [ 
				'deletedWebhookId' => [ 
					'type' => 'ID',
					'description' => __( 'The ID of the deleted webhook', 'wp-graphql-webhooks' ),
					'resolve' => function ($payload) {
						return $payload['deletedWebhookId'] ?? null;
					},
				],
				'success' => [ 
					'type' => 'Boolean',
					'description' => __( 'Whether the webhook was successfully deleted', 'wp-graphql-webhooks' ),
					'resolve' => function ($payload) {
						return $payload['success'] ?? false;
					},
				],
			],
			'mutateAndGetPayload' => function ($input, $context, $info) {
				// Capability check
				if ( ! current_user_can( 'manage_options' ) ) {
					throw new UserError( __( 'You do not have permission to delete webhooks.', 'wp-graphql-webhooks' ) );
				}

				if ( empty( $input['id'] ) ) {
					throw new UserError( __( 'The ID of the webhook to delete is required.', 'wp-graphql-webhooks' ) );
				}
				$post_id = is_numeric( $input['id'] ) ? (int) $input['id'] : 0;

				if ( $post_id <= 0 ) {
					throw new UserError( __( 'Invalid webhook ID.', 'wp-graphql-webhooks' ) );
				}

				$post = get_post( $post_id );
				if ( ! $post || $post->post_type !== 'graphql_webhook' ) {
					throw new UserError( __( 'Webhook not found.', 'wp-graphql-webhooks' ) );
				}

				// Delete the post (force delete to bypass trash)
				$deleted = wp_delete_post( $post_id, true );
				if ( ! $deleted ) {
					throw new UserError( __( 'Failed to delete webhook.', 'wp-graphql-webhooks' ) );
				}

				return [ 
					'deletedWebhookId' => $post_id,
					'success' => true,
				];
			},
		] );
	}
}