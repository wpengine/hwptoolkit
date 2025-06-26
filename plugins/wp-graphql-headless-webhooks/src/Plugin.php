<?php
/**
 * Main Plugin Class for WPGraphQL Webhooks.
 *
 * @package WPGraphQL\Webhooks
 */

declare(strict_types=1);

namespace WPGraphQL\Webhooks;

use AxeWP\GraphQL\Helper\Helper;
use WPGraphQL\Webhooks\Handlers\WebhookHandler;
use WPGraphQL\Webhooks\PostTypes\WebhookPostType;
use WPGraphQL\Webhooks\Repository\WebhookRepository;
use WPGraphQL\Webhooks\Events\WebhookEventManager;
use WPGraphQL\Webhooks\Events\SmartCacheWebhookManager;
use WPGraphQL\Webhooks\Services\Interfaces\ServiceLocator;
use WPGraphQL\Webhooks\Services\PluginServiceLocator;

/**
 * Plugin singleton class.
 */
if ( ! class_exists( 'WPGraphQL\Webhooks\Plugin' ) ) :

	final class Plugin {

		/**
		 * Singleton instance.
		 *
		 * @var ?self
		 */
		private static ?self $instance = null;

		/**
		 * Service locator instance.
		 *
		 * @var ServiceLocator
		 */
		private ServiceLocator $services;

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
			do_action( 'graphql_webhooks_init', self::$instance );

			return self::$instance;
		}

		private function setup(): void {
			Helper::set_hook_prefix( 'graphql_webhooks' );
			WebhookPostType::init();

			$this->services = new PluginServiceLocator();

			// Register services
			$this->services->set( 'repository', function () {
				return new WebhookRepository();
			} );

			$this->services->set( 'handler', function () {
				return new WebhookHandler();
			} );

			$this->services->set( 'event_manager', function () {
				$repository = $this->services->get( 'repository' );
				$handler = $this->services->get( 'handler' );
				
				if ( class_exists( 'WPGraphQL\SmartCache\Document' ) || defined( 'WPGRAPHQL_SMART_CACHE_VERSION' ) ) {
					return new SmartCacheWebhookManager( $repository, $handler );
				}
				
				return new WebhookEventManager( $repository, $handler );
			} );
			// Initialize event manager and register hooks
			$eventManager = $this->services->get( 'event_manager' );
			$eventManager->register_hooks();

			// Initialize admin UI
			if ( is_admin() ) {
				$repository = $this->services->get( 'repository' );
				
				if ( class_exists( 'WPGraphQL\Webhooks\Admin\WebhooksAdmin' ) ) {
					$admin = new \WPGraphQL\Webhooks\Admin\WebhooksAdmin( $repository );
					// The constructor already sets up all necessary hooks
				}
			}

			// Initialize REST endpoints
			add_action( 'rest_api_init', function () {
				$repository = $this->services->get( 'repository' );
				
				if ( class_exists( 'WPGraphQL\Webhooks\Rest\WebhookTestEndpoint' ) ) {
					$testEndpoint = new \WPGraphQL\Webhooks\Rest\WebhookTestEndpoint( $repository );
					$testEndpoint->register_routes();
				}
			} );
		}

		/**
		 * Include required files.
		 */
		private function includes(): void {
			if (
				defined( 'WPGRAPHQL_HEADLESS_WEBHOOKS_AUTOLOAD' )
				&& false !== WPGRAPHQL_HEADLESS_WEBHOOKS_AUTOLOAD
				&& defined( 'WPGRAPHQL_HEADLESS_WEBHOOKS_PLUGIN_DIR' )
			) {
				require_once WPGRAPHQL_HEADLESS_WEBHOOKS_PLUGIN_DIR . 'vendor/autoload.php';
			}
		}

		/**
		 * Get the webhook repository instance.
		 *
		 * Provides access to the WebhookRepository for managing webhook data.
		 *
		 * @return WebhookRepository The repository instance.
		 */
		public function get_repository(): WebhookRepository {
			return $this->services->get( 'repository' );
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