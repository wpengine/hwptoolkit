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
     * @param array $data The webhook data containing:
     *                    - name (string): The name (title) of the webhook.
     *                    - event (string): The event identifier the webhook listens to.
     *                    - url (string): The target URL the webhook will send data to.
     *                    - method (string): The HTTP method to use when sending the webhook (e.g., 'POST').
     *                    - headers (array): Optional associative array of headers to send with the request.
     *
     * @return int|\WP_Error The new webhook's post ID on success, or WP_Error on failure.
     */
    public function create(array $data);

    /**
     * Updates an existing webhook.
     *
     * @param int   $id   The webhook post ID.
     * @param array $data The webhook data containing:
     *                    - name (string): The updated name (title) of the webhook.
     *                    - event (string): The updated event identifier.
     *                    - url (string): The updated target URL.
     *                    - method (string): The updated HTTP method.
     *                    - headers (array): The updated array of headers.
     *
     * @return bool|\WP_Error True on success, or WP_Error on failure.
     */
    public function update(int $id, array $data);

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