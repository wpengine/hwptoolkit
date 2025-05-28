<?php
namespace WPGraphQL\Webhooks\Repository\Interfaces;

use WPGraphQL\Webhooks\Entity\Webhook;

/**
 * Webhook Repository Interface
 *
 * Defines the contract for interacting with webhook data storage.
 */
interface WebhookRepositoryInterface {

    /**
     * Retrieves all webhooks.
     *
     * @return Webhook[] Array of Webhook entities.
     */
    public function get_all(): array;

    /**
     * Retrieves a single webhook by its ID.
     *
     * @param int $id The webhook post ID.
     *
     * @return Webhook|null The Webhook entity, or null if not found.
     */
    public function get(int $id): ?Webhook;

    /**
     * Creates a new webhook.
     *
     * @param string $name    The name (title) of the webhook.
     * @param string $event   The event identifier the webhook listens to.
     * @param string $url     The target URL the webhook will send data to.
     * @param string $method  The HTTP method to use when sending the webhook (e.g., 'POST').
     * @param array  $headers Optional associative array of headers to send with the request.
     *
     * @return int|\WP_Error The new webhook's post ID on success, or WP_Error on failure.
     */
    public function create(string $name, string $event, string $url, string $method, array $headers);

    /**
     * Updates an existing webhook.
     *
     * @param int    $id      The webhook post ID.
     * @param string $name    The updated name (title) of the webhook.
     * @param string $event   The updated event identifier.
     * @param string $url     The updated target URL.
     * @param string $method  The updated HTTP method.
     * @param array  $headers The updated array of headers.
     *
     * @return bool|\WP_Error True on success, or WP_Error on failure.
     */
    public function update(int $id, string $name, string $event, string $url, string $method, array $headers);

    /**
     * Deletes a webhook by its ID.
     *
     * @param int $id The webhook post ID.
     *
     * @return bool True on successful deletion, false otherwise.
     */
    public function delete(int $id): bool;

    /**
     * Retrieves the list of allowed webhook events.
     *
     * @return string[] Array of allowed event identifiers.
     */
    public function get_allowed_events(): array;

    /**
     * Validates webhook data before creation or update.
     *
     * @param string $event  The event identifier.
     * @param string $url    The target URL.
     * @param string $method The HTTP method.
     *
     * @return bool|\WP_Error True if data is valid, or WP_Error with a descriptive message.
     */
    public function validate_data(string $event, string $url, string $method);
}