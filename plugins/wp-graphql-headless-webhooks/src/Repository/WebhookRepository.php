<?php
namespace WPGraphQL\Webhooks;

use WP_Error;

/**
 * Class WebhookRepository
 *
 * Handles CRUD operations for webhook CPT posts.
 */
class WebhookRepository {

    /**
     * Creates a new webhook CPT post.
     *
     * @param string               $type   Webhook type identifier.
     * @param string               $name   Webhook post title.
     * @param array<string,mixed>  $config Optional webhook configuration.
     *
     * @return int|WP_Error Post ID on success, WP_Error on failure.
     */
    public function createWebhook(string $type, string $name, array $config = []) {
        $postId = wp_insert_post([
            'post_title'  => $name,
            'post_type'   => 'graphql_webhook',
            'post_status' => 'publish',
        ], true);

        if (is_wp_error($postId)) {
            return $postId;
        }

        update_post_meta($postId, '_webhook_type', $type);

        if (!empty($config)) {
            update_post_meta($postId, '_webhook_config', wp_json_encode($config));
        }

        return $postId;
    }

    /**
     * Retrieves a webhook post by ID.
     *
     * @param int $postId Post ID.
     *
     * @return \WP_Post|null The webhook post object or null if not found.
     */
    public function getWebhook(int $postId): ?\WP_Post {
        $post = get_post($postId);
        if ($post && $post->post_type === 'graphql_webhook') {
            return $post;
        }
        return null;
    }

    /**
     * Updates an existing webhook post.
     *
     * @param int                  $postId Webhook post ID.
     * @param array<string,mixed>  $fields Associative array of fields to update:
     *                                    'title', 'content', 'status', 'config', 'type'.
     *
     * @return int|WP_Error Updated post ID on success, WP_Error on failure.
     */
    public function updateWebhook(int $postId, array $fields) {
        $post = get_post($postId);
        if (!$post || $post->post_type !== 'graphql_webhook') {
            return new WP_Error('invalid_webhook', __('Webhook not found.', 'wp-graphql-headless-webhooks'));
        }

        $postData = ['ID' => $postId];

        if (isset($fields['title'])) {
            $postData['post_title'] = sanitize_text_field($fields['title']);
        }
        if (isset($fields['content'])) {
            $postData['post_content'] = sanitize_textarea_field($fields['content']);
        }
        if (isset($fields['status'])) {
            $postData['post_status'] = sanitize_text_field($fields['status']);
        }

        $updatedPostId = wp_update_post($postData, true);
        if (is_wp_error($updatedPostId)) {
            return $updatedPostId;
        }

        if (isset($fields['type'])) {
            update_post_meta($postId, '_webhook_type', sanitize_text_field($fields['type']));
        }

        if (isset($fields['config'])) {
            update_post_meta($postId, '_webhook_config', wp_json_encode($fields['config']));
        }

        return $updatedPostId;
    }

    /**
     * Deletes a webhook post permanently.
     *
     * @param int $postId Post ID to delete.
     *
     * @return bool|WP_Error True on success, WP_Error on failure.
     */
    public function deleteWebhook(int $postId) {
        $post = get_post($postId);
        if (!$post || $post->post_type !== 'graphql_webhook') {
            return new WP_Error('invalid_webhook', __('Webhook not found.', 'wp-graphql-headless-webhooks'));
        }

        $deleted = wp_delete_post($postId, true);
        if (!$deleted) {
            return new WP_Error('delete_failed', __('Failed to delete webhook.', 'wp-graphql-headless-webhooks'));
        }

        return true;
    }
}