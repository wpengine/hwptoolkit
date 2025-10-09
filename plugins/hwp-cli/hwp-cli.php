<?php
/**
 * Plugin Name: HWP CLI Plugin
 * Plugin URI: https://github.com/hwp/cli
 * Description: Command-line interface for Headless WordPress Toolkit
 * Version: 0.1.0
 * Author: HWP Team
 * Author URI: https://github.com/hwp
 * License: MIT
 * Text Domain: hwp-cli
 * Domain Path: /languages
 * Requires PHP: 7.4
 * Requires at least: 6.0
 * NPM Package: @placeholder/cli
 * Repository: https://github.com/hwp/cli
 *
 * @package HWP\CLI
 */

namespace HWP\CLI;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main plugin class.
 */
class Plugin {
	/**
	 * Plugin instance.
	 *
	 * @var Plugin|null
	 */
	private static $instance = null;

	/**
	 * Get plugin instance.
	 *
	 * @return Plugin
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		$this->init();
	}

	/**
	 * Initialize plugin.
	 *
	 * @return void
	 */
	private function init() {
		add_action( 'rest_api_init', [ $this, 'register_rest_routes' ] );
		add_action( 'admin_menu', [ $this, 'register_admin_menu' ] );
	}

	/**
	 * Register REST API routes.
	 *
	 * @return void
	 */
	public function register_rest_routes() {
		register_rest_route(
			'hwp/v1',
			'/cli/status',
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'get_cli_status' ],
				'permission_callback' => '__return_true',
			]
		);

		register_rest_route(
			'hwp/v1',
			'/cli/plugins',
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'get_hwp_plugins' ],
				'permission_callback' => '__return_true',
			]
		);
	}

	/**
	 * Register admin menu.
	 *
	 * @return void
	 */
	public function register_admin_menu() {
		add_submenu_page(
			'tools.php',
			__( 'HWP CLI', 'hwp-cli' ),
			__( 'HWP CLI', 'hwp-cli' ),
			'manage_options',
			'hwp-cli',
			[ $this, 'render_admin_page' ]
		);
	}

	/**
	 * Get CLI status.
	 *
	 * @return \WP_REST_Response
	 */
	public function get_cli_status() {
		global $wp_version;

		return rest_ensure_response( [
			'status'     => 'active',
			'version'    => '1.0.0',
			'environment' => wp_get_environment_type(),
			'url'        => get_site_url(),
			'wp_version' => $wp_version,
			'wp_debug'   => defined('WP_DEBUG') && WP_DEBUG,
			'rest_api'   => true,
		] );
	}

	/**
	 * Get HWP plugins.
	 *
	 * @return \WP_REST_Response
	 */
	public function get_hwp_plugins() {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$all_plugins = get_plugins();
		$hwp_plugins = [];

		foreach ( $all_plugins as $plugin_path => $plugin_data ) {
			if ( strpos( $plugin_path, 'hwp-' ) === 0 || strpos( dirname( $plugin_path ), 'hwp-' ) === 0 ) {
				$plugin_data['active'] = is_plugin_active( $plugin_path );
				$plugin_data['file'] = $plugin_path;
				$hwp_plugins[] = $plugin_data;
			}
		}

		return rest_ensure_response( $hwp_plugins );
	}

	/**
	 * Render admin page.
	 *
	 * @return void
	 */
	public function render_admin_page() {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'HWP CLI', 'hwp-cli' ); ?></h1>
			<div id="hwp-cli-root"></div>
		</div>
		<?php
	}
}

// Initialize plugin.
Plugin::get_instance();
