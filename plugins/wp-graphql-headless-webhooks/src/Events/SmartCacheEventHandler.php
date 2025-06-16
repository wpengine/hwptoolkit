<?php

namespace WPGraphQL\Webhooks\Events;

use GraphQLRelay\Relay;

/**
 * Handles Smart Cache events and consolidates them before triggering webhooks
 * 
 * This is a lightweight handler that simply consolidates multiple cache purge
 * events into single webhook triggers to avoid webhook spam.
 */
class SmartCacheEventHandler {
	/**
	 * Stores Smart Cache events temporarily to consolidate them
	 * @var array
	 */
	private array $buffer = [];

	/**
	 * Timer to process buffered Smart Cache events
	 * @var int|false
	 */
	private $timer = false;

	/**
	 * Callback to trigger webhooks
	 * @var callable
	 */
	private $webhook_trigger_callback;

	/**
	 * Event action mapping
	 * @var array
	 */
	private const EVENT_MAP = [
		'create' => 'smart_cache_created',
		'update' => 'smart_cache_updated',
		'delete' => 'smart_cache_deleted',
	];

	/**
	 * Constructor
	 *
	 * @param $webhook_trigger_callback Callback to trigger webhooks
	 */
	public function __construct( $webhook_trigger_callback ) {
		$this->webhook_trigger_callback = $webhook_trigger_callback;
	}

	/**
	 * Initialize hooks
	 */
	public function init() {
		add_action( 'graphql_purge', [ $this, 'handle_graphql_purge' ], 10, 3 );
		add_action( 'wpgraphql_cache_purge_nodes', [ $this, 'handle_cache_purge_nodes' ], 10, 2 );
		add_action( 'shutdown', [ $this, 'process_buffer' ] );
	}

	/**
	 * Handle graphql_purge event
	 *
	 * @param string $key Cache key being purged
	 * @param string $event Event type (e.g., post_UPDATE)
	 * @param string $graphql_endpoint GraphQL endpoint URL
	 */
	public function handle_graphql_purge( $key, $event, $graphql_endpoint ) {
		$parts = explode( '_', $event );
		if ( count( $parts ) !== 2 ) {
			return;
		}

		$post_type = $parts[0];
		$action = strtolower( $parts[1] );
		$buffer_key = "{$post_type}_{$action}";
		
		if ( ! isset( $this->buffer[ $buffer_key ] ) ) {
			$this->buffer[ $buffer_key ] = [
				'post_type' => $post_type,
				'action' => $action,
				'graphql_endpoint' => $graphql_endpoint,
				'keys' => [],
			];
		}
		
		// Just store the key - let webhook consumers decode if needed
		$this->buffer[ $buffer_key ]['keys'][] = $key;
		
		// Schedule processing if not already scheduled
		if ( $this->timer === false ) {
			$this->timer = wp_schedule_single_event( time() + 1, 'wpgraphql_webhooks_process_smart_cache' );
			add_action( 'wpgraphql_webhooks_process_smart_cache', [ $this, 'process_buffer' ] );
		}
	}

	/**
	 * Handle cache purge nodes event - this fires immediately
	 *
	 * @param string $key Cache key
	 * @param array $nodes Nodes being purged
	 */
	public function handle_cache_purge_nodes( $key, $nodes ) {
		// This event provides the actual nodes being purged, so we can fire immediately
		$payload = [
			'cache_key' => $key,
			'nodes' => $nodes,
			'timestamp' => current_time( 'c' ),
		];

		call_user_func( $this->webhook_trigger_callback, 'smart_cache_nodes_purged', $payload );
	}

	/**
	 * Process the buffered events
	 */
	public function process_buffer() {
		if ( empty( $this->buffer ) ) {
			return;
		}
		
		foreach ( $this->buffer as $data ) {
			$webhook_event = self::EVENT_MAP[ $data['action'] ] ?? null;
			if ( ! $webhook_event ) {
				continue;
			}
			
			// Decode cache keys to get actual post IDs
			$decoded_items = [];
			$paths = [];
			
			foreach ( $data['keys'] as $key ) {
				// WPGraphQL cache keys are base64 encoded global IDs
				// Format is typically: base64("post:123") or base64("page:456")
				$decoded = base64_decode( $key );
				if ( $decoded && strpos( $decoded, ':' ) !== false ) {
					list( $type, $id ) = explode( ':', $decoded, 2 );
					
					// Get the post data
					if ( $type === 'post' || $type === 'page' ) {
						$post = get_post( $id );
						if ( $post ) {
							$uri = str_replace( home_url(), '', get_permalink( $post ) );
							$path = '/' . trim( $uri, '/' ) . '/';
							
							$decoded_items[] = [
								'id' => $post->ID,
								'title' => $post->post_title,
								'slug' => $post->post_name,
								'uri' => $uri,
								'path' => $path,
								'type' => $post->post_type,
								'status' => $post->post_status,
							];
							
							$paths[] = $path;
						}
					}
				}
			}
			
			// Enhanced payload with decoded post data
			$payload = [
				'post_type' => $data['post_type'],
				'action' => $data['action'],
				'graphql_endpoint' => $data['graphql_endpoint'],
				'cache_keys' => array_unique( $data['keys'] ), // Original cache keys
				'cache_keys_count' => count( array_unique( $data['keys'] ) ),
				'posts' => $decoded_items, // Decoded post data
				'paths' => array_unique( $paths ), // Paths for ISR revalidation
				'timestamp' => current_time( 'c' ),
			];
			
			// If there's only one post, add it as the primary post/path for compatibility
			if ( count( $decoded_items ) === 1 ) {
				$payload['post'] = $decoded_items[0];
				$payload['path'] = $decoded_items[0]['path'];
			}
			
			call_user_func( $this->webhook_trigger_callback, $webhook_event, $payload );
		}
		
		$this->buffer = [];
		$this->timer = false;
	}
}
