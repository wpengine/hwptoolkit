<?php

declare(strict_types=1);

/**
 * Deactivation Hook
 *
 * @package HWP\Previews
 *
 * @since 0.0.1
 */

/**
 * Runs when the plugin is deactivated.
 */
function hwp_previews_deactivation_callback(): callable {
	return static function (): void {
		do_action( 'hwp_previews_deactivate' );
	};
}
