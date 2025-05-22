<?php
/**
 * Webhook Registry class
 *
 * @package WPGraphQL\Webhooks
 */

namespace WPGraphQL\Webhooks;

use WPGraphQL\Webhooks\Events\Interfaces\EventRegistry;
use WPGraphQL\Webhooks\PostTypes\WebhookPostType;

/**
 * Class WebhookRegistry
 */
class WebhookRegistry {

	/**
	 * Registered webhook types
	 *
	 * @var array<string, array<string, mixed>> Array of webhook types keyed by type identifier.
	 */
	private array $webhook_types = [];

	private ?EventRegistry $eventRegistry = null;

	/**
	 * Instance of the registry
	 *
	 * @var WebhookRegistry|null
	 */
	private static ?WebhookRegistry $instance = null;

	/**
	 * Get registry instance
	 *
	 * @return WebhookRegistry
	 */
	public static function instance(): WebhookRegistry {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Initialize the registry
	 */
	/**
	 * Initialize the registry
	 */
	public static function init(): void {
		WebhookPostType::init();
		do_action( 'graphql_register_webhooks', self::instance() );
	}

	public function set_event_registry(EventRegistry $eventRegistry): void {
        $this->eventRegistry = $eventRegistry;
		$this->eventRegistry->init();
    }

	/**
	 * Register a webhook type
	 *
	 * @param string $type Type identifier for the webhook.
	 * @param array<string, mixed> $args {
	 *     Args for the webhook type.
	 *
	 *     @type string $label       Human-readable label for the webhook type (default: $type).
	 *     @type string $description Description of the webhook type (default: '').
	 *     @type array<string, mixed>  $config      Optional. Additional configuration for the webhook.
	 * }
	 * @return bool Whether the webhook type was registered successfully.
	 */
	public function register_webhook_type( string $type, array $args = [] ): bool {
		if ( $type === '' ) {
			return false;
		}
		if ( isset( $this->webhook_types[ $type ] ) ) {
			return false;
		}
		

		$defaults = [ 
			'label' => $type,
			'description' => '',
			'config' => [],
			'events' => [],
		];

		$args = wp_parse_args( $args, $defaults );
		$this->webhook_types[ $type ] = $args;
		if ($this->eventRegistry !== null && !empty($args['events'])) {
            foreach ($args['events'] as $event) {
                if (!isset($event['name']) || !isset($event['hook_name'])) {
                    continue;
                }
                $this->eventRegistry->register_event(
                    $event['name'],
                    $event['hook_name'],
                    $event['callback'] ?? null,
                    $event['priority'] ?? 10,
                    $event['arg_count'] ?? 1
                );
            }
        }

		return true;
	}

	/**
	 * Get all registered webhook types
	 *
	 * @return array<string, array<string, mixed>>
	 */
	public function get_webhook_types(): array {
		return $this->webhook_types;
	}

	/**
	 * Get a specific webhook type
	 *
	 * @param string $type The webhook type.
	 * @return array<string, mixed>|null
	 */
	public function get_webhook_type( string $type ): ?array {
		return $this->webhook_types[ $type ] ?? null;
	}

	/**
	 * Creates a new webhook
	 *
	 * @param string $type    Webhook type identifier.
	 * @param string $name    Webhook name/title.
	 * @param array<string, mixed> $config  Webhook configuration.
	 * @return int|\WP_Error Post ID of the new webhook or error.
	 */
	public function create_webhook( string $type, string $name, array $config = [] ) {
		if ( ! isset( $this->webhook_types[ $type ] ) ) {
			return new \WP_Error( 'invalid_webhook_type', __( 'Invalid webhook type.', 'wp-graphql-headless-webhooks' ) );
		}

		$post_id = wp_insert_post(
			[ 
				'post_title' => $name,
				'post_type' => 'graphql_webhook',
				'post_status' => 'publish',
			],
			true
		);

		if ( is_wp_error( $post_id ) ) {
			return $post_id;
		}

		update_post_meta( $post_id, '_webhook_type', $type );

		// Replace empty() with count check for strictness
		if ( count( $config ) > 0 ) {
			update_post_meta( $post_id, '_webhook_config', $config );
		}

		return $post_id;
	}
}
