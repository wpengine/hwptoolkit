<?php
/**
 * Uninstall HWP Documents
 *
 * Drops the documents table, and every document in it, on each site when the
 * plugin is deleted, but only if HWP_DOCUMENTS_UNINSTALL_PLUGIN is
 * defined. The documents are the app's data, so they're kept by default.
 *
 * @package HWP\Documents
 */

declare(strict_types=1);

use HWP\Documents\Autoloader;
use HWP\Documents\Store\Document_Table;

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Only proceed if the uninstall constant is defined.
if ( ! defined( 'HWP_DOCUMENTS_UNINSTALL_PLUGIN' ) || ! HWP_DOCUMENTS_UNINSTALL_PLUGIN ) {
	return;
}

require_once __DIR__ . '/src/Autoloader.php';
if ( ! Autoloader::autoload() ) {
	return;
}

if ( ! is_multisite() ) {
	Document_Table::uninstall();
}

if ( is_multisite() ) {
	$hwp_documents_site_ids = get_sites(
		[
			'fields' => 'ids',
			'number' => 0,
		]
	);

	foreach ( $hwp_documents_site_ids as $hwp_documents_site_id ) {
		switch_to_blog( (int) $hwp_documents_site_id ); // phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.switch_to_blog_switch_to_blog -- Only the database context is needed.
		Document_Table::uninstall();
		restore_current_blog();
	}
}

// Fire action for extensibility.
do_action( 'hwp_documents_after_uninstall' );
