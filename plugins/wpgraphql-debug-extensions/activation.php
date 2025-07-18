<?php
/**
 * Activation Hook
 *
 * @package WPGraphql\Debug
 */

declare( strict_types = 1 );

/**
 * Runs when the plugin is activated.
 *
 */
function graphql_debug_activation_callback(): callable {
	return static function (): void {
		do_action( 'graphql_debug_activate' );
	};
}