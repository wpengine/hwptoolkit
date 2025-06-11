<?php

namespace WPGraphQL\Webhooks\Events;

use GraphQLRelay\Relay;

/**
 * Handles Smart Cache events and consolidates them before triggering webhooks
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
	 * @param callable $webhook_trigger_callback Callback to trigger webhooks
	 */
	public function __construct( callable $webhook_trigger_callback ) {
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
		$parsed = $this->parse_event( $event );
		if ( ! $parsed ) {
			return;
		}

		$this->buffer_event( $key, $parsed['post_type'], $parsed['action'], $graphql_endpoint );
	}

	/**
	 * Handle cache purge nodes event
	 *
	 * @param string $key Cache key
	 * @param array $nodes Nodes being purged
	 */
	public function handle_cache_purge_nodes( $key, $nodes ) {
		$payload = [
			'cache_key' => $key,
			'nodes' => $nodes,
			'nodes_count' => count( $nodes ),
			'timestamp' => current_time( 'c' ),
		];

		call_user_func( $this->webhook_trigger_callback, 'smart_cache_nodes_purged', $payload );
	}

	/**
	 * Parse event string into components
	 *
	 * @param string $event Event string (e.g., post_UPDATE)
	 * @return array|null Array with 'post_type' and 'action' keys, or null if invalid
	 */
	private function parse_event( string $event ): ?array {
		$parts = explode( '_', $event );
		if ( count( $parts ) !== 2 ) {
			return null;
		}

		return [
			'post_type' => $parts[0],
			'action' => strtolower( $parts[1] ),
		];
	}

	/**
	 * Buffer an event for consolidated processing
	 *
	 * @param string $key Cache key
	 * @param string $post_type Post type
	 * @param string $action Action (create, update, delete)
	 * @param string $graphql_endpoint GraphQL endpoint URL
	 */
	private function buffer_event( string $key, string $post_type, string $action, string $graphql_endpoint ) {
		$buffer_key = "{$post_type}_{$action}";
		
		if ( ! isset( $this->buffer[ $buffer_key ] ) ) {
			$this->buffer[ $buffer_key ] = [
				'post_type' => $post_type,
				'action' => $action,
				'graphql_endpoint' => $graphql_endpoint,
				'keys' => [],
				'objects' => [],
			];
		}
		
		$key_info = $this->analyze_cache_key( $key );
		
		// Extract object information if it's a Relay ID
		if ( $key_info['type'] === 'relay_id' && isset( $key_info['decoded'] ) ) {
			$this->add_object_to_buffer( $buffer_key, $key_info['decoded'], $action );
		}
		
		$this->buffer[ $buffer_key ]['keys'][] = $key_info;
		$this->schedule_processing();
	}

	/**
	 * Analyze a cache key and determine its type
	 *
	 * @param string $key Cache key
	 * @return array Key information with 'key', 'type', and optionally 'decoded'
	 */
	private function analyze_cache_key( string $key ): array {
		$info = [
			'key' => $key,
			'type' => $this->classify_key_type( $key ),
		];
		
		// Try to decode Relay IDs
		if ( $info['type'] === 'relay_id' && class_exists( Relay::class ) ) {
			try {
				$decoded = Relay::fromGlobalId( $key );
				if ( ! empty( $decoded['type'] ) && ! empty( $decoded['id'] ) ) {
					$info['decoded'] = $decoded;
				}
			} catch ( \Exception $e ) {
				// Not a valid Relay ID after all
				$info['type'] = 'unknown';
			}
		}
		
		return $info;
	}

	/**
	 * Classify the type of cache key
	 *
	 * @param string $key Cache key
	 * @return string Key type: 'list', 'skipped', 'relay_id', or 'unknown'
	 */
	private function classify_key_type( string $key ): string {
		if ( strpos( $key, 'list:' ) === 0 ) {
			return 'list';
		}
		
		if ( strpos( $key, 'skipped:' ) === 0 ) {
			return 'skipped';
		}
		
		// Assume it might be a Relay ID if it looks like base64
		if ( preg_match( '/^[A-Za-z0-9+\/]+=*$/', $key ) ) {
			return 'relay_id';
		}
		
		return 'unknown';
	}

	/**
	 * Add object data to buffer
	 *
	 * @param string $buffer_key Buffer key
	 * @param array $decoded Decoded Relay ID data
	 * @param string $action Action being performed
	 */
	private function add_object_to_buffer( string $buffer_key, array $decoded, string $action ) {
		$object_key = "{$decoded['type']}:{$decoded['id']}";
		
		if ( isset( $this->buffer[ $buffer_key ]['objects'][ $object_key ] ) ) {
			return; // Already added
		}
		
		$object_data = $this->fetch_object_data( $decoded['type'], (int) $decoded['id'], $action );
		if ( $object_data ) {
			$this->buffer[ $buffer_key ]['objects'][ $object_key ] = $object_data;
		}
	}

	/**
	 * Fetch object data based on type and ID
	 *
	 * @param string $type Object type
	 * @param int    $id   Object ID
	 * @param string $action The action being performed
	 * @return array|null Object data or null if not found
	 */
	private function fetch_object_data( string $type, int $id, string $action ): ?array {
		// For delete actions, just return minimal data
		if ( $action === 'delete' ) {
			return [
				'id' => $id,
				'type' => $type,
				'deleted' => true,
			];
		}
		
		$fetchers = [
			'post' => [ $this, 'fetch_post_data' ],
			'term' => [ $this, 'fetch_term_data' ],
			'user' => [ $this, 'fetch_user_data' ],
		];
		
		if ( isset( $fetchers[ $type ] ) ) {
			return call_user_func( $fetchers[ $type ], $id );
		}
		
		return null;
	}

	/**
	 * Fetch post data
	 *
	 * @param int $id Post ID
	 * @return array|null
	 */
	private function fetch_post_data( int $id ): ?array {
		$post = get_post( $id );
		if ( ! $post ) {
			return null;
		}
		
		return [
			'id' => $post->ID,
			'title' => $post->post_title,
			'status' => $post->post_status,
			'type' => $post->post_type,
			'url' => get_permalink( $post ),
		];
	}

	/**
	 * Fetch term data
	 *
	 * @param int $id Term ID
	 * @return array|null
	 */
	private function fetch_term_data( int $id ): ?array {
		$term = get_term( $id );
		if ( ! $term || is_wp_error( $term ) ) {
			return null;
		}
		
		return [
			'id' => $term->term_id,
			'name' => $term->name,
			'taxonomy' => $term->taxonomy,
			'url' => get_term_link( $term ),
		];
	}

	/**
	 * Fetch user data
	 *
	 * @param int $id User ID
	 * @return array|null
	 */
	private function fetch_user_data( int $id ): ?array {
		$user = get_user_by( 'id', $id );
		if ( ! $user ) {
			return null;
		}
		
		return [
			'id' => $user->ID,
			'login' => $user->user_login,
			'display_name' => $user->display_name,
			'url' => get_author_posts_url( $user->ID ),
		];
	}

	/**
	 * Schedule buffer processing
	 */
	private function schedule_processing() {
		if ( $this->timer !== false ) {
			return; // Already scheduled
		}
		
		$this->timer = wp_schedule_single_event( time() + 1, 'wpgraphql_webhooks_process_smart_cache' );
		add_action( 'wpgraphql_webhooks_process_smart_cache', [ $this, 'process_buffer' ] );
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
			
			$payload = $this->build_payload( $data );
			call_user_func( $this->webhook_trigger_callback, $webhook_event, $payload );
		}
		
		$this->buffer = [];
		$this->timer = false;
	}

	/**
	 * Build webhook payload from buffered data
	 *
	 * @param array $data Buffered event data
	 * @return array
	 */
	private function build_payload( array $data ): array {
		return [
			'post_type' => $data['post_type'],
			'action' => $data['action'],
			'graphql_endpoint' => $data['graphql_endpoint'],
			'timestamp' => current_time( 'c' ),
			'cache_keys_purged' => count( $data['keys'] ),
			'objects_affected' => array_values( $data['objects'] ),
			'cache_key_summary' => $this->summarize_keys( $data['keys'] ),
		];
	}

	/**
	 * Summarize cache keys by type
	 *
	 * @param array $keys Array of key information
	 * @return array Summary with counts by type
	 */
	private function summarize_keys( array $keys ): array {
		$summary = array_fill_keys( [ 'relay_id', 'list', 'skipped', 'unknown' ], 0 );
		
		foreach ( $keys as $key_info ) {
			$summary[ $key_info['type'] ]++;
		}
		
		return array_filter( $summary ); // Remove zeros
	}
}
