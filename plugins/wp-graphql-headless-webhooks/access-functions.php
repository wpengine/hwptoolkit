<?php
/**
 * Access functions for WP GraphQL Headless Webhooks.
 *
 * @package WPGraphQL\Webhooks
 */

declare(strict_types=1);

use WPGraphQL\Webhooks\WebhookRegistry;

/**
 * Registers a new webhook type.
 *
 * This function schedules a webhook type registration callback to be executed when
 * the `graphql_register_webhooks` action is fired (during `WebhookRegistry::init()`).
 *
 * @param string $type Webhook type identifier.
 * @param array  $args {
 *     Args for the webhook type.
 *
 *     @type string $label       Human-readable label for the webhook type (default: $type).
 *     @type string $description Description of the webhook type (default: '').
 *     @type array  $config      Optional. Additional configuration for the webhook.
 * }
 */
if ( ! function_exists( 'register_webhook_type' ) ) {
	function register_webhook_type( $type, $args = [] ) {
		if ( did_action( 'graphql_register_webhooks' ) ) {
			_doing_it_wrong(
				'register_webhook_type',
				esc_html__( 'Call this before WebhookRegistry::init', 'wp-graphql-headless-webhooks' ),
				'0.1.0'
			);
		}

		add_action(
			'graphql_register_webhooks',
			static function (WebhookRegistry $webhook_registry) use ($type, $args) {
				$webhook_registry->register_webhook_type( $type, $args );
			}
		);
	}
}

/**
 * Creates a new webhook
 *
 * @param string $type    Webhook type identifier.
 * @param string $name    Webhook name/title.
 * @param array  $config  Webhook configuration.
 * @return int|\WP_Error Post ID of the new webhook or error.
 */
if ( ! function_exists( 'create_webhook' ) ) {
	function create_webhook( $type, $name, $config = [] ) {
		return WebhookRegistry::instance()->create_webhook( $type, $name, $config );
	}
}

/**
 * Gets a registered webhook type
 *
 * @param string $type Webhook type identifier.
 * @return array|null Webhook type configuration or null if not found.
 */
if ( ! function_exists( 'get_webhook_type' ) ) {
	function get_webhook_type( $type ) {
		return WebhookRegistry::instance()->get_webhook_type( $type );
	}
}

/**
 * Gets all registered webhook types
 *
 * @return array All registered webhook types.
 */
if ( ! function_exists( 'get_webhook_types' ) ) {
	function get_webhook_types() {
		return WebhookRegistry::instance()->get_webhook_types();
	}
}