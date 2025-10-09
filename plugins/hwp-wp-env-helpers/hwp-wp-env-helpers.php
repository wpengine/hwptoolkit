<?php
/**
 * Plugin Name: HWP WP-Env Helpers
 * Plugin URI: https://github.com/wpengine/hwptoolkit
 * Description: Fixes for wp-env quirks. Forces REST API to use ?rest_route= format to avoid .htaccess issues.
 * Version: 0.1.0
 * Author: WP Engine
 * Author URI: https://wpengine.com
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Requires at least: 6.0
 * Requires PHP: 7.4
 *
 * @package HWP\WpEnvHelpers
 */

namespace HWP\WpEnvHelpers;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Force REST API to use ?rest_route= format
 *
 * This is needed for wp-env which has issues with .htaccess permalink rewrites
 *
 * @param string $url REST URL
 * @return string Modified REST URL
 */
function force_rest_route_format( $url ) {
	// Remove existing rest_route parameter if present
	$url = remove_query_arg( 'rest_route', $url );

	// Extract the path from the URL
	$path = trim( parse_url( $url, PHP_URL_PATH ), '/' );

	// Add it back as a query parameter
	return add_query_arg( 'rest_route', '/' . $path, home_url( '/' ) );
}

/**
 * Initialize the plugin
 */
function init() {
	// Only apply in local/development environments
	if ( defined( 'WP_ENVIRONMENT_TYPE' ) && WP_ENVIRONMENT_TYPE === 'local' ) {
		add_filter( 'rest_url', __NAMESPACE__ . '\\force_rest_route_format', 10, 1 );
	}
}

init();
