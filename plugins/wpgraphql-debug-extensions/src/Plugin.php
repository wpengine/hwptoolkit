<?php
/**
 * Main Plugin Class for WPGraphQL Debug Extensions.
 *
 * @package WPGraphQL\Debug
 */

declare(strict_types=1);

namespace WPGraphQL\Debug;

use AxeWP\GraphQL\Helper\Helper;

/**
 * Plugin singleton class.
 */
if ( ! class_exists( 'WPGraphQL\Debug\Plugin' ) ) :

	final class Plugin {

		/**
		 * Singleton instance.
		 *
		 * @var ?self
		 */
		private static ?self $instance = null;

		/**
		 * Get singleton instance.
		 *
		 * @return self
		 */
		public static function instance(): self {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new self();
				self::$instance->includes();
				self::$instance->setup();
			}

			/**
			 * Plugin init action.
			 *
			 * @param self $instance
			 */
			do_action( 'graphql_debug_extensions_init', self::$instance );

			return self::$instance;
		}

		/**
		 * Set up basic plugin components.
		 * This method is intentionally minimal, serving as a scaffold.
		 */
		private function setup(): void {
			// Set the hook prefix for consistency across the plugin's actions/filters.
			// This can be used by other parts of the plugin if generic helpers are introduced.
			Helper::set_hook_prefix( 'graphql_debug_extensions' );
		}

		/**
		 * Include required files, specifically the Composer autoloader.
		 */
		private function includes(): void {
			// Check for the autoloader constant and plugin directory constant defined in the main plugin file.
			if (
				defined( 'WPGRAPHQL_DEBUG_EXTENSIONS_AUTOLOAD' )
				&& false !== WPGRAPHQL_DEBUG_EXTENSIONS_AUTOLOAD
				&& defined( 'WPGRAPHQL_DEBUG_EXTENSIONS_PLUGIN_DIR' )
			) {
				// Require the Composer autoloader.
				// This assumes 'vendor/autoload.php' exists relative to the plugin's root directory.
				require_once WPGRAPHQL_DEBUG_EXTENSIONS_PLUGIN_DIR . 'vendor/autoload.php';
			}
		}

		/**
		 * Prevent cloning.
		 */
		public function __clone() {
			_doing_it_wrong( __FUNCTION__, 'The plugin main class should not be cloned.', '0.0.1' );
		}
		/**
		 * Prevent unserializing.
		 */
		public function __wakeup(): void {
			_doing_it_wrong( __FUNCTION__, 'De-serializing instances of the plugin main class is not allowed.', '0.0.1' );
		}
	}

endif;