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
	public function get( int $id ): ?Webhook;

	/**
	 * Creates a new webhook.
	 *
	 * @param Webhook $webhook The webhook entity to create.
	 *
	 * @return int|\WP_Error The new webhook's post ID on success, or WP_Error on failure.
	 */
	public function create( Webhook $webhook );

	/**
	 * Updates an existing webhook.
	 *
	 * @param int     $id      The webhook post ID.
	 * @param Webhook $webhook The webhook entity with updated data.
	 *
	 * @return bool|\WP_Error True on success, or WP_Error on failure.
	 */
	public function update( int $id, Webhook $webhook );

	/**
	 * Deletes a webhook by its ID.
	 *
	 * @param int $id The webhook post ID.
	 *
	 * @return bool True on successful deletion, false otherwise.
	 */
	public function delete( int $id ): bool;

	/**
	 * Retrieves the list of allowed webhook events.
	 *
	 * @return string[] Array of allowed event identifiers.
	 */
	public function get_allowed_events(): array;

	/**
	 * Validates the webhook data.
	 *
	 * @param Webhook $webhook The webhook entity to validate.
	 * @return true|\WP_Error True if valid, or WP_Error on failure.
	 */
	public function validate( Webhook $webhook );
}