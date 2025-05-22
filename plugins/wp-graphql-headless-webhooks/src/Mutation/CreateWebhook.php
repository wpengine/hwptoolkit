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
			'inputFields' => [ 
				'title' => [ 
					'type' => 'String',
					'description' => __( 'The title of the webhook', 'wp-graphql-headless-webhooks' ),
				],
				'content' => [ 
					'type' => 'String',
					'description' => __( 'The content/description of the webhook', 'wp-graphql-headless-webhooks' ),
				],
				'eventTrigger' => [ 
					'type' => 'String',
					'description' => __( 'The event hook name that triggers this webhook', 'wp-graphql-headless-webhooks' ),
				],
				'enabled' => [ 
					'type' => 'Boolean',
					'description' => __( 'Whether the webhook is enabled', 'wp-graphql-headless-webhooks' ),
				],
				'security' => [ 
					'type' => 'String',
					'description' => __( 'Security information for the webhook', 'wp-graphql-headless-webhooks' ),
				],
				'handlerClass' => [ 
					'type' => 'String',
					'description' => __( 'The handler class used for dispatching', 'wp-graphql-headless-webhooks' ),
				],
				'handlerConfig' => [ 
					'type' => 'String',
					'description' => __( 'Configuration for the handler, JSON encoded', 'wp-graphql-headless-webhooks' ),
				],
				'status' => [ 
					'type' => 'String',
					'description' => __( 'Post status, e.g. PUBLISH or DRAFT', 'wp-graphql-headless-webhooks' ),
					'defaultValue' => 'publish',
				],
			],
			'outputFields' => [ 
				'webhook' => [ 
					'type' => 'Webhook',
					'description' => __( 'The created webhook', 'wp-graphql-headless-webhooks' ),
					'resolve' => function ($payload) {
						return get_post( $payload['webhookId'] );
					},
				],
			],
			'mutateAndGetPayload' => function ($input, $context, $info) {
				// Check user capabilities
				if ( ! current_user_can( 'edit_posts' ) ) {
					throw new UserError( __( 'You do not have permission to create webhooks.', 'wp-graphql-headless-webhooks' ) );
				}

				// Prepare post data
				$post_data = [ 
					'post_type' => 'graphql_webhook',
					'post_title' => sanitize_text_field( $input['title'] ?? '' ),
					'post_content' => sanitize_textarea_field( $input['content'] ?? '' ),
					'post_status' => sanitize_text_field( $input['status'] ?? 'publish' ),
				];

				// Insert the post
				$post_id = wp_insert_post( $post_data );

				if ( is_wp_error( $post_id ) ) {
					throw new UserError( __( 'Failed to create webhook.', 'wp-graphql-headless-webhooks' ) );
				}

				// Save meta fields
				if ( isset( $input['eventTrigger'] ) ) {
					update_post_meta( $post_id, '_event_trigger', sanitize_text_field( $input['eventTrigger'] ) );
				}
				if ( isset( $input['enabled'] ) ) {
					update_post_meta( $post_id, '_enabled', (bool) $input['enabled'] );
				}
				if ( isset( $input['security'] ) ) {
					update_post_meta( $post_id, '_security', sanitize_text_field( $input['security'] ) );
				}
				if ( isset( $input['handlerClass'] ) ) {
					update_post_meta( $post_id, '_handler_class', sanitize_text_field( $input['handlerClass'] ) );
				}
				if ( isset( $input['handlerConfig'] ) ) {
					update_post_meta( $post_id, '_handler_config', sanitize_text_field( $input['handlerConfig'] ) );
				}

				return [ 
					'webhookId' => $post_id,
				];
			},
		] );
	}
}