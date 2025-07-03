<?php
/**
 * Plugin Name:WPGraphQL Query Monitor
 * Plugin URI: https://github.com/wpengine/hwptoolkit
 * GitHub Plugin URI: https://github.com/wpengine/hwptoolkit
 * Description: Monitor and analyze WPGraphQL queries with detailed logging and performance insights.
 * Author: WPEngine Headless OSS Team
 * Author URI: https://github.com/wpengine
 * Update URI: https://github.com/wpengine/hwptoolkit
 * Version: 0.0.1
 * Text Domain: graphql-log-monitor
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
 * @package WPGraphQL\LogMonitor
 *
 * @author WPEngine Headless OSS Team
 *
 * @license GPL-2
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('WPGRAPHQL_MONITOR_VERSION', '1.0.0');
define('WPGRAPHQL_MONITOR_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WPGRAPHQL_MONITOR_PLUGIN_URL', plugin_dir_url(__FILE__));

// Simple autoloader
spl_autoload_register(function ($class) {
    $prefix = 'WPGraphQL\\LogMonitor\\';
    
    if (strpos($class, $prefix) !== 0) {
        return;
    }
    
    $relative_class = substr($class, strlen($prefix));
    $file = WPGRAPHQL_MONITOR_PLUGIN_DIR . 'src/' . str_replace('\\', '/', $relative_class) . '.php';
    
    if (file_exists($file)) {
        require_once $file;
    }
});

// Initialize the plugin
new \WPGraphQL\LogMonitor\Plugin();