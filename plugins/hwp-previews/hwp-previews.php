<?php
/**
 * Plugin Name: HWP Previews
 * Description: Headless Previews solution as a WordPress plugin with extensive configurability.
 * Author: WP Engine
 * Author URI: https://wpengine.com/
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: hwp-previews
 * Domain Path: /languages
 * Version: 0.0.1
 * Requires PHP: 7.4
 * Requires at least: 5.3
 *
 * @package HWP\Previews
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require __DIR__ . '/autoload.php';

define( 'HWP_PREVIEWS_BASENAME', plugin_basename( __FILE__ ) );

add_action( 'plugins_loaded', static fn() => HWP\Previews\Plugin::get_instance(
	'0.0.1',
	plugin_dir_path( __FILE__ ),
	plugin_dir_url( __FILE__ )
)->init(), 5, 0 );
