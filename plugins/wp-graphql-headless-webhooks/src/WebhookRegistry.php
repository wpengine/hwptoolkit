<?php
/**
 * Webhook Registry class
 *
 * @package WPGraphQL\Webhooks
 */

namespace WPGraphQL\Webhooks;

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
	public static function init(): void {
		// Register the CPT on initialization.
		add_action( 'init', [ self::instance(), 'register_webhook_cpt' ], 5, 0 );
		do_action( 'graphql_register_webhooks', self::instance() );
	}

	/**
	 * Register the webhook CPT
	 */
	public function register_webhook_cpt(): void {
		$labels = [ 
			'name' => __( 'Webhooks', 'wp-graphql-headless-webhooks' ),
			'singular_name' => __( 'Webhook', 'wp-graphql-headless-webhooks' ),
			'add_new' => __( 'Add New', 'wp-graphql-headless-webhooks' ),
			'add_new_item' => __( 'Add New Webhook', 'wp-graphql-headless-webhooks' ),
			'edit_item' => __( 'Edit Webhook', 'wp-graphql-headless-webhooks' ),
			'new_item' => __( 'New Webhook', 'wp-graphql-headless-webhooks' ),
			'view_item' => __( 'View Webhook', 'wp-graphql-headless-webhooks' ),
			'search_items' => __( 'Search Webhooks', 'wp-graphql-headless-webhooks' ),
			'not_found' => __( 'No Webhooks found', 'wp-graphql-headless-webhooks' ),
			'not_found_in_trash' => __( 'No Webhooks found in Trash', 'wp-graphql-headless-webhooks' ),
			'parent_item_colon' => __( 'Parent Webhook:', 'wp-graphql-headless-webhooks' ),
			'menu_name' => __( 'Webhooks', 'wp-graphql-headless-webhooks' ),
		];

		$args = [ 
			'labels' => $labels,
			'publicly_queryable' => false,
			'hierarchical' => false,
			'description' => 'Manages GraphQL Webhooks',
			'taxonomies' => [],
			'public' => false,
			'show_ui' => true,
			'show_in_menu' => true,
			'show_in_admin_bar' => false,
			'menu_icon' => 'dashicons-share-alt',
			'show_in_nav_menus' => false,
			'exclude_from_search' => true,
			'has_archive' => false,
			'query_var' => true,
			'can_export' => true,
			'rewrite' => false,
			'capability_type' => 'post',
			'supports' => [ 'title' ],
		];

		register_post_type( 'graphql_webhook', $args );
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
	public function register_webhook_type(string $type, array $args = []): bool {
		if ($type === '') {
			return false;
		}

		if (isset($this->webhook_types[$type])) {
			return false;
		}

		$defaults = [ 
			'label' => $type,
			'description' => '',
			'config' => [],
		];

		$args = wp_parse_args($args, $defaults);
		$this->webhook_types[$type] = $args;

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
	public function get_webhook_type(string $type): ?array {
		return $this->webhook_types[$type] ?? null;
	}

	/**
	 * Creates a new webhook
	 *
	 * @param string $type    Webhook type identifier.
	 * @param string $name    Webhook name/title.
	 * @param array<string, mixed> $config  Webhook configuration.
	 * @return int|\WP_Error Post ID of the new webhook or error.
	 */
	public function create_webhook(string $type, string $name, array $config = []) {
		if (!isset($this->webhook_types[$type])) {
			return new \WP_Error('invalid_webhook_type', __('Invalid webhook type.', 'wp-graphql-headless-webhooks'));
		}

		$post_id = wp_insert_post(
			[ 
				'post_title' => $name,
				'post_type' => 'graphql_webhook',
				'post_status' => 'publish',
			],
			true
		);

		if (is_wp_error($post_id)) {
			return $post_id;
		}

		update_post_meta($post_id, '_webhook_type', $type);

		// Replace empty() with count check for strictness
		if (count($config) > 0) {
			update_post_meta($post_id, '_webhook_config', $config);
		}

		return $post_id;
	}
}
