<?php
/**
 * Main Plugin Class for WPGraphQL Webhooks.
 *
 * @package WPGraphQL\Webhooks
 */

declare(strict_types=1);

namespace WPGraphQL\Webhooks;

use AxeWP\GraphQL\Helper\Helper;
use WPGraphQL\Webhooks\DTO\WebhookDTO;
use WPGraphQL\Webhooks\Events\Event;
use WPGraphQL\Webhooks\Events\EventMonitor;
use WPGraphQL\Webhooks\Events\GraphQLEventDispatcher;
use WPGraphQL\Webhooks\Events\GraphQLEventRegistry;
use WPGraphQL\Webhooks\Events\GraphQLEventSubscriber;
use WPGraphQL\Webhooks\PostTypes\WebhookPostType;
use WPGraphQL\Webhooks\Events\Interfaces\EventRegistry;
use WPGraphQL\Webhooks\Events\Interfaces\EventSubscriber;

if ( ! class_exists( 'WPGraphQL\Webhooks\Plugin' ) ) :

	final class Plugin {

		/**
		 * Instance of the webhook type registry.
		 *
		 * @var WebhookTypeRegistry|null
		 */
		private ?WebhookTypeRegistry $webhookTypeRegistry = null;

		/**
		 * Instance of the event registry.
		 *
		 * @var EventRegistry|null
		 */
		private ?EventRegistry $eventRegistry = null;

		/**
		 * List of subscriber class names or instances.
		 *
		 * @var array<class-string|EventSubscriber>
		 */
		private array $subscribers = [];

		/**
		 * Bootstraps the plugin.
		 *
		 * This method initializes all components and hooks.
		 */
		public function init(): void {
			$this->includes();

			// Initialize custom post type
			WebhookPostType::init();

			// Initialize event system
			$this->init_events();

			// Fire action to allow registration of webhook types
			do_action( 'graphql_register_webhooks', $this->webhookTypeRegistry );

			// Setup hooks and schema
			$this->setup();

			// Plugin fully initialized
			do_action( 'graphql_webhooks_init', $this );
		}

		/**
		 * Includes required files via Composer autoload if defined.
		 *
		 * @codeCoverageIgnore
		 */
		private function includes(): void {
			if (
				defined( 'WPGRAPHQL_HEADLESS_WEBHOOKS_AUTOLOAD' ) &&
				false !== WPGRAPHQL_HEADLESS_WEBHOOKS_AUTOLOAD &&
				defined( 'WPGRAPHQL_HEADLESS_WEBHOOKS_PLUGIN_DIR' )
			) {
				require_once WPGRAPHQL_HEADLESS_WEBHOOKS_PLUGIN_DIR . 'vendor/autoload.php';
			}
		}

		/**
		 * Initializes the event system components.
		 */
		private function init_events(): void {
			$eventMonitor = new EventMonitor();
			$eventDispatcher = new GraphQLEventDispatcher( $eventMonitor );
			$this->eventRegistry = new GraphQLEventRegistry( $eventDispatcher );
			$this->webhookTypeRegistry = new WebhookTypeRegistry( $this->eventRegistry );
		}

		/**
		 * Sets up hooks, registers events, subscribers, and schema.
		 */
		private function setup(): void {
			// Set hook prefix for helper functions
			Helper::set_hook_prefix( 'graphql_webhooks' );

			// Register events declared by subscribers
			$this->register_events();

			// Initialize and subscribe all event subscribers
			$this->init_subscribers();

			// Finalize event system (fires graphql_register_events and attaches events)
			$this->eventRegistry->init();

			// Register GraphQL types
			add_action( get_graphql_register_action(), [ TypeRegistry::class, 'init' ] );
		}

		/**
		 * Returns the list of active subscribers, filtered by users.
		 *
		 * @return array<class-string|EventSubscriber>
		 */
		public function get_subscribers(): array {
			return apply_filters( 'graphql_webhooks_active_subscribers', $this->subscribers );
		}

		/**
		 * Registers all events declared by subscribers.
		 *
		 * This method is hooked to 'graphql_register_events' and called during WPGraphQL lifecycle.
		 */
		public function register_events(): void {
			if ( ! $this->webhookTypeRegistry ) {
				error_log( 'WebhookTypeRegistry not initialized.' );
				return;
			}

			foreach ( $this->get_subscribers() as $subscriber ) {
				// Instantiate subscriber if given as class-string
				if ( is_string( $subscriber ) && class_exists( $subscriber ) ) {
					$subscriber = new $subscriber();
				}

				if ( $subscriber instanceof GraphQLEventSubscriber ) {
					$events = [];

					foreach ( $subscriber->get_event_registrations() as $eventData ) {
						$events[] = new Event(
							$eventData['name'],
							$eventData['hook_name'],
							$eventData['callback'] ?? null,
							$eventData['priority'] ?? 10,
							$eventData['arg_count'] ?? 1
						);
					}

					$webhookTypeKey = strtolower( ( new \ReflectionClass( $subscriber ) )->getShortName() );
					$webhookType = new WebhookDTO(
						$webhookTypeKey,
						ucfirst( $webhookTypeKey ),
						"Webhook type for {$webhookTypeKey} events",
						[],
						$events
					);

					$this->webhookTypeRegistry->register_webhook_type( $webhookType );
				}
			}
		}

		/**
		 * Instantiates and subscribes all subscribers.
		 */
		private function init_subscribers(): void {
			foreach ( $this->get_subscribers() as $subscriber ) {
				if ( is_string( $subscriber ) && class_exists( $subscriber ) ) {
					$subscriber = new $subscriber();
				}

				if ( $subscriber instanceof EventSubscriber ) {
					$subscriber->subscribe();
				}
			}
		}

		/**
		 * Prevent cloning of the singleton instance.
		 *
		 * @codeCoverageIgnore
		 */
		public function __clone() {
			_doing_it_wrong( __FUNCTION__, esc_html__( 'The plugin main class should not be cloned.', 'wp-graphql-headless-webhooks' ), '0.0.1' );
		}

		/**
		 * Prevent unserializing of the singleton instance.
		 *
		 * @codeCoverageIgnore
		 */
		public function __wakeup(): void {
			_doing_it_wrong( __FUNCTION__, esc_html__( 'De-serializing instances of the plugin main class is not allowed.', 'wp-graphql-headless-webhooks' ), '0.0.1' );
		}
	}

endif;