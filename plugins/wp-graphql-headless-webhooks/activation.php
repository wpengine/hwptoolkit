<?php
/**
 * Activation Hook
 *
 * @package WPGraphql\Webhooks
 * @since 0.0.1
 */

declare( strict_types = 1 );

namespace WPGraphQL\Webhooks;

/**
 * Runs when the plugin is activated.
 *
 * @since 0.0.1
 */
function graphql_headless_webhooks_activation_callback(): callable {
	return static function (): void {
		// Runs when the plugin is activated.
		do_action( 'graphql_headless_webhooks_activate' );
	};
}