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
			'description' => __( 'A Webhook configuration object.', 'wp-graphql-webhooks' ),
			'fields' => [ 
				'id' => [ 
					'type' => 'ID',
					'description' => __( 'The global ID of the webhook.', 'wp-graphql-webhooks' ),
					'resolve' => function ($webhook) {
						return (string) $webhook->ID;
					},
				],
				'eventTrigger' => [ 
					'type' => 'String',
					'description' => __( 'The event hook name that triggers this webhook.', 'wp-graphql-webhooks' ),
					'resolve' => function ($webhook) {
						return get_post_meta( $webhook->ID, '_event_trigger', true );
					},
				],
				'title' => [ 
					'type' => 'String',
					'description' => __( 'The title of the webhook.', 'wp-graphql-webhooks' ),
					'resolve' => function ($webhook) {
						return get_the_title( $webhook );
					},
				],
				'enabled' => [ 
					'type' => 'Boolean',
					'description' => __( 'Whether the webhook is enabled.', 'wp-graphql-webhooks' ),
					'resolve' => function ($webhook) {
						return (bool) get_post_meta( $webhook->ID, '_enabled', true );
					},
				],
				'security' => [ 
					'type' => 'String',
					'description' => __( 'Security information for the webhook.', 'wp-graphql-webhooks' ),
					'resolve' => function ($webhook) {
						return get_post_meta( $webhook->ID, '_security', true );
					},
				],
				'handlerClass' => [ 
					'type' => 'String',
					'description' => __( 'The handler class used for dispatching.', 'wp-graphql-webhooks' ),
					'resolve' => function ($webhook) {
						return get_post_meta( $webhook->ID, '_handler_class', true );
					},
				],
				'handlerConfig' => [ 
					'type' => 'String',
					'description' => __( 'Configuration for the handler.', 'wp-graphql-webhooks' ),
					'resolve' => function ($webhook) {
						return get_post_meta( $webhook->ID, '_handler_config', true );
					},
				],
			],
		] );
	}
}