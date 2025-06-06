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
		 * Webhook repository.
		 *
		 * @var WebhookRepository
		 */
		private WebhookRepository $repository;

		/**
		 * Webhook handler.
		 *
		 * @var WebhookHandler
		 */
		private WebhookHandler $handler;

		/**
		 * Webhook event manager.
		 *
		 * @var WebhookEventManager
		 */
		private WebhookEventManager $event_manager;

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

		/**
		 * Setup plugin.
		 */
		private function setup(): void {
			Helper::set_hook_prefix( 'graphql_webhooks' );
			WebhookPostType::init();

			$this->repository = new WebhookRepository();
			$this->handler = new WebhookHandler();
			$this->event_manager = new WebhookEventManager( $this->repository, $this->handler );
			$this->event_manager->register_hooks();
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
			return $this->repository;
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