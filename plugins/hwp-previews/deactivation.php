<?php
/**
 * Deactivation Hook
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
 * Runs when the plugin is deactivated.
 */
function hwp_previews_deactivation_callback(): void {
	do_action( 'hwp_previews_deactivate' );
}
