<?php
/**
 * Activation Hook
 *
 * @package HWP\Documents
 *
 * @since 0.1.0
 */

declare(strict_types=1);

use HWP\Documents\Store\Document_Table;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Runs when the plugin is activated.
 *
 * Creates the documents table for the current site. On a network activation,
 * each other site creates its table on its first request.
 */
function hwp_documents_activation_callback(): void {
	Document_Table::maybe_install();
	do_action( 'hwp_documents_activate' );
}
