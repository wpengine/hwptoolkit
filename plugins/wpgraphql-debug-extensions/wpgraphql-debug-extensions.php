<?php
/**
 * Plugin Name: WPGraphQL Debug Extensions
 * Plugin URI: https://github.com/wpengine/hwptoolkit
 * GitHub Plugin URI: https://github.com/wpengine/hwptoolkit
 * Description: Debug extensions for WPGraphQL.
 * Author: WPEngine OSS Team
 * Author URI: https://github.com/wpengine
 * Update URI: https://github.com/wpengine/hwptoolkit
 * Version: 0.0.1
 * Text Domain: wpgraphql-debug-extensions
 * Domain Path: /languages
 * Requires at least: 6.0
 * Tested up to: 6.8
 * Requires PHP: 7.4+
 * Requires Plugins: wp-graphql
 * WPGraphQL requires at least: 1.8.0
 * License: BSD-0-Clause
 * License URI: https://opensource.org/licenses/BSD-3-Clause
 *
 * @package WPGraphQL\Debug
 */

declare(strict_types=1);

namespace WPGraphQL\Debug;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define text domain constant to use instead of string literals
if ( ! defined( 'WPGRAPHQL_DEBUG_EXTENSIONS_TEXT_DOMAIN' ) ) {
    define( 'WPGRAPHQL_DEBUG_EXTENSIONS_TEXT_DOMAIN', 'wpgraphql-debug-extensions' );
}

// Load the autoloader.
require_once __DIR__ . '/src/Autoloader.php';
if ( ! \WPGraphQL\Debug\Autoloader::autoload() ) {
    return;
}

// Run this function when the plugin is activated.
if ( file_exists( __DIR__ . '/activation.php' ) ) {
    require_once __DIR__ . '/activation.php';
    register_activation_hook( __FILE__, 'graphql_debug_activation_callback' );
}

// Run this function when the plugin is deactivated.
if ( file_exists( __DIR__ . '/deactivation.php' ) ) {
    require_once __DIR__ . '/deactivation.php';
    register_deactivation_hook( __FILE__, 'graphql_debug_deactivation_callback' );
}

/**
 * Define plugin constants.
 */
function wpgraphql_debug_extensions_constants(): void {
    // Plugin version.
    if ( ! defined( 'WPGRAPHQL_DEBUG_EXTENSIONS_VERSION' ) ) {
        define( 'WPGRAPHQL_DEBUG_EXTENSIONS_VERSION', '0.0.3' );
    }

    // Plugin Folder Path.
    if ( ! defined( 'WPGRAPHQL_DEBUG_EXTENSIONS_PLUGIN_DIR' ) ) {
        define( 'WPGRAPHQL_DEBUG_EXTENSIONS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
    }

    // Plugin Folder URL.
    if ( ! defined( 'WPGRAPHQL_DEBUG_EXTENSIONS_PLUGIN_URL' ) ) {
        define( 'WPGRAPHQL_DEBUG_EXTENSIONS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
    }

    // Plugin Root File.
    if ( ! defined( 'WPGRAPHQL_DEBUG_EXTENSIONS_PLUGIN_FILE' ) ) {
        define( 'WPGRAPHQL_DEBUG_EXTENSIONS_PLUGIN_FILE', __FILE__ );
    }

    // Whether to autoload the files or not.
    if ( ! defined( 'WPGRAPHQL_DEBUG_EXTENSIONS_AUTOLOAD' ) ) {
        define( 'WPGRAPHQL_DEBUG_EXTENSIONS_AUTOLOAD', true );
    }
}

/**
 * Checks if all the the required plugins are installed and activated.
 *
 * @return array<string>
 */
function wpgraphql_debug_extensions_dependencies_not_ready(): array {
    $deps = [];

    if ( ! class_exists( '\WPGraphQL' ) ) {
        $deps[] = 'WPGraphQL';
    }

    return $deps;
}

/**
 * Initializes plugin.
 */
function wpgraphql_debug_extensions_init(): void {
    wpgraphql_debug_extensions_constants();
    $not_ready = wpgraphql_debug_extensions_dependencies_not_ready();

    if ( $not_ready === [] && defined( 'WPGRAPHQL_DEBUG_EXTENSIONS_PLUGIN_DIR' ) ) {
        // Load text domain at the init hook
        add_action( 'init', 'WPGraphQL\Debug\wpgraphql_debug_extensions_load_textdomain' );

        $plugin = new \WPGraphQL\Debug\Plugin();
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
                            '%1$s must be active for WPGraphQL Debug Extensions to work.',
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
function wpgraphql_debug_extensions_load_textdomain(): void {
    load_plugin_textdomain(
        'wpgraphql-debug-extensions',
        false,
        dirname( plugin_basename( __FILE__ ) ) . '/languages'
    );
}

// Load the text domain during init, not earlier
add_action( 'init', 'WPGraphQL\Debug\wpgraphql_debug_extensions_load_textdomain', 1 );

/** @psalm-suppress HookNotFound */
add_action( 'plugins_loaded', 'WPGraphQL\Debug\wpgraphql_debug_extensions_init' );