<?php

declare(strict_types=1);

/**
 * Deactivation Hook
 *
 * @package WPGraphQL\Velocity
 *
 * @since 0.0.1
 */

/**
 * Runs when the plugin is deactivated.
 */
function wpgraphql_velocity__deactivation_callback(): callable {
	return static function (): void {
		do_action( 'wpgraphql_velocity__deactivate' );
	};
}
