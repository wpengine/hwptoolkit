<?php
namespace WPGraphQL\Webhooks\Repository;

use WPGraphQL\Webhooks\Entity\Webhook;
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
        // Smart Cache events only by default
        'smart_cache_created' => 'Smart Cache - Content Created',
        'smart_cache_updated' => 'Smart Cache - Content Updated',
        'smart_cache_deleted' => 'Smart Cache - Content Deleted',
        'smart_cache_nodes_purged' => 'Smart Cache - Nodes Purged',
    ];

    /**
     * Get the list of allowed webhook events.
     *
     * @return array<string, string> Associative array of event keys and labels.
     */
    public function get_allowed_events(): array {
        return apply_filters('graphql_webhooks_allowed_events', $this->default_events);
    }

    /**
     * Get the list of allowed HTTP methods.
     *
     * @return array<string> Array of allowed HTTP methods.
     */
    public function get_allowed_methods(): array {
        $default_methods = ['POST', 'GET'];
        return apply_filters('graphql_webhooks_allowed_methods', $default_methods);
    }

    /**
     * Retrieve all published webhook entities.
     *
     * @return Webhook[] Array of Webhook entity objects.
     */
    public function get_all(): array {
        $webhooks = [];

        $posts = get_posts([
            'post_type'   => 'graphql_webhook',
            'post_status' => 'publish',
            'numberposts' => -1,
        ]);

        foreach ($posts as $post) {
            $webhooks[] = $this->mapPostToEntity($post);
        }

        return $webhooks;
    }

    /**
     * Retrieve a webhook entity by its post ID.
     *
     * @param int $id The post ID of the webhook.
     * @return Webhook|null The Webhook entity or null if not found or invalid post type.
     */
    public function get($id): ?Webhook {
        $post = get_post($id);
        if (!$post || $post->post_type !== 'graphql_webhook') {
            return null;
        }
        return $this->mapPostToEntity($post);
    }

    /**
     * Create a new webhook entity.
     *
     * @param string $name    Name/title of the webhook.
     * @param string $event   Event key the webhook listens to.
     * @param string $url     Target URL for the webhook request.
     * @param string $method  HTTP method (GET or POST).
     * @param array  $headers Associative array of HTTP headers.
     *
     * @return int|WP_Error Post ID on success, or WP_Error on failure.
     */
    public function create($name, $event, $url, $method, $headers) {
        $validation = $this->validate_data($event, $url, $method);
        if (is_wp_error($validation)) {
            return $validation;
        }

        $postId = wp_insert_post([
            'post_title'  => $name,
            'post_type'   => 'graphql_webhook',
            'post_status' => 'publish',
        ], true);

        if (is_wp_error($postId)) {
            return $postId;
        }

        update_post_meta($postId, '_webhook_event', sanitize_text_field($event));
        update_post_meta($postId, '_webhook_url', esc_url_raw($url));
        update_post_meta($postId, '_webhook_method', strtoupper($method));
        update_post_meta($postId, '_webhook_headers', wp_json_encode($headers));

        return $postId;
    }

    /**
     * Update an existing webhook entity.
     *
     * @param int    $id      Post ID of the webhook to update.
     * @param string $name    New name/title of the webhook.
     * @param string $event   New event key.
     * @param string $url     New target URL.
     * @param string $method  New HTTP method.
     * @param array  $headers New HTTP headers.
     *
     * @return bool|WP_Error True on success, or WP_Error on failure.
     */
    public function update($id, $name, $event, $url, $method, $headers) {
        $post = get_post($id);
        if (!$post || $post->post_type !== 'graphql_webhook') {
            return new WP_Error('invalid_webhook', __('Webhook not found.', 'wp-graphql-headless-webhooks'));
        }

        $validation = $this->validate_data($event, $url, $method);
        if (is_wp_error($validation)) {
            return $validation;
        }

        $postData = [
            'ID'         => $id,
            'post_title' => sanitize_text_field($name),
        ];

        $updated = wp_update_post($postData, true);
        if (is_wp_error($updated)) {
            return $updated;
        }

        update_post_meta($id, '_webhook_event', sanitize_text_field($event));
        update_post_meta($id, '_webhook_url', esc_url_raw($url));
        update_post_meta($id, '_webhook_method', strtoupper($method));
        update_post_meta($id, '_webhook_headers', wp_json_encode($headers));

        return true;
    }

    /**
     * Delete a webhook entity by post ID.
     *
     * @param int $id Post ID of the webhook to delete.
     * @return bool True if deleted, false otherwise.
     */
    public function delete($id): bool {
        $post = get_post($id);
        if (!$post || $post->post_type !== 'graphql_webhook') {
            return false;
        }

        $deleted = wp_delete_post($id, true);

        return (bool) $deleted;
    }

    /**
     * Validate webhook data before creation or update.
     *
     * @param string $event  Event key to validate.
     * @param string $url    URL to validate.
     * @param string $method HTTP method to validate.
     *
     * @return bool|WP_Error True if valid, WP_Error if invalid.
     */
    public function validate_data($event, $url, $method) {
        if (!isset($this->get_allowed_events()[$event])) {
            return new WP_Error('invalid_event', 'Invalid event type.');
        }
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return new WP_Error('invalid_url', 'Invalid URL.');
        }
        $allowed_methods = $this->get_allowed_methods();
        if (!in_array(strtoupper($method), array_map('strtoupper', $allowed_methods), true)) {
            return new WP_Error('invalid_method', 'Invalid HTTP method.');
        }
        return apply_filters('graphql_webhooks_validate_data', true, $event, $url, $method);
    }

    /**
     * Map a WP_Post object to a Webhook entity.
     *
     * @param WP_Post $post The webhook post object.
     *
     * @return Webhook The mapped Webhook entity.
     */
    private function mapPostToEntity(WP_Post $post) {
        $event   = get_post_meta($post->ID, '_webhook_event', true);
        $url     = get_post_meta($post->ID, '_webhook_url', true);
        $method  = get_post_meta($post->ID, '_webhook_method', true) ?: 'POST';
        $headers = get_post_meta($post->ID, '_webhook_headers', true);
        $headers = $headers ? json_decode($headers, true) : [];

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
