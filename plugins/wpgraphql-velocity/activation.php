<?php
/**
 * Activation Hook
 *
 * @package WPGraphQL\Velocity
 *
 * @since 0.0.1
 */

declare(strict_types=1);

/**
 * Runs when the plugin is activated.
 */
function wpgraphql_velocity__activation_callback(): callable {
	return static function (): void {
		do_action( 'wpgraphql_velocity__activate' );
	};
}
