<?php
/**
 * Plugin Name: HWP Documents
 * Plugin URI: https://github.com/wpengine/hwptoolkit
 * GitHub Plugin URI: https://github.com/wpengine/hwptoolkit
 * Description: Store your app's JSON documents in WordPress over REST, with versioned writes that reject stale updates instead of overwriting them.
 * Author: WPEngine Headless OSS Team
 * Author URI: https://github.com/wpengine
 * Update URI: https://github.com/wpengine/hwptoolkit
 * Version: 1.0.0
 * Text Domain: hwp-documents
 * Domain Path: /languages
 * Requires at least: 6.0
 * Tested up to: 7.1
 * Requires PHP: 8.2
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package HWP\Documents
 */

declare(strict_types=1);

use HWP\Documents\Autoloader;
use HWP\Documents\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Load the autoloader.
require_once __DIR__ . '/src/Autoloader.php';
if ( ! Autoloader::autoload() ) {
	return;
}

if ( file_exists( __DIR__ . '/activation.php' ) ) {
	require_once __DIR__ . '/activation.php';
	register_activation_hook( __FILE__, 'hwp_documents_activation_callback' );
}

if ( file_exists( __DIR__ . '/deactivation.php' ) ) {
	require_once __DIR__ . '/deactivation.php';
	register_deactivation_hook( __FILE__, 'hwp_documents_deactivation_callback' );
}

if ( ! function_exists( 'hwp_documents_init' ) ) {
	/**
	 * Initializes the plugin.
	 */
	function hwp_documents_init(): void {
		hwp_documents_constants();
		Plugin::init();
	}
}

if ( ! function_exists( 'hwp_documents_constants' ) ) {
	/**
	 * Define plugin constants.
	 */
	function hwp_documents_constants(): void {
		if ( ! defined( 'HWP_DOCUMENTS_VERSION' ) ) {
			define( 'HWP_DOCUMENTS_VERSION', '1.0.0' );
		}

		if ( ! defined( 'HWP_DOCUMENTS_PLUGIN_DIR' ) ) {
			define( 'HWP_DOCUMENTS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
		}

		if ( ! defined( 'HWP_DOCUMENTS_PLUGIN_URL' ) ) {
			define( 'HWP_DOCUMENTS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
		}
	}
}

/**
 * Load plugin text domain.
 */
function hwp_documents_load_textdomain(): void {
	load_plugin_textdomain( 'hwp-documents', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}

add_action( 'init', 'hwp_documents_load_textdomain', 1, 0 );

/** @psalm-suppress HookNotFound */
add_action( 'plugins_loaded', 'hwp_documents_init', 15, 0 );
