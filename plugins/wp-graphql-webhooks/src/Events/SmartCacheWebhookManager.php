<?php

namespace WPGraphQL\Webhooks\Events;

use GraphQLRelay\Relay;
use WPGraphQL\Webhooks\Events\Interfaces\EventManager;
use WPGraphQL\Webhooks\Repository\Interfaces\WebhookRepositoryInterface;
use WPGraphQL\Webhooks\Handlers\Interfaces\Handler;

/**
 * Smart Cache Webhook Manager
 *
 * Listens to WPGraphQL Smart Cache purge events and triggers webhooks
 */
class SmartCacheWebhookManager implements EventManager {

	private WebhookRepositoryInterface $repository;
	private Handler $handler;

	public function __construct( WebhookRepositoryInterface $repository, Handler $handler ) {
		$this->repository = $repository;
		$this->handler = $handler;
	}

	/**
	 * Register Smart Cache purge hooks
	 */
	public function register_hooks(): void {
		add_action( 'wpgraphql_cache_purge_nodes', [ $this, 'handle_purge_nodes' ], 10, 2 );
		add_action( 'graphql_purge', [ $this, 'handle_graphql_purge' ], 10, 3 );
	}

	/**
	 * Handle node purge events from Smart Cache
	 */
	public function handle_purge_nodes( string $key, array $nodes ): void {
		error_log( "[Webhook] handle_purge_nodes - Key: $key, Node count: " . count( $nodes ) );

		// Handle empty nodes array
		if ( empty( $nodes ) ) {
			error_log( "[Webhook] handle_purge_nodes - No nodes provided for key: $key" );
			return;
		}

		$node_type = $nodes[0]['type'] ?? null;

		if ( empty( $node_type ) ) {
			error_log( "[Webhook] handle_purge_nodes - No node type found in first node for key: $key" );
			return;
		}

		$event = SmartCacheEventMapper::mapEvent( strtolower( $node_type ) );

		if ( $event === null ) {
			error_log( "[Webhook] handle_purge_nodes - No mapped event found for node type: $node_type" );
			return;
		}

		error_log( "[Webhook] handle_purge_nodes - Mapped '$node_type' to event: $event" );

		$path = $this->get_path_from_key( $key );
		$smart_cache_keys = $this->get_smart_cache_keys( $nodes );

		$this->trigger_webhooks( $event, [ 
			'key' => $key,
			'path' => $path,
			'nodes' => $nodes,
			'smart_cache_keys' => $smart_cache_keys
		] );
	}

	/**
	 * Handle general purge events from Smart Cache
	 */
	public function handle_graphql_purge( string $key, string $event, string $graphql_endpoint ): void {
		error_log( "[Webhook] handle_graphql_purge - Key: $key, Event: $event, Endpoint: $graphql_endpoint" );

		// Skip special prefixed keys (they're not actual entity IDs)
		if ( strpos( $key, 'skipped:' ) === 0 || strpos( $key, 'list:' ) === 0 ) {
			error_log( "[Webhook] Skipping webhook trigger for special key: $key" );
			return;
		}
		$mapped_event = SmartCacheEventMapper::mapEvent( strtolower( $event ) );

		if ( $mapped_event === null ) {
			error_log( "[Webhook] handle_graphql_purge - No mapped event found for Smart Cache event: $event" );
			return;
		}

		error_log( "[Webhook] handle_graphql_purge - Mapped '$event' to event: $mapped_event" );

		$path = $this->get_path_from_key( $key );

		$this->trigger_webhooks( $mapped_event, [ 
			'key' => $key,
			'path' => $path,
			'graphql_endpoint' => $graphql_endpoint,
			'smart_cache_keys' => [ $key ]
		] );
	}

	/**
	 * Trigger webhooks with Smart Cache formatted payload
	 */
	private function trigger_webhooks( string $event, array $payload ): void {
		// Event is already mapped, no need to map again
		$allowed_events = $this->repository->get_allowed_events();

		if ( ! array_key_exists( $event, $allowed_events ) ) {
			error_log( "[Webhook] Event '$event' is not in allowed events list." );
			return;
		}

		// Set uri fallback if smart_cache_keys is empty
		if ( empty( $payload['smart_cache_keys'] ) ) {
			$payload['uri'] = $payload['path'] ?? '';
		}

		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( "[Webhook] Triggering webhooks for event: $event with payload: " . var_export( $payload, true ) );
		}

		do_action( 'graphql_webhooks_before_trigger', $event, $payload );

		$webhooks = $this->repository->get_all();
		error_log( "[Webhook] Found " . count( $webhooks ) . " webhooks for event: $event" );
		$triggered_count = 0;

		foreach ( $webhooks as $webhook ) {
			if ( $webhook->event === $event ) {
				$this->handler->handle( $webhook, $payload );
				$triggered_count++;
			}
		}

		error_log( "[Webhook] Triggered $triggered_count webhooks for event: $event" );

		do_action( 'graphql_webhooks_after_trigger', $event, $payload );
	}

	/**
	 * Extract Smart Cache keys from nodes
	 */
	private function get_smart_cache_keys( array $nodes ): array {
		$keys = [];

		foreach ( $nodes as $node ) {
			if ( isset( $node['id'] ) && ! empty( $node['id'] ) ) {
				$keys[] = $node['id'];
			} elseif ( isset( $node['databaseId'] ) && ! empty( $node['databaseId'] ) ) {
				// Fallback to databaseId if id is not available
				$keys[] = $node['databaseId'];
			}
		}

		return array_filter( $keys ); // Remove empty values
	}

	/**
	 * Get the path from the key
	 *
	 * Supports all post types, terms, users, and falls back gracefully.
	 * Handles special prefixed keys like 'skipped:post', 'list:post', etc.
	 *
	 * @param string $key The key to get the path from
	 *
	 * @return string
	 */
	public function get_path_from_key( $key ) {
		$path = '';

		if ( empty( $key ) ) {
			error_log( "[Webhook] Empty key provided to get_path_from_key" );
			return $path;
		}

		// Handle special prefixed keys (skipped:, list:, etc.)
		if ( strpos( $key, ':' ) !== false ) {
			error_log( "[Webhook] Prefixed key detected: $key - cannot generate path for non-entity keys" );
			return $path;
		}

		try {
			$node_id = Relay::fromGlobalId( $key );
		} catch (Exception $e) {
			error_log( "[Webhook] Failed to decode GraphQL global ID: $key - " . $e->getMessage() );
			return $path;
		}

		$node_type = $node_id['type'] ?? null;
		$database_id = $node_id['id'] ?? null;

		if ( empty( $node_type ) || empty( $database_id ) ) {
			error_log( "[Webhook] Invalid node ID structure for key: $key (type: $node_type, id: $database_id)" );
			return $path;
		}

		$permalink = null;
		error_log( "[Webhook] Processing key: $key (type: $node_type, database_id: $database_id)" );

		switch ( $node_type ) {
			case 'post':
			case 'page':
			default:
				$post_id = absint( $database_id );
				if ( $post_id > 0 ) {
					$post = get_post( $post_id );
					if ( $post && ! is_wp_error( $post ) ) {
						$permalink = get_permalink( $post_id );
						error_log( "[Webhook] Generated permalink for post $post_id: $permalink" );
					} else {
						error_log( "[Webhook] Post not found or error for ID: $post_id" );
					}
				}
				break;

			case 'term':
				$term_id = absint( $database_id );
				if ( $term_id > 0 ) {
					$term = get_term( $term_id );
					if ( $term && ! is_wp_error( $term ) ) {
						$permalink = get_term_link( $term_id );
						error_log( "[Webhook] Generated permalink for term $term_id: $permalink" );
					} else {
						error_log( "[Webhook] Term not found or error for ID: $term_id" );
					}
				}
				break;

			case 'user':
				$user_id = absint( $database_id );
				if ( $user_id > 0 ) {
					$user = get_user_by( 'id', $user_id );
					if ( $user instanceof \WP_User ) {
						$permalink = home_url( '/author/' . $user->user_nicename . '/' );
						error_log( "[Webhook] Generated permalink for user $user_id: $permalink" );
					} else {
						error_log( "[Webhook] User not found for ID: $user_id" );
					}
				}
				break;
		}

		if ( ! empty( $permalink ) && is_string( $permalink ) && ! is_wp_error( $permalink ) ) {
			$parsed_path = wp_parse_url( $permalink, PHP_URL_PATH );
			if ( $parsed_path !== false ) {
				$path = $parsed_path;
				error_log( "[Webhook] Final path for key $key: $path" );
			} else {
				error_log( "[Webhook] Failed to parse URL path from permalink: $permalink" );
			}
		} else {
			error_log( "[Webhook] No valid permalink generated for key: $key" );
		}

		return $path;
	}
}