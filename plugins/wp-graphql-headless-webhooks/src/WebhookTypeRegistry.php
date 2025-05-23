<?php
/**
 * Webhook Registry class
 *
 * @package WPGraphQL\Webhooks
 */

namespace WPGraphQL\Webhooks;

use \WPGraphQL\Webhooks\DTO\WebhookDTO;
use WPGraphQL\Webhooks\Events\Interfaces\EventRegistry;

/**
 * Class WebhookTypeRegistry
 */
class WebhookTypeRegistry {

	/**
	 * Registered webhook types
	 *
	 * @var array<string, array<string, mixed>> Array of webhook types keyed by type identifier.
	 */
	private array $webhook_types = [];

	private ?EventRegistry $eventRegistry = null;

	/**
	 * Instance of the registry
	 *
	 * @var WebhookTypeRegistry|null
	 */
	private static ?WebhookTypeRegistry $instance = null;

	public function __construct( EventRegistry $eventRegistry ) {
		$this->eventRegistry = $eventRegistry;
	}

	public function set_event_registry( EventRegistry $eventRegistry ): void {
		$this->eventRegistry = $eventRegistry;
		$this->eventRegistry->init();
	}

	/**
	 * Registers a webhook type and its events.
	 *
	 * @param WebhookDTO $webhookType
	 * 
	 * @return bool True if registered successfully, false if already exists.
	 */
	public function register_webhook_type( WebhookDTO $webhookType ): bool {
		if ( isset( $this->webhook_types[ $webhookType->type ] ) ) {
			return false;
		}

		$this->webhook_types[ $webhookType->type ] = $webhookType;

		// Register events with the event registry
		foreach ( $webhookType->events as $event ) {
			$this->eventRegistry->register_event( $event );
		}

		return true;
	}

	/**
	 * Get all registered webhook types
	 *
	 * @return array<string, array<string, mixed>>
	 */
	public function get_webhook_types(): array {
		return $this->webhook_types;
	}

	/**
	 * Get a specific webhook type
	 *
	 * @param string $type The webhook type.
	 * @return array<string, mixed>|null
	 */
	public function get_webhook_type( string $type ): ?array {
		return $this->webhook_types[ $type ] ?? null;
	}
}
