<?php
/**
 * Activation Hook
 *
 * @package HWP\Previews
 *
 * @since 0.0.1
 */

declare(strict_types=1);

/**
 * Runs when the plugin is activated.
 */
function hwp_previews_activation_callback( bool $_network_wide = false ): void { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
	do_action( 'hwp_previews_activate' );
}
