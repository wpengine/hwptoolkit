<?php
/**
 * Plugin Name: HWP Post Previews
 * Description: A POC for headless post previews.
 * Author: WP Engine
 * Author URI: https://wpengine.com/
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: hwp-post-previews
 * Domain Path: /languages
 * Version: 0.0.1
 * Requires PHP: 7.4
 * Requires at least: 6.7
 *
 * @package Hwp\PostPreviews
 */

use HWP\PostPreviews\Admin\PostPreviews;
use HWP\PostPreviews\Admin\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require __DIR__ . '/vendor/autoload.php';
}

if ( ! defined( 'HWP_POST_PREVIEWS_VERSION' ) ) {
	define( 'HWP_POST_PREVIEWS_VERSION', '0.0.1' );
}

if ( ! defined( 'HWP_POST_PREVIEWS_PLUGIN_DIR' ) ) {
	define( 'HWP_POST_PREVIEWS_PLUGIN_DIR', __DIR__ );
}

if ( ! defined( 'HWP_POST_PREVIEWS_PLUGIN_FILE' ) ) {
	define( 'HWP_POST_PREVIEWS_PLUGIN_FILE', __FILE__ );
}

if ( ! defined( 'HWP_POST_PREVIEWS_PLUGIN_PATH' ) ) {
	define( 'HWP_POST_PREVIEWS_PLUGIN_PATH', plugin_basename( HWP_POST_PREVIEWS_PLUGIN_FILE ) );
}

add_action(
	'init',
	function () {
		$settings = new Settings();
		$settings->init();

		$functions = new PostPreviews();
		$functions->init();
	}
);
