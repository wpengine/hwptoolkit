<?php
/**
 * Uninstall Headless Documents
 *
 * Drops the documents table, and every document in it, on each site when the
 * plugin is deleted.
 *
 * @package Headless\Documents
 */

declare(strict_types=1);

use Headless\Documents\Autoloader;
use Headless\Documents\Store\Document_Table;

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

require_once __DIR__ . '/src/Autoloader.php';
if ( ! Autoloader::autoload() ) {
	return;
}

if ( ! is_multisite() ) {
	Document_Table::uninstall();
}

if ( is_multisite() ) {
	$headless_documents_site_ids = get_sites(
		[
			'fields' => 'ids',
			'number' => 0,
		]
	);

	foreach ( $headless_documents_site_ids as $headless_documents_site_id ) {
		switch_to_blog( (int) $headless_documents_site_id ); // phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.switch_to_blog_switch_to_blog -- Only the database context is needed.
		Document_Table::uninstall();
		restore_current_blog();
	}
}

// Fire action for extensibility.
do_action( 'headless_documents_after_uninstall' );
