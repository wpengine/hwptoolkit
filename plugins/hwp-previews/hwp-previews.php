<?php
/**
 * Plugin Name: HWP Previews
 * Plugin URI: https://github.com/wpengine/hwptoolkit
 * GitHub Plugin URI: https://github.com/wpengine/hwptoolkit
 * Description: Headless Previews solution as a WordPress plugin with extensive configurability.
 * Author: WPEngine Headless OSS Team
 * Author URI: https://github.com/wpengine
 * Update URI: https://github.com/wpengine/hwptoolkit
 * Version: 0.0.14
 * Text Domain: hwp-previews
 * Domain Path: /languages
 * Requires at least: 6.0
 * Tested up to: 6.8.1
 * Requires PHP: 7.4+
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package HWP\Previews
 */

declare(strict_types=1);

use HWP\Previews\Autoloader;
use HWP\Previews\Plugin;

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
	register_activation_hook( __FILE__, 'hwp_previews_activation_callback' );
}

if ( file_exists( __DIR__ . '/deactivation.php' ) ) {
	require_once __DIR__ . '/deactivation.php';
	// @phpstan-ignore-next-line
	register_deactivation_hook( __FILE__, 'hwp_previews_deactivation_callback' );
}


// phpcs:enable Generic.Metrics.CyclomaticComplexity.TooHigh
// phpcs:enable SlevomatCodingStandard.Complexity.Cognitive.ComplexityTooHigh

if ( ! function_exists( 'hwp_previews_init' ) ) {
	/**
	 * Initializes plugin.
	 */
	function hwp_previews_init(): void {
		hwp_previews_constants();
		hwp_previews_plugin_init();
		hwp_previews_plugin_admin_notice();
	}
}

if ( ! function_exists( 'hwp_previews_constants' ) ) {
	/**
	 * Define plugin constants.
	 */
	function hwp_previews_constants(): void {
		if ( ! defined( 'HWP_PREVIEWS_VERSION' ) ) {
			define( 'HWP_PREVIEWS_VERSION', '0.0.14' );
		}

		if ( ! defined( 'HWP_PREVIEWS_PLUGIN_DIR' ) ) {
			define( 'HWP_PREVIEWS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
		}

		if ( ! defined( 'HWP_PREVIEWS_PLUGIN_URL' ) ) {
			define( 'HWP_PREVIEWS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
		}

		if ( ! defined( 'HWP_PREVIEWS_SETTINGS_GROUP' ) ) {
			define( 'HWP_PREVIEWS_SETTINGS_GROUP', 'hwp_previews_settings_group' );
		}

		if ( ! defined( 'HWP_PREVIEWS_SETTINGS_KEY' ) ) {
			define( 'HWP_PREVIEWS_SETTINGS_KEY', 'hwp_previews_settings' );
		}
	}
}

if ( ! function_exists( 'hwp_previews_plugin_init' ) ) {
	/**
	 * Initialize the HWP Previews plugin.
	 */
	function hwp_previews_plugin_init(): ?Plugin {
		if ( ! defined( 'HWP_PREVIEWS_PLUGIN_DIR' ) ) {
			return null;
		}
		require_once HWP_PREVIEWS_PLUGIN_DIR . 'src/Plugin.php';
		return Plugin::init();
	}
}


if ( ! function_exists( 'hwp_previews_plugin_admin_notice' ) ) {
	/**
	 * Display an admin notice if the plugin is not properly initialized.
	 */
	function hwp_previews_plugin_admin_notice(): void {
		if ( defined( 'HWP_PREVIEWS_PLUGIN_DIR' ) ) {
			return;
		}

		add_action(
			'admin_notices',
			static function (): void {
				?>
				<div class="error notice">
					<p>
						<?php
						echo 'Composer vendor directory must be present for HWP Previews to work.'
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
function hwp_previews_load_textdomain(): void {
	load_plugin_textdomain( 'hwp-previews', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}

add_action( 'init', 'hwp_previews_load_textdomain', 1, 0 );

/** @psalm-suppress HookNotFound */
add_action( 'plugins_loaded', 'hwp_previews_init', 15, 0 );
