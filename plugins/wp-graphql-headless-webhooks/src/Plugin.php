<?php
/**
 * Main Plugin Class for WPGraphQL Webhooks.
 *
 * @package WPGraphQL\Webhooks
 */

declare(strict_types=1);

namespace WPGraphQL\Webhooks;

use AxeWP\GraphQL\Helper\Helper;
use WPGraphQL\Webhooks\Admin\WebhooksAdmin;
use WPGraphQL\Webhooks\Handlers\WebhookHandler;
use WPGraphQL\Webhooks\PostTypes\WebhookPostType;
use WPGraphQL\Webhooks\Repository\WebhookRepository;
use WPGraphQL\Webhooks\Events\WebhookEventManager;
use WPGraphQL\Webhooks\Rest\WebhookEventsEndpoint;
use WPGraphQL\Webhooks\Rest\WebhookTestEndpoint;
use WPGraphQL\Webhooks\Mutation\CreateWebhook;

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
		 * Webhooks admin.
		 *
		 * @var WebhooksAdmin
		 */
		private WebhooksAdmin $admin;

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

			$this->repository    = new WebhookRepository();
			$this->handler       = new WebhookHandler();
			$this->event_manager = new WebhookEventManager( $this->repository, $this->handler );
			$this->event_manager->register_hooks();

			// Register REST endpoints
			if ( class_exists( WebhookEventsEndpoint::class ) ) {
				$events_endpoint = new WebhookEventsEndpoint( $this->repository );
				add_action( 'rest_api_init', array( $events_endpoint, 'register' ) );
			}

			// Register test endpoint
			if ( class_exists( WebhookTestEndpoint::class ) ) {
				$test_endpoint = new WebhookTestEndpoint( $this->repository );
				add_action( 'rest_api_init', array( $test_endpoint, 'register' ) );
			}

			// Initialize admin UI
			if ( is_admin() ) {
				$this->admin = new WebhooksAdmin( $this->repository );
				$this->admin->init();
			}
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
