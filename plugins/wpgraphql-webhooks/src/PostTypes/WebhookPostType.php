<?php
/**
 * Webhook CPT class
 *
 * @package WPGraphQL\Webhooks
 */

namespace WPGraphQL\Webhooks\PostTypes;

/**
 * Class WebhookPostType
 */
class WebhookPostType {

	/**
	 * Initialize the Webhook CPT
	 */
	public static function init(): void {
		add_action( 'init', [ self::class, 'register_webhook_cpt' ], 5, 0 );
	}

	/**
	 * Register the webhook CPT
	 */
	public static function register_webhook_cpt(): void {
		$labels = [ 
			'name' => __( 'Webhooks', 'wpgraphql-webhooks' ),
			'singular_name' => __( 'Webhook', 'wpgraphql-webhooks' ),
			'add_new' => __( 'Add New', 'wpgraphql-webhooks' ),
			'add_new_item' => __( 'Add New Webhook', 'wpgraphql-webhooks' ),
			'edit_item' => __( 'Edit Webhook', 'wpgraphql-webhooks' ),
			'new_item' => __( 'New Webhook', 'wpgraphql-webhooks' ),
			'view_item' => __( 'View Webhook', 'wpgraphql-webhooks' ),
			'search_items' => __( 'Search Webhooks', 'wpgraphql-webhooks' ),
			'not_found' => __( 'No Webhooks found', 'wpgraphql-webhooks' ),
			'not_found_in_trash' => __( 'No Webhooks found in Trash', 'wpgraphql-webhooks' ),
			'parent_item_colon' => __( 'Parent Webhook:', 'wpgraphql-webhooks' ),
			'menu_name' => __( 'Webhooks', 'wpgraphql-webhooks' ),
		];
		$args = [ 
			'labels' => $labels,
			'publicly_queryable' => false,
			'hierarchical' => false,
			'description' => 'Manages GraphQL Webhooks',
			'taxonomies' => [],
			'public' => false,
			'show_ui' => false,
			'show_in_menu' => false,
			'show_in_admin_bar' => false,
			'menu_icon' => 'dashicons-share-alt',
			'show_in_nav_menus' => false,
			'exclude_from_search' => true,
			'has_archive' => false,
			'query_var' => true,
			'can_export' => true,
			'rewrite' => false,
			'capability_type' => 'webhook',
			'supports' => [ 'title' ],
			'capabilities' => [ 
				'create_posts' => 'manage_options',
				'edit_posts' => 'manage_options',
				'edit_post' => 'manage_options',
				'delete_posts' => 'manage_options',
				'delete_post' => 'manage_options',
				'read_post' => 'manage_options',
				'read_private_posts' => 'manage_options',
			],
			'map_meta_cap' => false,
		];

		register_post_type( 'graphql_webhook', $args );
	}
}