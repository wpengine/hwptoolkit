<?php
namespace WPGraphQL\Webhooks\Repository;

use WPGraphQL\Webhooks\Entity\Webhook;
use WPGraphQL\Webhooks\Repository\Interfaces\WebhookRepositoryInterface;
use WP_Error;
use WP_Post;

/**
 * Class WebhookRepository
 *
 * Implements CRUD operations for webhook CPT posts as Webhook entities.
 */
class WebhookRepository implements WebhookRepositoryInterface {

	/**
	 * Allowed event keys and labels for UI.
	 */
	private array $default_events = array(
		'post_published'   => 'Post Published',
		'post_updated'     => 'Post Updated',
		'post_deleted'     => 'Post Deleted',
		'post_meta_change' => 'Post Meta Changed',
		'term_created'     => 'Term Created',
		'term_assigned'    => 'Term Assigned to Post',
		'term_unassigned'  => 'Term Unassigned from Post',
		'term_deleted'     => 'Term Deleted',
		'term_meta_change' => 'Term Meta Changed',
		'user_created'     => 'User Created',
		'user_assigned'    => 'User Assigned as Author',
		'user_deleted'     => 'User Deleted',
		'user_reassigned'  => 'User Author Reassigned',
		'media_uploaded'   => 'Media Uploaded',
		'media_updated'    => 'Media Updated',
		'media_deleted'    => 'Media Deleted',
		'comment_inserted' => 'Comment Inserted',
		'comment_status'   => 'Comment Status Changed',
	);

	public function get_allowed_events(): array {
		return apply_filters( 'graphql_webhooks_allowed_events', $this->default_events );
	}

	/**
	 * Get allowed HTTP methods for webhooks.
	 *
	 * @return array<string, string> Array of method values and labels.
	 */
	public function get_allowed_methods(): array {
		return apply_filters(
			'graphql_webhooks_allowed_methods',
			array(
				'POST' => __( 'POST', 'wp-graphql-headless-webhooks' ),
				'GET'  => __( 'GET', 'wp-graphql-headless-webhooks' ),
			)
		);
	}

	public function get_all(): array {
		$webhooks = array();

		$posts = get_posts(
			array(
				'post_type'   => 'graphql_webhook',
				'post_status' => 'publish',
				'numberposts' => -1,
			)
		);

		foreach ( $posts as $post ) {
			$webhooks[] = $this->mapPostToEntity( $post );
		}

		return $webhooks;
	}

	public function get( int $id ): ?Webhook {
		$post = get_post( $id );
		if ( ! $post || $post->post_type !== 'graphql_webhook' ) {
			return null;
		}
		return $this->mapPostToEntity( $post );
	}

	public function create( string $name, string $event, string $url, string $method, array $headers ): int|WP_Error {
		$validation = $this->validate_data( $event, $url, $method );
		if ( is_wp_error( $validation ) ) {
			return $validation;
		}

		$postId = wp_insert_post(
			array(
				'post_title'  => $name,
				'post_type'   => 'graphql_webhook',
				'post_status' => 'publish',
			),
			true
		);

		if ( is_wp_error( $postId ) ) {
			return $postId;
		}

		update_post_meta( $postId, '_webhook_event', sanitize_text_field( $event ) );
		update_post_meta( $postId, '_webhook_url', esc_url_raw( $url ) );
		update_post_meta( $postId, '_webhook_method', strtoupper( $method ) );
		update_post_meta( $postId, '_webhook_headers', wp_json_encode( $headers ) );

		return $postId;
	}

	public function update( int $id, string $name, string $event, string $url, string $method, array $headers ): bool|WP_Error {
		$post = get_post( $id );
		if ( ! $post || $post->post_type !== 'graphql_webhook' ) {
			return new WP_Error( 'invalid_webhook', __( 'Webhook not found.', 'wp-graphql-headless-webhooks' ) );
		}

		$validation = $this->validate_data( $event, $url, $method );
		if ( is_wp_error( $validation ) ) {
			return $validation;
		}

		$postData = array(
			'ID'         => $id,
			'post_title' => sanitize_text_field( $name ),
		);

		$updated = wp_update_post( $postData, true );
		if ( is_wp_error( $updated ) ) {
			return $updated;
		}

		update_post_meta( $id, '_webhook_event', sanitize_text_field( $event ) );
		update_post_meta( $id, '_webhook_url', esc_url_raw( $url ) );
		update_post_meta( $id, '_webhook_method', strtoupper( $method ) );
		update_post_meta( $id, '_webhook_headers', wp_json_encode( $headers ) );

		return true;
	}

	public function delete( int $id ): bool {
		$post = get_post( $id );
		if ( ! $post || $post->post_type !== 'graphql_webhook' ) {
			return false;
		}

		$deleted = wp_delete_post( $id, true );

		return (bool) $deleted;
	}

	public function validate_data( string $event, string $url, string $method ): bool|WP_Error {
		if ( ! isset( $this->get_allowed_events()[ $event ] ) ) {
			return new WP_Error( 'invalid_event', 'Invalid event type.' );
		}
		if ( ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
			return new WP_Error( 'invalid_url', 'Invalid URL.' );
		}
		$allowed_methods = array_keys( $this->get_allowed_methods() );
		if ( ! in_array( strtoupper( $method ), $allowed_methods, true ) ) {
			return new WP_Error( 'invalid_method', 'Invalid HTTP method.' );
		}
		return apply_filters( 'graphql_webhooks_validate_data', true, $event, $url, $method );
	}

	/**
	 * Maps a WP_Post to a Webhook entity.
	 *
	 * @param WP_Post $post The webhook post object.
	 *
	 * @return Webhook The mapped Webhook entity.
	 */
	private function mapPostToEntity( WP_Post $post ): Webhook {
		$event   = get_post_meta( $post->ID, '_webhook_event', true );
		$url     = get_post_meta( $post->ID, '_webhook_url', true );
		$method  = get_post_meta( $post->ID, '_webhook_method', true ) ?: 'POST';
		$headers = get_post_meta( $post->ID, '_webhook_headers', true );
		$headers = $headers ? json_decode( $headers, true ) : array();

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
