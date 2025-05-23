<?php

declare(strict_types=1);

/**
 * Deactivation Hook
 *
 * @package HWP\Previews
 */

/**
 * Runs when the plugin is deactivated.
 */
function hwp_previews_deactivation_callback(): callable {
	return static function (): void {
		do_action( 'hwp_previews_deactivate' );
	};
}
