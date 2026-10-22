<?php
/**
 * Deactivation Hook
 *
 * Documents are kept when the plugin is deactivated. Deleting the plugin
 * drops them; see uninstall.php.
 *
 * @package Headless\Documents
 *
 * @since 0.1.0
 */

declare(strict_types=1);

/**
 * Runs when the plugin is deactivated.
 */
function headless_documents_deactivation_callback(): void {
	do_action( 'headless_documents_deactivate' );
}
