<?php
/**
 * Plugin Name: HWP Global Stylesheet
 * Description: Exposes WordPress global stylesheets through WPGraphQL
 * Version: 1.0.0
 * Author: WP Engine
 * Text Domain: hwp-global-stylesheet
 * Domain Path: /languages
 *
 * @package WPGlobalStylesheet
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WP_Global_Stylesheet
 */
class WP_Global_Stylesheet {

	/**
	 * Instance of this class.
	 *
	 * @var WP_Global_Stylesheet
	 */
	private static $instance = null;

	/**
	 * Get an instance of this class.
	 *
	 * @return WP_Global_Stylesheet
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Initialize the plugin.
	 */
	private function __construct() {
		// Check if WPGraphQL is active
		if ( class_exists( 'WPGraphQL' ) ) {
			add_action( 'graphql_register_types', [ $this, 'register_global_stylesheet_field' ] );
			return;
		}

		// Add admin notice if WPGraphQL is not active
		add_action( 'admin_notices', [ $this, 'wpgraphql_missing_notice' ] );
	}

	/**
	 * Admin notice for missing WPGraphQL dependency.
	 */
	public function wpgraphql_missing_notice() {
		if ( current_user_can( 'activate_plugins' ) ) {
			?>
			<div class="notice notice-error">
				<p><?php esc_html_e( 'WP Global Stylesheet requires WPGraphQL plugin to be installed and activated.', 'hwp-global-stylesheet' ); ?></p>
			</div>
			<?php
		}
	}

	/**
	 * Registers a field on the RootQuery called "globalStylesheet" which
	 * returns the stylesheet resulting of merging core, theme, and user data.
	 *
	 * @return void
	 */
	public function register_global_stylesheet_field() {
		register_graphql_enum_type(
			'GlobalStylesheetTypesEnum',
			[
				'description' => __( 'Types of styles to load', 'hwp-global-stylesheet' ),
				'values' => [
					'VARIABLES' => [
						'value' => 'variables',
					],
					'PRESETS' => [
						'value' => 'presets',
					],
					'STYLES' => [
						'value' => 'styles',
					],
					'BASE_LAYOUT_STYLES' => [
						'value' => 'base-layout-styles',
					],
				],
			]
		);

		register_graphql_field(
			'RootQuery',
			'globalStylesheet',
			[
				'type' => 'String',
				'args' => [
					'types' => [
						'type' => [ 'list_of' => 'GlobalStylesheetTypesEnum' ],
						'description' => __( 'Types of styles to load.', 'hwp-global-stylesheet' ),
					],
				],
				'description' => __( 'Returns the stylesheet resulting of merging core, theme, and user data.', 'hwp-global-stylesheet' ),
				'resolve' => function( $root, $args, $context, $info ) {
					$types = $args['types'] ?? null;

					// Check if wp_get_global_stylesheet function exists (WordPress 5.9+)
					if ( function_exists( 'wp_get_global_stylesheet' ) ) {
						return wp_get_global_stylesheet( $types );
					} else {
						return '/* wp_get_global_stylesheet function is not available. Your WordPress version might be too old. */';
					}
				},
			]
		);
	}
}

// Initialize the plugin
add_action( 'plugins_loaded', function() {
	WP_Global_Stylesheet::get_instance();
});
