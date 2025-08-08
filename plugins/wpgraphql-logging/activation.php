<?php
/**
 * Activation Hook
 *
 * @package WPGraphQL\Logging
 *
 * @since 0.0.1
 */

declare(strict_types=1);

use WPGraphQL\Logging\Plugin;

/**
 * Runs when the plugin is activated.
 */
function wpgraphql_logging_activation_callback(): void {
	Plugin::activate();
	do_action( 'wpgraphql_logging_activate' );
}
