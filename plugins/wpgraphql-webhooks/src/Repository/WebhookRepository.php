<?php
namespace WPGraphQL\Webhooks\Repository;

use WPGraphQL\Webhooks\Entity\Webhook;
use WPGraphQL\Webhooks\Events\SmartCacheEventMapper;
use WPGraphQL\Webhooks\Repository\Interfaces\WebhookRepositoryInterface;
use WP_Error;
use WP_Post;

/**
 * Class WebhookRepository
 *
 * Implements CRUD operations for webhook Custom Post Types (CPT) as Webhook entities.
 * Provides methods to create, read, update, delete, and validate webhook configurations.
 *
 * This class manages webhooks stored as WordPress posts of type 'graphql_webhook'.
 *
 * @package WPGraphQL\Webhooks\Repository
 */
class WebhookRepository implements WebhookRepositoryInterface {

	/**
	 * Allowed event keys and labels for UI.
	 *
	 * @var array<string, string>
	 */
	private $default_events = [ 
		'post_published' => 'Post Published',
		'post_updated' => 'Post Updated',
		'post_deleted' => 'Post Deleted',
		'post_meta_change' => 'Post Meta Changed',
		'term_created' => 'Term Created',
		'term_assigned' => 'Term Assigned to Post',
		'term_unassigned' => 'Term Unassigned from Post',
		'term_deleted' => 'Term Deleted',
		'term_meta_change' => 'Term Meta Changed',
		'user_created' => 'User Created',
		'user_assigned' => 'User Assigned as Author',
		'user_deleted' => 'User Deleted',
		'user_reassigned' => 'User Author Reassigned',
		'media_uploaded' => 'Media Uploaded',
		'media_updated' => 'Media Updated',
		'media_deleted' => 'Media Deleted',
		'comment_inserted' => 'Comment Inserted',
		'comment_status' => 'Comment Status Changed',
	];

	/**
	 * Get the list of allowed webhook events.
	 *
	 * @return array<string, string> Associative array of event keys and labels.
	 */
	public function get_allowed_events(): array {
		$default_events = $this->default_events;
		$mapped_events = SmartCacheEventMapper::getMappedEvents();
		$filtered_events = array_intersect_key( $default_events, $mapped_events );

		return apply_filters( 'graphql_webhooks_allowed_events', $filtered_events );
	}

	/**
	 * Get the list of allowed HTTP methods.
	 *
	 * @return array<string> Array of allowed HTTP methods.
	 */
	public function get_allowed_methods(): array {
		$methods = [ 'POST', 'GET' ];
		return apply_filters( 'graphql_webhooks_allowed_methods', $methods );
	}

	/**
	 * Retrieve all published webhook entities.
	 *
	 * @return Webhook[] Array of Webhook entity objects.
	 */
	public function get_all(): array {
		$webhooks = [];

		$posts = get_posts( [ 
			'post_type' => 'graphql_webhook',
			'post_status' => 'publish',
			'numberposts' => -1,
		] );

		foreach ( $posts as $post ) {
			$webhooks[] = $this->mapPostToEntity( $post );
		}

		return $webhooks;
	}

	/**
	 * Retrieve a webhook entity by its post ID.
	 *
	 * @param int $id The post ID of the webhook.
	 * @return Webhook|null The Webhook entity or null if not found or invalid post type.
	 */
	public function get( $id ): ?Webhook {
		$post = get_post( $id );
		if ( ! $post || $post->post_type !== 'graphql_webhook' ) {
			return null;
		}
		return $this->mapPostToEntity( $post );
	}

	/**
	 * Create a new webhook entity.
	 *
	 * @param Webhook $webhook The webhook entity to create.
	 *
	 * @return int|WP_Error Post ID on success, or WP_Error on failure.
	 */
	public function create( Webhook $webhook ) {
		$validation = $this->validate( $webhook );
		if ( is_wp_error( $validation ) ) {
			return $validation;
		}

		$postId = wp_insert_post( [ 
			'post_title' => $webhook->name,
			'post_type' => 'graphql_webhook',
			'post_status' => 'publish',
		], true );

		if ( is_wp_error( $postId ) ) {
			return $postId;
		}

		update_post_meta( $postId, '_webhook_event', sanitize_text_field( $webhook->event ) );
		update_post_meta( $postId, '_webhook_url', esc_url_raw( $webhook->url ) );
		update_post_meta( $postId, '_webhook_method', strtoupper( $webhook->method ) );
		update_post_meta( $postId, '_webhook_headers', wp_json_encode( $webhook->headers ) );

		return $postId;
	}

	/**
	 * Update an existing webhook entity.
	 *
	 * @param int     $id      Post ID of the webhook to update.
	 * @param Webhook $webhook The webhook entity with updated data.
	 *
	 * @return bool|WP_Error True on success, or WP_Error on failure.
	 */
	public function update( int $id, Webhook $webhook ) {
		$post = get_post( $id );
		if ( ! $post || $post->post_type !== 'graphql_webhook' ) {
			return new WP_Error( 'invalid_webhook', __( 'Webhook not found.', 'graphql-webhooks' ) );
		}

		// Validate using the Webhook entity
		$validation = $this->validate( $webhook );
		if ( is_wp_error( $validation ) ) {
			return $validation;
		}

		$postData = [ 
			'ID' => $id,
			'post_title' => sanitize_text_field( $webhook->name ),
		];

		$updated = wp_update_post( $postData, true );
		if ( is_wp_error( $updated ) ) {
			return $updated;
		}

		update_post_meta( $id, '_webhook_event', sanitize_text_field( $webhook->event ) );
		update_post_meta( $id, '_webhook_url', esc_url_raw( $webhook->url ) );
		update_post_meta( $id, '_webhook_method', strtoupper( $webhook->method ) );
		update_post_meta( $id, '_webhook_headers', wp_json_encode( $webhook->headers ) );

		return true;
	}

	/**
	 * Delete a webhook entity by post ID.
	 *
	 * @param int $id Post ID of the webhook to delete.
	 * @return bool True if deleted, false otherwise.
	 */
	public function delete( $id ): bool {
		$post = get_post( $id );
		if ( ! $post || $post->post_type !== 'graphql_webhook' ) {
			return false;
		}

		$deleted = wp_delete_post( $id, true );

		return (bool) $deleted;
	}

	/**
	 * Validate webhook entity before creation or update.
	 *
	 * @param Webhook $webhook The webhook entity to validate.
	 * @return bool|WP_Error True if valid, WP_Error if invalid.
	 */
	public function validate( Webhook $webhook ) {
		$event = $webhook->event;
		$url = $webhook->url;
		$method = $webhook->method;

		if ( ! isset( $this->get_allowed_events()[ $event ] ) ) {
			return new WP_Error( 'invalid_event', 'Invalid event type.' );
		}
		if ( ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
			return new WP_Error( 'invalid_url', 'Invalid URL.' );
		}
		if ( ! in_array( strtoupper( $method ), $this->get_allowed_methods(), true ) ) {
			return new WP_Error( 'invalid_method', 'Invalid HTTP method.' );
		}
		return apply_filters( 'graphql_webhooks_validate_data', true, $event, $url, $method );
	}

	/**
	 * Map a WP_Post object to a Webhook entity.
	 *
	 * @param WP_Post $post The webhook post object.
	 *
	 * @return Webhook The mapped Webhook entity.
	 */
	private function mapPostToEntity( WP_Post $post ) {
		$event = get_post_meta( $post->ID, '_webhook_event', true );
		$url = get_post_meta( $post->ID, '_webhook_url', true );
		$method = get_post_meta( $post->ID, '_webhook_method', true ) ?: 'POST';
		$headers = get_post_meta( $post->ID, '_webhook_headers', true );
		$headers = $headers ? json_decode( $headers, true ) : [];

		return new Webhook(
			$post->ID,
			$post->post_title,
			$event,
			$url,
			$method,
			$headers
		);
	}
}
