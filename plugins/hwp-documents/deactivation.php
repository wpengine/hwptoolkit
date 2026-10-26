<?php
/**
 * Deactivation Hook
 *
 * Documents are kept when the plugin is deactivated. Deleting the plugin
 * drops them; see uninstall.php.
 *
 * @package HWP\Documents
 *
 * @since 0.1.0
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Runs when the plugin is deactivated.
 */
function hwp_documents_deactivation_callback(): void {
	do_action( 'hwp_documents_deactivate' );
}
