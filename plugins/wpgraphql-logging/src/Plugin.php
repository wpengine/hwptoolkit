<?php

declare(strict_types=1);

namespace WPGraphQL\Logging;

use WPGraphQL\Logging\Admin\Settings_Page;
use WPGraphQL\Logging\Events\Event_Manager;
use WPGraphQL\Logging\Events\Query_Event_Lifecycle;
use WPGraphQL\Logging\Logger\Database\Database_Entity;

/**
 * Plugin class for WPGraphQL Logging.
 *
 * This class serves as the main entry point for the plugin, handling initialization, action and filter hooks.
 *
 * @package WPGraphQL\Logging
 */
final class Plugin {
	/**
	 * The instance of the plugin.
	 *
	 * @var \WPGraphQL\Logging\Plugin|null
	 */
	protected static ?Plugin $instance = null;

	/**
	 * Private constructor to prevent direct instantiation.
	 */
	protected function __construct() {
	}

	/**
	 * Constructor
	 */
	public static function init(): self {
		if ( ! isset( self::$instance ) || ! ( is_a( self::$instance, self::class ) ) ) {
			self::$instance = new self();
			self::$instance->setup();
		}

		/**
		 * Fire off init action.
		 *
		 * @param \WPGraphQL\Logging\Plugin $instance the instance of the plugin class.
		 */
		do_action( 'wpgraphql_logging_init', self::$instance );

		return self::$instance;
	}

	/**
	 * Subscribe to an event using the internal Event_Manager.
	 *
	 * @param string   $event_name Event name from \WPGraphQL\Logging\Events\Events.
	 * @param callable $listener  Listener callable with signature: function(array $payload): void {}.
	 * @param int      $priority  Lower runs earlier.
	 */
	public static function on( string $event_name, callable $listener, int $priority = 10 ): void {
		Event_Manager::subscribe( $event_name, $listener, $priority );
	}

	/**
	 * Publish an event to subscribers.
	 *
	 * @param string               $event_name Event name from \WPGraphQL\Logging\Events\Events.
	 * @param array<string, mixed> $payload   Arbitrary payload data.
	 */
	public static function emit( string $event_name, array $payload = [] ): void {
		Event_Manager::publish( $event_name, $payload );
	}

	/**
	 * Register a transform for an event payload. The transformer should return
	 * the (possibly) modified payload array.
	 *
	 * @param string   $event_name Event name from \WPGraphQL\Logging\Events\Events.
	 * @param callable $transform  function(array $payload): array {}.
	 * @param int      $priority  Lower runs earlier.
	 */
	public static function transform( string $event_name, callable $transform, int $priority = 10 ): void {
		Event_Manager::subscribe_to_transform( $event_name, $transform, $priority );
	}

	/**
	 * Activation callback for the plugin.
	 */
	public static function activate(): void {
		Database_Entity::create_table();
	}

	/**
	 * Deactivation callback for the plugin.
	 */
	public static function deactivate(): void {
		// @TODO: Add configuration to determine if the table should be dropped on deactivation.
		Database_Entity::drop_table();
	}

	/**
	 * Initialize the plugin admin, frontend & api functionality.
	 */
	protected function setup(): void {
		Settings_Page::init();
		Query_Event_Lifecycle::init();
	}

	/**
	 * Throw error on object clone.
	 * The whole idea of the singleton design pattern is that there is a single object
	 * therefore, we don't want the object to be cloned.
	 *
	 * @codeCoverageIgnore
	 *
	 * @return void
	 */
	public function __clone() {
		// Cloning instances of the class is forbidden.
		_doing_it_wrong( __METHOD__, 'The plugin Plugin class should not be cloned.', '0.0.1' );
	}

	/**
	 * Disable unserialize of the class.
	 *
	 * @codeCoverageIgnore
	 */
	public function __wakeup(): void {
		// De-serializing instances of the class is forbidden.
		_doing_it_wrong( __METHOD__, 'De-serializing instances of the plugin Main class is not allowed.', '0.0.1' );
	}
}
