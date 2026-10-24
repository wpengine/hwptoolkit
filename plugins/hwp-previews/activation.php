<?php
/**
 * Activation Hook
 *
 * @package HWP\Previews
 *
 * @since 0.0.1
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Runs when the plugin is activated.
 */
function hwp_previews_activation_callback(): void {
	do_action( 'hwp_previews_activate' );
}
