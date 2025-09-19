<?php
/**
 * GraphQL Mutation: updateWebhook
 *
 * @package WPGraphQL\Webhooks\Mutation
 */

namespace WPGraphQL\Webhooks\Mutation;

use GraphQL\Error\UserError;

/**
 * Class UpdateWebhook
 *
 * Registers the updateWebhook GraphQL mutation.
 */
class UpdateWebhook {

    /**
     * Registers the updateWebhook mutation with the GraphQL schema.
     *
     * @return void
     */
    public static function register(): void {
        register_graphql_mutation('updateWebhook', [
            'inputFields' => [
                'id' => [
                    'type' => 'ID',
                    'description' => __('The ID of the webhook to update', 'graphql-webhooks'),
                ],
                'title' => [
                    'type' => 'String',
                    'description' => __('The new title of the webhook', 'graphql-webhooks'),
                ],
                'content' => [
                    'type' => 'String',
                    'description' => __('The new content/description of the webhook', 'graphql-webhooks'),
                ],
                'eventTrigger' => [
                    'type' => 'String',
                    'description' => __('The new event hook name that triggers this webhook', 'graphql-webhooks'),
                ],
                'enabled' => [
                    'type' => 'Boolean',
                    'description' => __('Whether the webhook is enabled', 'graphql-webhooks'),
                ],
                'security' => [
                    'type' => 'String',
                    'description' => __('Security information for the webhook', 'graphql-webhooks'),
                ],
                'handlerClass' => [
                    'type' => 'String',
                    'description' => __('The handler class used for dispatching', 'graphql-webhooks'),
                ],
                'handlerConfig' => [
                    'type' => 'String',
                    'description' => __('Configuration for the handler, JSON encoded', 'graphql-webhooks'),
                ],
                'status' => [
                    'type' => 'String',
                    'description' => __('Post status, e.g. PUBLISH or DRAFT', 'graphql-webhooks'),
                ],
            ],
            'outputFields' => [
                'webhook' => [
                    'type' => 'Webhook',
                    'description' => __('The updated webhook', 'graphql-webhooks'),
                    'resolve' => function ($payload) {
                        return get_post($payload['webhookId']);
                    },
                ],
            ],
            'mutateAndGetPayload' => function ($input, $context, $info) {
                if (!current_user_can('manage_options')) {
                    throw new UserError(__('You do not have permission to update webhooks.', 'graphql-webhooks'));
                }

                if (empty($input['id'])) {
                    throw new UserError(__('The ID of the webhook to update is required.', 'graphql-webhooks'));
                }

                $post_id = is_numeric($input['id']) ? (int) $input['id'] : 0;

                if ($post_id <= 0) {
                    throw new UserError(__('Invalid webhook ID.', 'graphql-webhooks'));
                }

                $post = get_post($post_id);
                if (!$post || $post->post_type !== 'graphql_webhook') {
                    throw new UserError(__('Webhook not found.', 'graphql-webhooks'));
                }
                $post_data = ['ID' => $post_id];

                if (isset($input['title'])) {
                    $post_data['post_title'] = sanitize_text_field($input['title']);
                }
                if (isset($input['content'])) {
                    $post_data['post_content'] = sanitize_textarea_field($input['content']);
                }
                if (isset($input['status'])) {
                    $post_data['post_status'] = sanitize_text_field($input['status']);
                }
                $updated_post_id = wp_update_post($post_data, true);
                if (is_wp_error($updated_post_id)) {
                    throw new UserError(__('Failed to update webhook.', 'graphql-webhooks'));
                }

                if (isset($input['eventTrigger'])) {
                    update_post_meta($post_id, '_event_trigger', sanitize_text_field($input['eventTrigger']));
                }
                if (isset($input['enabled'])) {
                    update_post_meta($post_id, '_enabled', (bool) $input['enabled']);
                }
                if (isset($input['security'])) {
                    update_post_meta($post_id, '_security', sanitize_text_field($input['security']));
                }
                if (isset($input['handlerClass'])) {
                    update_post_meta($post_id, '_handler_class', sanitize_text_field($input['handlerClass']));
                }
                if (isset($input['handlerConfig'])) {
                    update_post_meta($post_id, '_handler_config', sanitize_text_field($input['handlerConfig']));
                }

                return ['webhookId' => $post_id];
            },
        ]);
    }
}