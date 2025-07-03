<?php
/**
 * Plugin Name: WPGraphQL Velocity
 * Plugin URI: https://github.com/wpengine/hwptoolkit
 * GitHub Plugin URI: https://github.com/wpengine/hwptoolkit
 * Description: A plugin for analyzing and measuring the performance of WPGraphQL queries in your headless application.
 * Author: WPEngine Headless OSS Team
 * Author URI: https://github.com/wpengine
 * Update URI: https://github.com/wpengine/hwptoolkit
 * Version: 0.0.1
 * Text Domain: wpgraphql-velocity
 * Domain Path: /languages
 * Requires at least: 6.0
 * Tested up to: 6.8.1
 * Requires PHP: 7.4+
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Requires Plugins: wp-graphql
 * WPGraphQL requires at least: 1.8.0
 * WPGraphQL tested up to: 2.3.3
 *
 * @package WPGraphQL\Velocity
 *
 * @author WPEngine Headless OSS Team
 *
 * @license GPL-2
 */

declare(strict_types=1);

use WPGraphQL\Velocity\Autoloader;
use WPGraphQL\Velocity\Plugin;

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
	// @phpstan-ignore-next-line
	register_activation_hook( __FILE__, 'wpgraphql_velocity__activation_callback' );
}

if ( file_exists( __DIR__ . '/deactivation.php' ) ) {
	require_once __DIR__ . '/deactivation.php';
	// @phpstan-ignore-next-line
	register_deactivation_hook( __FILE__, 'wpgraphql_velocity__deactivation_callback' );
}


// phpcs:enable Generic.Metrics.CyclomaticComplexity.TooHigh
// phpcs:enable SlevomatCodingStandard.Complexity.Cognitive.ComplexityTooHigh

if ( ! function_exists( 'wpgraphql_velocity__init' ) ) {
	/**
	 * Initializes plugin.
	 */
	function wpgraphql_velocity__init(): void {
		wpgraphql_velocity__constants();
		wpgraphql_velocity__plugin_init();
		wpgraphql_velocity__plugin_admin_notice();
	}
}

if ( ! function_exists( 'wpgraphql_velocity__constants' ) ) {
	/**
	 * Define plugin constants.
	 */
	function wpgraphql_velocity__constants(): void {
		if ( ! defined( 'WPGRAPHQL_VELOCITY_VERSION' ) ) {
			define( 'WPGRAPHQL_VELOCITY_VERSION', '0.0.1' );
		}

		if ( ! defined( 'WPGRAPHQL_VELOCITY_PLUGIN_DIR' ) ) {
			define( 'WPGRAPHQL_VELOCITY_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
		}

		if ( ! defined( 'WPGRAPHQL_VELOCITY_PLUGIN_URL' ) ) {
			define( 'WPGRAPHQL_VELOCITY_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
		}

		if ( ! defined( 'WPGRAPHQL_VELOCITY_SETTINGS_GROUP' ) ) {
			define( 'WPGRAPHQL_VELOCITY_SETTINGS_GROUP', 'wpgraphql_velocity__settings_group' );
		}

		if ( ! defined( 'WPGRAPHQL_VELOCITY_SETTINGS_KEY' ) ) {
			define( 'WPGRAPHQL_VELOCITY_SETTINGS_KEY', 'wpgraphql_velocity__settings' );
		}
	}
}

if ( ! function_exists( 'wpgraphql_velocity__plugin_init' ) ) {
	/**
	 * Initialize the WPGraphQL Velocity plugin.
	 */
	function wpgraphql_velocity__plugin_init(): ?Plugin {
		if ( ! defined( 'WPGRAPHQL_VELOCITY_PLUGIN_DIR' ) ) {
			return null;
		}
		require_once WPGRAPHQL_VELOCITY_PLUGIN_DIR . 'src/Plugin.php';
		return Plugin::init();
	}
}


if ( ! function_exists( 'wpgraphql_velocity__plugin_admin_notice' ) ) {
	/**
	 * Display an admin notice if the plugin is not properly initialized.
	 */
	function wpgraphql_velocity__plugin_admin_notice(): void {
		if ( defined( 'WPGRAPHQL_VELOCITY_PLUGIN_DIR' ) ) {
			return;
		}

		add_action(
			'admin_notices',
			static function (): void {
				?>
				<div class="error notice">
					<p>
						<?php
						echo 'Composer vendor directory must be present for WPGraphQL Velocity to work.'
						?>
					</p>
				</div>
				<?php
			},
			10,
			0
		);
	}
}

/**
 * Load plugin text domain.
 */
function wpgraphql_velocity__load_textdomain(): void {
	load_plugin_textdomain( 'wpgraphql-velocity', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}

add_action( 'init', 'wpgraphql_velocity__load_textdomain', 1, 0 );

/** @psalm-suppress HookNotFound */
add_action( 'plugins_loaded', 'wpgraphql_velocity__init', 15, 0 );
