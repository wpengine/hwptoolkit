<?php
/**
 * Plugin Name: WPGraphQL Webhooks
 * Plugin URI: https://github.com/wpengine/hwptoolkit
 * GitHub Plugin URI: https://github.com/wpengine/hwptoolkit
 * Description: Adds webhook subscription and dispatch functionality to WPGraphQL, enabling WordPress sites to respond to content events via GraphQL-driven webhooks.
 * Author: WPEngine OSS Team
 * Author URI: https://github.com/wpengine
 * Update URI: https://github.com/wpengine/hwptoolkit
 * Version: 0.0.7
 * Text Domain: graphql-webhooks
 * Domain Path: /languages
 * Requires at least: 6.0
 * Tested up to: 6.9
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

// Define text domain constant to use instead of string literals
if ( ! defined( 'WPGRAPHQL_WEBHOOKS_TEXT_DOMAIN' ) ) {
	define( 'WPGRAPHQL_WEBHOOKS_TEXT_DOMAIN', 'graphql-webhooks' );
}

// Load the autoloader.
require_once __DIR__ . '/src/Autoloader.php';
if ( ! \WPGraphQL\Webhooks\Autoloader::autoload() ) {
	return;
}

// Run this function when the plugin is activated.
if ( file_exists( __DIR__ . '/activation.php' ) ) {
	require_once __DIR__ . '/activation.php';
	register_activation_hook( __FILE__, 'graphql_webhooks_activation_callback' );
}

// Run this function when the plugin is deactivated.
if ( file_exists( __DIR__ . '/deactivation.php' ) ) {
	require_once __DIR__ . '/deactivation.php';
	register_deactivation_hook( __FILE__, 'graphql_webhooks_deactivation_callback' );
}

/**
 * Define plugin constants.
 */
function graphql_webhooks_constants(): void {
	// Plugin version.
	if ( ! defined( 'WPGRAPHQL_WEBHOOKS_VERSION' ) ) {
		define( 'WPGRAPHQL_WEBHOOKS_VERSION', '0.0.7' );
	}

	// Plugin Folder Path.
	if ( ! defined( 'WPGRAPHQL_WEBHOOKS_PLUGIN_DIR' ) ) {
		define( 'WPGRAPHQL_WEBHOOKS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
	}

	// Plugin Folder URL.
	if ( ! defined( 'WPGRAPHQL_WEBHOOKS_PLUGIN_URL' ) ) {
		define( 'WPGRAPHQL_WEBHOOKS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
	}

	// Plugin Root File.
	if ( ! defined( 'WPGRAPHQL_WEBHOOKS_PLUGIN_FILE' ) ) {
		define( 'WPGRAPHQL_WEBHOOKS_PLUGIN_FILE', __FILE__ );
	}

	// Whether to autoload the files or not.
	if ( ! defined( 'WPGRAPHQL_WEBHOOKS_AUTOLOAD' ) ) {
		define( 'WPGRAPHQL_WEBHOOKS_AUTOLOAD', true );
	}
}

/**
 * Checks if all the the required plugins are installed and activated.
 *
 * @return array<string>
 */
function graphql_webhooks_dependencies_not_ready(): array {
	$deps = [];

	if ( ! class_exists( '\WPGraphQL' ) ) {
		$deps[] = 'WPGraphQL';
	}

	return $deps;
}

/**
 * Initializes plugin.
 */
function graphql_webhooks_init(): void {
	graphql_webhooks_constants();
	$not_ready = graphql_webhooks_dependencies_not_ready();

	if ( $not_ready === [] && defined( 'WPGRAPHQL_WEBHOOKS_PLUGIN_DIR' ) ) {
		// Load text domain at the init hook
		add_action( 'init', 'WPGraphQL\Webhooks\graphql_webhooks_load_textdomain' );

		require_once WPGRAPHQL_WEBHOOKS_PLUGIN_DIR . 'src/Plugin.php';
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
						// Using plain string to avoid early text domain loading
						printf(
							/* translators: dependency not ready error message */
							'%1$s must be active for WPGraphQL Webhooks to work.',
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
/**
 * Load plugin text domain.
 */
function graphql_webhooks_load_textdomain(): void {
	load_plugin_textdomain(
		'graphql-webhooks',
		false,
		dirname( plugin_basename( __FILE__ ) ) . '/languages'
	);
}

// Load the text domain during init, not earlier
add_action( 'init', 'WPGraphQL\Webhooks\graphql_webhooks_load_textdomain', 1 );

/** @psalm-suppress HookNotFound */
add_action( 'plugins_loaded', 'WPGraphQL\Webhooks\graphql_webhooks_init' );