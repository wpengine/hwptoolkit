<?php
/**
 * Initializes a singleton instance of the plugin.
 *
 * @package WPGraphQL\Webhooks
 */

declare(strict_types=1);

namespace WPGraphQL\Webhooks;

use AxeWP\GraphQL\Helper\Helper;
use WPGraphQL\Webhooks\Events\GraphQLEventRegistry;
use WPGraphQL\Webhooks\Events\GraphQLEventSubscriber;
use WPGraphQL\Webhooks\WebhookRegistry;
use WPGraphQL\Webhooks\Events\Interfaces\EventRegistration;
use WPGraphQL\Webhooks\Events\Interfaces\EventSubscriber;

if ( ! class_exists( 'WPGraphQL\Webhooks\Plugin' ) ) :

	/**
	 * Class - Plugin
	 */
	final class Plugin {
		/**
		 * Instance of the webhook registry.
		 *
		 * @var WebhookRegistry|null
		 */
		private ?WebhookRegistry $webhookRegistry = null;
		private ?EventRegistration $eventRegisty = null;

		/**
		 * List of subscriber class names or instances.
		 *
		 * @var array<class-string|EventSubscriber>
		 */
		private array $subscribers = [
		];

		/**
		 * Initialize the plugin.
		 */
		public function init(): void {
			$this->includes();

			// Instantiate the webhook registry here
			$this->webhookRegistry = new WebhookRegistry();
			$this->eventRegisty = new GraphQLEventRegistry();
			$this->setup();

			/**
			 * Fires after the plugin has initialized.
			 *
			 * @param Plugin $this Instance of the Main plugin class.
			 */
			do_action( 'graphql_webhooks_init', $this );
		}

		/**
		 * Get the list of subscribers, filtered by users.
		 *
		 * @return array<class-string|EventSubscriber>
		 */
		public function getSubscribers(): array {
			return apply_filters( 'graphql_webhooks_active_subscribers', $this->subscribers );
		}

		/**
		 * Register all events declared by subscribers.
		 *
		 * This method is hooked to 'graphql_register_events' and will be called
		 * during the WPGraphQL lifecycle to register events dynamically.
		 */
		private function registerEvents(): void {
			if ( ! $this->webhookRegistry ) {
				error_log( 'WebhookRegistry not initialized.' );
				return;
			}

			foreach ( $this->getSubscribers() as $subscriber ) {
				// Instantiate subscriber if class-string
				if ( is_string( $subscriber ) && class_exists( $subscriber ) ) {
					$subscriber = new $subscriber();
				}

				if ( $subscriber instanceof GraphQLEventSubscriber ) {
					$events = $subscriber->getEventRegistrations();
				
					// Prepare webhook type args
					$webhookType = strtolower( ( new \ReflectionClass( $subscriber ) )->getShortName() ); // e.g. 'postsavedsubscriber'
					$args = [ 
						'label' => ucfirst( $webhookType ),
						'description' => "Webhook type for {$webhookType} events",
						'events' => $events,
						'config' => [],
					];

					// Register webhook type and its events via webhookRegistry
					$this->webhookRegistry->register_webhook_type( $webhookType, $args );
				}
			}
		}

		/**
		 * Instantiate and subscribe all subscribers.
		 */
		private function init_subscribers(): void {
			foreach ( $this->getSubscribers() as $subscriber ) {
				if ( is_string( $subscriber ) && class_exists( $subscriber ) ) {
					$subscriber = new $subscriber();
				}

				if ( $subscriber instanceof SubscriberInterface ) {
					$subscriber->subscribe();
				}
			}
		}

		/**
		 * Includes the required files with Composer's autoload.
		 *
		 * @codeCoverageIgnore
		 */
		private function includes(): void {
			if ( defined( 'WPGRAPHQL_HEADLESS_WEBHOOKS_AUTOLOAD' ) && false !== WPGRAPHQL_HEADLESS_WEBHOOKS_AUTOLOAD && defined( 'WPGRAPHQL_HEADLESS_WEBHOOKS_PLUGIN_DIR' ) ) {
				require_once WPGRAPHQL_HEADLESS_WEBHOOKS_PLUGIN_DIR . 'vendor/autoload.php';
			}
		}

		/**
		 * Sets up the schema.
		 *
		 * @codeCoverageIgnore
		 */
		private function setup(): void {
			// Setup boilerplate hook prefix.
			Helper::set_hook_prefix( 'graphql_webhooks' );

			$this->webhookRegistry::init();
			$this->init_subscribers();
			$this->registerEvents();
		}

		/**
		 * Throw error on object clone.
		 * The whole idea of the singleton design pattern is that there is a single object
		 * therefore, we don't want the object to be cloned.
		 *
		 * @codeCoverageIgnore
		 *
		 * @return void
		 */
		public function __clone() {
			// Cloning instances of the class is forbidden.
			_doing_it_wrong( __FUNCTION__, esc_html__( 'The plugin Main class should not be cloned.', 'wp-graphql-headless-webhooks' ), '0.0.1' );
		}

		/**
		 * Disable unserializing of the class.
		 *
		 * @codeCoverageIgnore
		 */
		public function __wakeup(): void {
			// De-serializing instances of the class is forbidden.
			_doing_it_wrong( __FUNCTION__, esc_html__( 'De-serializing instances of the plugin Main class is not allowed.', 'wp-graphql-headless-webhooksu' ), '0.0.1' );
		}
	}
endif;