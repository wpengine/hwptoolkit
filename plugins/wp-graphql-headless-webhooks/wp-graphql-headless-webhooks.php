<?php
/**
 * Plugin Name: WPGraphQL Headless Webhooks
 * Plugin URI: https://github.com/wpengine/hwptoolkit
 * GitHub Plugin URI: https://github.com/wpengine/hwptoolkit
 * Description: Adds webhook subscription and dispatch functionality to WPGraphQL, enabling headless WordPress sites to respond to content events via GraphQL-driven webhooks.
 * Author: WPEngine Headless OSS Team
 * Author URI: https://github.com/wpengine
 * Update URI: https://github.com/wpengine/hwptoolkit
 * Version: 0.0.1
 * Text Domain: wp-graphql-headless-webhooks
 * Domain Path: /languages
 * Requires at least: 6.0
 * Tested up to: 6.8
 * Requires PHP: 7.4+
 * Requires Plugins: wp-graphql
 * WPGraphQL requires at least: 1.8.0
 * License: BSD-0-Clause
 * License URI: https://opensource.org/licenses/BSD-3-Clause
 *
 * @package WPGraphQL\Webhooks
 */

declare(strict_types=1);

namespace WPGraphQL\Webhooks;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Load the autoloader.
require_once __DIR__ . '/src/Autoloader.php';
if ( ! \WPGraphQL\Webhooks\Autoloader::autoload() ) {
	return;
}

// Run this function when the plugin is activated.
if ( file_exists( __DIR__ . '/activation.php' ) ) {
	require_once __DIR__ . '/activation.php';
	register_activation_hook( __FILE__, 'graphql_headless_webhooks_activation_callback' );
}

// Run this function when the plugin is deactivated.
if ( file_exists( __DIR__ . '/deactivation.php' ) ) {
	require_once __DIR__ . '/deactivation.php';
	register_deactivation_hook( __FILE__, 'graphql_headless_webhooks_deactivation_callback' );
}

/**
 * Define plugin constants.
 */
function graphql_headless_webhooks_constants(): void {
	// Plugin version.
	if ( ! defined( 'WPGRAPHQL_HEADLESS_WEBHOOKS_VERSION' ) ) {
		define( 'WPGRAPHQL_HEADLESS_WEBHOOKS_VERSION', '0.0.1' );
	}

	// Plugin Folder Path.
	if ( ! defined( 'WPGRAPHQL_HEADLESS_WEBHOOKS_PLUGIN_DIR' ) ) {
		define( 'WPGRAPHQL_HEADLESS_WEBHOOKS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
	}

	// Plugin Folder URL.
	if ( ! defined( 'WPGRAPHQL_HEADLESS_WEBHOOKS_PLUGIN_URL' ) ) {
		define( 'WPGRAPHQL_HEADLESS_WEBHOOKS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
	}

	// Plugin Root File.
	if ( ! defined( 'WPGRAPHQL_HEADLESS_WEBHOOKS_PLUGIN_FILE' ) ) {
		define( 'WPGRAPHQL_HEADLESS_WEBHOOKS_PLUGIN_FILE', __FILE__ );
	}

	// Whether to autoload the files or not.
	if ( ! defined( 'WPGRAPHQL_HEADLESS_WEBHOOKS_AUTOLOAD' ) ) {
		define( 'WPGRAPHQL_HEADLESS_WEBHOOKS_AUTOLOAD', true );
	}
}

/**
 * Checks if all the the required plugins are installed and activated.
 *
 * @return array<string>
 */
function graphql_headless_webhooks_dependencies_not_ready(): array {
	$deps = [];

	if ( ! class_exists( '\WPGraphQL' ) ) {
		$deps[] = 'WPGraphQL';
	}

	return $deps;
}

/**
 * Initializes plugin.
 */
function graphql_headless_webhooks_init(): void {
	graphql_headless_webhooks_constants();
	$not_ready = graphql_headless_webhooks_dependencies_not_ready();

	if ( $not_ready === [] && defined( 'WPGRAPHQL_HEADLESS_WEBHOOKS_PLUGIN_DIR' ) ) {
		require_once WPGRAPHQL_HEADLESS_WEBHOOKS_PLUGIN_DIR . 'src/Plugin.php';
		$plugin = new \WPGraphQL\Webhooks\Plugin();
		$plugin::instance();
		return;
	}

	foreach ( $not_ready as $dep ) {
		add_action(
			'admin_notices',
			static function () use ($dep) {
				?>
			<div class="error notice">
				<p>
					<?php
						printf(
							/* translators: dependency not ready error message */
							esc_html__( '%1$s must be active for WPGraphQL Plugin Name to work.', 'wp-graphql-headless-webhooks' ),
							esc_html( $dep )
						);
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
/** @psalm-suppress HookNotFound */
add_action( 'plugins_loaded', 'WPGraphQL\Webhooks\graphql_headless_webhooks_init' );

