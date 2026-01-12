<?php
/**
 * Plugin Name: HWP CORS Local
 * Plugin URI: https://github.com/wpengine/hwptoolkit
 * Description: Enables CORS headers for local headless WordPress development. Allows configurable frontend origins via HEADLESS_FRONTEND_URL constant.
 * Version: 0.1.0
 * Author: WP Engine
 * Author URI: https://wpengine.com
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Requires at least: 6.0
 * Requires PHP: 7.4
 *
 * @package HWP\CorsLocal
 */

namespace HWP\CorsLocal;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get the allowed origin for CORS requests
 *
 * @return string|null The allowed origin URL or null if not configured
 */
function get_allowed_origin() {
	if ( defined( 'HEADLESS_FRONTEND_URL' ) ) {
		return HEADLESS_FRONTEND_URL;
	}

	return null;
}

/**
 * Add CORS headers to REST API requests
 *
 * @param mixed $value Response value
 * @return mixed
 */
function add_cors_headers( $value ) {
	$allowed_origin = get_allowed_origin();

	if ( ! $allowed_origin ) {
		return $value;
	}

	header( "Access-Control-Allow-Origin: {$allowed_origin}" );
	header( 'Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE' );
	header( 'Access-Control-Allow-Credentials: true' );
	header( 'Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With' );

	// Handle preflight OPTIONS requests
	if ( $_SERVER['REQUEST_METHOD'] === 'OPTIONS' ) {
		status_header( 200 );
		exit();
	}

	return $value;
}

/**
 * Initialize the plugin
 */
function init() {
	// Only enable CORS in local development environments
	$is_local = ( defined( 'WP_ENVIRONMENT_TYPE' ) && WP_ENVIRONMENT_TYPE === 'local' );
	$is_debug = ( defined( 'WP_DEBUG' ) && WP_DEBUG );

	if ( $is_local || $is_debug ) {
		add_action( 'rest_api_init', function() {
			remove_filter( 'rest_pre_serve_request', 'rest_send_cors_headers' );
			add_filter( 'rest_pre_serve_request', __NAMESPACE__ . '\\add_cors_headers' );
		} );
	}
}

init();
