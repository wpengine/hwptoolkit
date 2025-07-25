<?php
/**
 * Activation Hook
 *
 * @package WPGraphql\Webhooks
 */

declare( strict_types = 1 );

/**
 * Runs when the plugin is activated.
 *
 */
function graphql_webhooks_activation_callback(): callable {
	return static function (): void {
		// Runs when the plugin is activated.
		do_action( 'graphql_webhooks_activate' );
	};
}