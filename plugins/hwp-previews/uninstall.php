<?php
/**
 * Uninstall HWP Previews
 *
 * Deletes all plugin data when the plugin is uninstalled,
 * only if HWP_PREVIEWS_UNINSTALL_PLUGIN constant is defined.
 *
 * @package HWP\Previews
 */

declare(strict_types=1);

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Only proceed if the uninstall constant is defined.
if ( ! defined( 'HWP_PREVIEWS_UNINSTALL_PLUGIN' ) || ! HWP_PREVIEWS_UNINSTALL_PLUGIN ) {
	exit;
}

// Define constants if not already defined.
if ( ! defined( 'HWP_PREVIEWS_SETTINGS_KEY' ) ) {
	define( 'HWP_PREVIEWS_SETTINGS_KEY', 'hwp_previews_settings' );
}

// Delete plugin settings.
delete_option( HWP_PREVIEWS_SETTINGS_KEY );

// Fire action for extensibility.
do_action( 'hwp_previews_after_uninstall' );

