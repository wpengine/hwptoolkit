<?php

declare(strict_types=1);

/**
 * Deactivation Hook
 *
 * @package WPGraphQL\Logging
 *
 * @since 0.0.1
 */

/**
 * Runs when the plugin is deactivated.
 */
function wpgraphql_logging_deactivation_callback(): void {
	do_action( 'wpgraphql_logging_deactivate' );
}
