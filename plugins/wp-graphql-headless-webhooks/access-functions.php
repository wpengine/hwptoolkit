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
 * @param string                        $type Webhook type identifier.
 * @param array<string, mixed>          $args {
 *     Args for the webhook type.
 *
 *     @type string              $label       Human-readable label for the webhook type (default: $type).
 *     @type string              $description Description of the webhook type (default: '').
 *     @type array<string>       $events      Optional. List of events to register.
 *     @type array<string,mixed> $config      Optional. Additional configuration for the webhook.
 * }
 */
if ( ! function_exists( 'register_webhook_type' ) ) {
	/** @phpstan-ignore missingType.iterableValue */
	function register_webhook_type(string $type, array $args = []): void {
		/** @psalm-suppress HookNotFound */
		if ( did_action( 'graphql_register_webhooks' ) > 0 ) {
			_doing_it_wrong(
				'register_webhook_type',
				esc_html__( 'Call this before WebhookRegistry::init', 'wp-graphql-headless-webhooks' ),
				'0.1.0'
			);
		}
		/** @psalm-suppress HookNotFound */
		add_action(
			'graphql_register_webhooks',
			static function (WebhookRegistry $webhook_registry) use ($type, $args): void {
				if ( ! isset( $args['events'] ) || ! is_array( $args['events'] ) ) {
					$args['events'] = [];
				}
				// Use explicit boolean condition
				if (count($args['events']) > 0) {
					foreach ( $args['events'] as $event_type ) {
						if ( function_exists( 'register_graphql_event' ) ) {
							register_graphql_event( $event_type );
						}
					}
				}
				$webhook_registry->register_webhook_type( $type, $args );
			}
		);
	}

}

/**
 * Creates a new webhook
 *
 * @param string                $type   Webhook type identifier.
 * @param string                $name   Webhook name/title.
 * @param array<string, mixed>  $config Webhook configuration.
 *
 * @return int|\WP_Error Post ID of the new webhook or error.
 */
if ( ! function_exists( 'create_webhook' ) ) {

	/**
	 * @return \WP_Error|int
	 */
	function create_webhook(string $type, string $name, array $config = []) { // @phpstan-ignore missingType.iterableValue
		return WebhookRegistry::instance()->create_webhook( $type, $name, $config );
	}

}

/**
 * Gets a registered webhook type
 *
 * @param string $type Webhook type identifier.
 *
 * @return array<string, mixed>|null Webhook type configuration or null if not found.
 */
if ( ! function_exists( 'get_webhook_type' ) ) {

	/** @phpstan-ignore missingType.iterableValue */
	function get_webhook_type(string $type): ?array {
		return WebhookRegistry::instance()->get_webhook_type( $type );
	}

}

/**
 * Gets all registered webhook types
 *
 * @return array<string, array<string, mixed>> All registered webhook types.
 */
if ( ! function_exists( 'get_webhook_types' ) ) {

	/** @phpstan-ignore missingType.iterableValue */
	function get_webhook_types(): array {
		return WebhookRegistry::instance()->get_webhook_types();
	}

}