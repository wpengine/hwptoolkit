<?php
/**
 * Plugin Name: HWP Previews
 * Description: A POC for headless previews.
 * Author: WP Engine
 * Author URI: https://wpengine.com/
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: hwp-previews
 * Domain Path: /languages
 * Version: 0.0.1
 * Requires PHP: 7.4
 * Requires at least: 6.7
 *
 * @package Hwp\Previews
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require __DIR__ . '/vendor/autoload.php';
}

define( 'HWP_PLUGIN_BASENAME', plugin_basename(__FILE__) );

// Initialize the plugin components after plugins are loaded.
add_action('plugins_loaded', function() {

	if ( ! is_php_version_compatible( '7.4' ) || ! is_wp_version_compatible( '6.7' ) ) {
		// TODO: Show some notices here and abort. Ideally we'd have a separate function for this and/or a standalone lib/class.
		return;
	}

	// Init the Plugin parts. Instantiate the singleton; this triggers dependency injection and hook registration.
	\HWP\Previews\Plugin::get_instance();
});
