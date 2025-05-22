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
}