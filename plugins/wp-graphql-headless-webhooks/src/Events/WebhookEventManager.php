<?php

namespace WPGraphQL\Webhooks\Events;

use WPGraphQL\Webhooks\Events\Interfaces\EventManager;
use WPGraphQL\Webhooks\Repository\Interfaces\WebhookRepositoryInterface;
use WPGraphQL\Webhooks\Handlers\Interfaces\Handler;

/**
 * Webhook Event Manager
 *
 * Manages WordPress events and triggers matching webhooks.
 */
class WebhookEventManager implements EventManager {

	private WebhookRepositoryInterface $repository;
	private Handler $handler;

	/**
	 * Constructor
	 *
	 * @param WebhookRepositoryInterface $repository
	 * @param Handler     $sender
	 */
	public function __construct( WebhookRepositoryInterface $repository, $handler ) {
		$this->repository = $repository;
		$this->handler = $handler;
	}

	/**
	 * Register specific WordPress event hooks.
	 */
	public function register_hooks(): void {
		add_action( 'transition_post_status', [ $this, 'on_transition_post_status' ], 10, 3 );
		add_action( 'post_updated', [ $this, 'on_post_updated' ], 10, 3 );
		add_action( 'deleted_post', [ $this, 'on_deleted_post' ], 10, 2 );
		add_action( 'added_post_meta', [ $this, 'on_post_meta_change' ], 10, 4 );
		add_action( 'created_term', [ $this, 'on_term_created' ], 10, 3 );
		add_action( 'set_object_terms', [ $this, 'on_term_assigned' ], 10, 6 );
		add_action( 'delete_term_relationships', [ $this, 'on_term_unassigned' ], 10, 3 );
		add_action( 'delete_term', [ $this, 'on_term_deleted' ], 10, 4 );
		add_action( 'added_term_meta', [ $this, 'on_term_meta_change' ], 10, 4 );
		add_action( 'user_register', [ $this, 'on_user_created' ], 10, 1 );
		add_action( 'deleted_user', [ $this, 'on_user_deleted' ], 10, 2 );
		add_action( 'add_attachment', [ $this, 'on_media_uploaded' ], 10, 1 );
		add_action( 'edit_attachment', [ $this, 'on_media_updated' ], 10, 1 );
		add_action( 'delete_attachment', [ $this, 'on_media_deleted' ], 10, 1 );
		add_action( 'wp_insert_comment', [ $this, 'on_comment_inserted' ], 10, 2 );
		add_action( 'transition_comment_status', [ $this, 'on_comment_status' ], 10, 3 );
		
		// Smart Cache integration
		add_action( 'graphql_purge', [ $this, 'on_graphql_purge' ], 10, 3 );
		add_action( 'wpgraphql_cache_purge_nodes', [ $this, 'on_cache_purge_nodes' ], 10, 2 );
	}

	/**
	 * Triggers webhooks for a given event if it is allowed.
	 *
	 * @param string $event
	 * @param array  $payload
	 */
	private function trigger_webhooks( string $event, array $payload ): void {
		$allowed_events = $this->repository->get_allowed_events();

		if ( ! array_key_exists( $event, $allowed_events ) ) {
			error_log( 'Event ' . $event . ' is not allowed. Allowed events: ' . implode( ', ', $allowed_events ) );
			return;
		}

		do_action( 'graphql_webhooks_before_trigger', $event, $payload );

		foreach ( $this->repository->get_all() as $webhook ) {
			if ( $webhook->event === $event ) {
				$this->handler->handle( $webhook, $payload );
			}
		}

		do_action( 'graphql_webhooks_after_trigger', $event, $payload );
	}

	/** Event Handlers **/

	public function on_transition_post_status( $new_status, $old_status, $post ) {
		if ( $old_status !== 'publish' && $new_status === 'publish' ) {
			$this->trigger_webhooks( 'post_published', [ 'post_id' => $post->ID ] );
		}
	}

	public function on_post_updated( $post_ID, $post_after, $post_before ) {
		$this->trigger_webhooks( 'post_updated', [ 'post_id' => $post_ID ] );

		if ( $post_after->post_author !== $post_before->post_author ) {
			$this->trigger_webhooks( 'user_assigned', [ 
				'post_id' => $post_ID,
				'author_id' => $post_after->post_author,
			] );

			$this->trigger_webhooks( 'user_reassigned', [ 
				'post_id' => $post_ID,
				'old_author_id' => $post_before->post_author,
				'new_author_id' => $post_after->post_author,
			] );
		}
	}

	public function on_deleted_post( $post_ID, $post ) {
		$this->trigger_webhooks( 'post_deleted', [ 'post_id' => $post_ID ] );
	}

	public function on_post_meta_change( $meta_id, $post_id, $meta_key, $meta_value ) {
		$this->trigger_webhooks( 'post_meta_change', [ 
			'post_id' => $post_id,
			'meta_key' => $meta_key,
		] );
	}

	public function on_term_created( $term_id, $tt_id, $taxonomy ) {
		$this->trigger_webhooks( 'term_created', [ 
			'term_id' => $term_id,
			'taxonomy' => $taxonomy,
		] );
	}

	public function on_term_assigned( $object_id, $terms, $tt_ids, $taxonomy, $append, $old_tt_ids ) {
		foreach ( (array) $terms as $term_id ) {
			$this->trigger_webhooks( 'term_assigned', [ 
				'object_id' => $object_id,
				'term_id' => $term_id,
				'taxonomy' => $taxonomy,
			] );
		}
	}

	public function on_term_unassigned( $object_id, $taxonomy, $term_ids ) {
		$this->trigger_webhooks( 'term_unassigned', [ 
			'object_id' => $object_id,
			'taxonomy' => $taxonomy,
			'term_ids' => $term_ids,
		] );
	}

	public function on_term_deleted( $term, $tt_id, $taxonomy, $deleted_term ) {
		$this->trigger_webhooks( 'term_deleted', [ 
			'term_id' => $term,
			'taxonomy' => $taxonomy,
		] );
	}

	public function on_term_meta_change( $meta_id, $term_id, $meta_key, $meta_value ) {
		$this->trigger_webhooks( 'term_meta_change', [ 
			'term_id' => $term_id,
			'meta_key' => $meta_key,
		] );
	}

	public function on_user_created( $user_id ) {
		$this->trigger_webhooks( 'user_created', [ 'user_id' => $user_id ] );
	}

	public function on_user_deleted( $user_id, $reassign ) {
		$this->trigger_webhooks( 'user_deleted', [ 'user_id' => $user_id ] );
	}

	public function on_media_uploaded( $post_id ) {
		$this->trigger_webhooks( 'media_uploaded', [ 'post_id' => $post_id ] );
	}

	public function on_media_updated( $post_id ) {
		$this->trigger_webhooks( 'media_updated', [ 'post_id' => $post_id ] );
	}

	public function on_media_deleted( $post_id ) {
		$this->trigger_webhooks( 'media_deleted', [ 'post_id' => $post_id ] );
	}

	public function on_comment_inserted( $comment_id, $comment_object ) {
		$this->trigger_webhooks( 'comment_inserted', [ 'comment_id' => $comment_id ] );
	}

	public function on_comment_status( $new_status, $old_status, $comment ) {
		$this->trigger_webhooks( 'comment_status', [ 
			'comment_id' => $comment->comment_ID,
			'new_status' => $new_status,
		] );
	}

	/**
	 * Handle WPGraphQL Smart Cache purge events
	 *
	 * @param string $key Cache key being purged
	 * @param string $event Event type (e.g., post_UPDATE)
	 * @param string $graphql_endpoint GraphQL endpoint URL
	 */
	public function on_graphql_purge( $key, $event, $graphql_endpoint ) {
		// Parse the event to extract post type and action
		$event_parts = explode( '_', $event );
		if ( count( $event_parts ) !== 2 ) {
			return;
		}

		$post_type = $event_parts[0];
		$action = strtolower( $event_parts[1] );
		
		// Map Smart Cache actions to our webhook events
		$event_map = [
			'create' => 'smart_cache_created',
			'update' => 'smart_cache_updated',
			'delete' => 'smart_cache_deleted',
		];

		if ( ! isset( $event_map[ $action ] ) ) {
			return;
		}

		$webhook_event = $event_map[ $action ];
		
		// Build payload with decoded information
		$payload = [
			'cache_key' => $key,
			'key_type' => $this->classify_cache_key( $key ),
			'post_type' => $post_type,
			'action' => $action,
			'graphql_endpoint' => $graphql_endpoint,
			'timestamp' => current_time( 'c' ),
		];

		// Try to decode the key if it's a Relay global ID
		if ( class_exists( '\GraphQLRelay\Relay' ) ) {
			try {
				$decoded = \GraphQLRelay\Relay::fromGlobalId( $key );
				if ( ! empty( $decoded['type'] ) && ! empty( $decoded['id'] ) ) {
					$payload['decoded_key'] = $decoded;
					$payload['object_id'] = absint( $decoded['id'] );
					
					// Add object details based on type
					if ( $decoded['type'] === 'post' && $action !== 'delete' ) {
						$post = get_post( $decoded['id'] );
						if ( $post ) {
							$payload['object'] = [
								'id' => $post->ID,
								'title' => $post->post_title,
								'status' => $post->post_status,
								'type' => $post->post_type,
								'url' => get_permalink( $post ),
							];
						}
					}
				}
			} catch ( \Exception $e ) {
				// Not a valid Relay ID, continue without decoding
			}
		}

		$this->trigger_webhooks( $webhook_event, $payload );
	}

	/**
	 * Handle WPGraphQL cache purge nodes event
	 *
	 * @param string $key Cache key
	 * @param array $nodes Nodes being purged
	 */
	public function on_cache_purge_nodes( $key, $nodes ) {
		$payload = [
			'cache_key' => $key,
			'nodes' => $nodes,
			'nodes_count' => count( $nodes ),
			'timestamp' => current_time( 'c' ),
		];

		$this->trigger_webhooks( 'smart_cache_nodes_purged', $payload );
	}

	/**
	 * Classify the type of cache key
	 *
	 * @param string $key Cache key
	 * @return string
	 */
	private function classify_cache_key( string $key ): string {
		if ( strpos( $key, 'list:' ) === 0 ) {
			return 'list';
		}
		
		if ( strpos( $key, 'skipped:' ) === 0 ) {
			return 'skipped';
		}
		
		// Check if it's a Relay ID
		if ( class_exists( '\GraphQLRelay\Relay' ) ) {
			try {
				$decoded = \GraphQLRelay\Relay::fromGlobalId( $key );
				if ( ! empty( $decoded['type'] ) && ! empty( $decoded['id'] ) ) {
					return 'relay_id';
				}
			} catch ( \Exception $e ) {
				// Not a valid Relay ID
			}
		}
		
		return 'unknown';
	}
}