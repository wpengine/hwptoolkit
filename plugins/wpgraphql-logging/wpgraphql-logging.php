<?php
/**
 * Plugin Name: WPGraphQL Logging
 * Plugin URI: https://github.com/wpengine/hwptoolkit
 * GitHub Plugin URI: https://github.com/wpengine/hwptoolkit
 * Description: A WPGraphQL logging plugin that provides visibility into request lifecycle to help quickly identify and resolve bottlenecks in your headless WordPress application.
 * Author: WPEngine Headless OSS Team
 * Author URI: https://github.com/wpengine
 * Update URI: https://github.com/wpengine/hwptoolkit
 * Version: 0.0.1
 * Text Domain: wpgraphql-logging
 * Domain Path: /languages
 * Requires at least: 6.5
 * Tested up to: 6.8.2
 * Requires PHP: 8.1+
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Requires Plugins: wp-graphql
 * WPGraphQL requires at least: 2.3.0
 * WPGraphQL tested up to: 2.3.3
 *
 * @package WPGraphQL\Logging
 *
 * @author WPEngine Headless OSS Team
 *
 * @license GPL-2
 */

declare(strict_types=1);

use WPGraphQL\Logging\Autoloader;
use WPGraphQL\Logging\Plugin;

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
	register_activation_hook( __FILE__, 'wpgraphql_logging_activation_callback' );
}

if ( file_exists( __DIR__ . '/deactivation.php' ) ) {
	require_once __DIR__ . '/deactivation.php';
	// @phpstan-ignore-next-line
	register_deactivation_hook( __FILE__, 'wpgraphql_logging_deactivation_callback' );
}


// phpcs:enable Generic.Metrics.CyclomaticComplexity.TooHigh
// phpcs:enable SlevomatCodingStandard.Complexity.Cognitive.ComplexityTooHigh

if ( ! function_exists( 'wpgraphql_logging_init' ) ) {
	/**
	 * Initializes plugin.
	 */
	function wpgraphql_logging_init(): void {
		wpgraphql_logging_constants();
		wpgraphql_logging_plugin_init();
		wpgraphql_logging_plugin_admin_notice();
	}
}

if ( ! function_exists( 'wpgraphql_logging_constants' ) ) {
	/**
	 * Define plugin constants.
	 */
	function wpgraphql_logging_constants(): void {

		if ( ! defined( 'WPGRAPHQL_LOGGING_VERSION' ) ) {
			define( 'WPGRAPHQL_LOGGING_VERSION', '0.0.1' );
		}

		if ( ! defined( 'WPGRAPHQL_LOGGING_PLUGIN_DIR' ) ) {
			define( 'WPGRAPHQL_LOGGING_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
		}

		if ( ! defined( 'WPGRAPHQL_LOGGING_PLUGIN_URL' ) ) {
			define( 'WPGRAPHQL_LOGGING_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
		}

		if ( ! defined( 'WPGRAPHQL_LOGGING_SETTINGS_KEY' ) ) {
			define( 'WPGRAPHQL_LOGGING_SETTINGS_KEY', 'wpgraphql_logging_settings' );
		}

		if ( ! defined( 'WPGRAPHQL_LOGGING_SETTINGS_GROUP' ) ) {
			define( 'WPGRAPHQL_LOGGING_SETTINGS_GROUP', 'wpgraphql_logging_settings_group' );
		}
	}
}

if ( ! function_exists( 'wpgraphql_logging_plugin_init' ) ) {
	/**
	 * Initialize the WPGraphQL Logging plugin.
	 */
	function wpgraphql_logging_plugin_init(): ?Plugin {
		if ( ! defined( 'WPGRAPHQL_LOGGING_PLUGIN_DIR' ) ) {
			return null;
		}
		require_once WPGRAPHQL_LOGGING_PLUGIN_DIR . 'src/Plugin.php';
		return Plugin::init();
	}
}


if ( ! function_exists( 'wpgraphql_logging_plugin_admin_notice' ) ) {
	/**
	 * Display an admin notice if the plugin is not properly initialized.
	 */
	function wpgraphql_logging_plugin_admin_notice(): void {
		if ( defined( 'WPGRAPHQL_LOGGING_PLUGIN_DIR' ) ) {
			return;
		}

		add_action(
			'admin_notices',
			static function (): void {
				?>
				<div class="error notice">
					<p>
						<?php
						echo 'Composer vendor directory must be present for WPGraphQL Logging to work.'
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
function wpgraphql_logging_load_textdomain(): void {
	load_plugin_textdomain( 'wpgraphql-logging', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}

add_action( 'init', 'wpgraphql_logging_load_textdomain', 1, 0 );

/** @psalm-suppress HookNotFound */
add_action( 'plugins_loaded', static function (): void {
	wpgraphql_logging_init();
}, 10, 0 );
