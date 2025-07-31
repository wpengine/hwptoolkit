<?php
/**
 * Main Plugin Class for WPGraphQL Debug Extensions.
 *
 * @package WPGraphQL\Debug
 */

declare(strict_types=1);

namespace WPGraphQL\Debug;

use AxeWP\GraphQL\Helper\Helper;
use WPGraphQL\Debug\Analysis\QueryAnalyzer;
use WPGraphQL\Utils\QueryAnalyzer as OriginalQueryAnalyzer;
use WPGraphQL\Debug\Analysis\Rules\Complexity;
use WPGraphQL\Debug\Analysis\Rules\UnfilteredLists;

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
			/**
			 * Hook into the 'graphql_determine_graphql_keys' action.
			 * This action is triggered within WPGraphQL\Utils\QueryAnalyzer::determine_graphql_keys().
			 * It provides the QueryAnalyzer instance as its first argument, allowing us to
			 * initialize our custom extension with the core QueryAnalyzer object.
			 *
			 * We're using a static variable `$initialized` to ensure our extension is
			 * initialized only once per request, even if this action is called multiple times
			 * (though typically it's once per main request).
			 *
			 * @param QueryAnalyzer $query_analyzer_instance The instance of the WPGraphQL Query Analyzer.
			 * @param string        $query                   The GraphQL query string being executed.
			 */
			add_action( 'graphql_determine_graphql_keys', function ($query_analyzer_instance) {
				static $initialized = false;

				if ( $initialized ) {
					return;
				}
				if ( $query_analyzer_instance instanceof OriginalQueryAnalyzer ) {
					$debug_analyzer = new QueryAnalyzer( $query_analyzer_instance );
					$debug_analyzer->addAnalyzerItem( new Complexity() );
					$debug_analyzer->addAnalyzerItem( new UnfilteredLists() );
					$debug_analyzer->init();

					$initialized = true;
				}
			}, 10, 2 );
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
