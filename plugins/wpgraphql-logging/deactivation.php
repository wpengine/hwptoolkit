<?php
/**
 * Deactivation Hook
 *
 * @package WPGraphQL\Logging
 *
 * @since 0.0.1
 */

declare(strict_types=1);

use WPGraphQL\Logging\Plugin;

/**
 * Runs when the plugin is deactivated.
 */
function wpgraphql_logging_deactivation_callback(): void {
	Plugin::deactivate();
	do_action( 'wpgraphql_logging_deactivate' );
}
