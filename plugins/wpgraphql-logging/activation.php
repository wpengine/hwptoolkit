<?php
/**
 * Activation Hook
 *
 * @package WPGraphQL\Logging
 *
 * @since 0.0.1
 */

declare(strict_types=1);

/**
 * Runs when the plugin is activated.
 */
function wpgraphql_logging_activation_callback(): void {
	do_action( 'wpgraphql_logging_activate' );
}
