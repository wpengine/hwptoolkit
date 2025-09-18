<?php

declare(strict_types=1);

namespace WPGraphQL\Logging;

use WPGraphQL\Logging\Admin\Settings_Page;
use WPGraphQL\Logging\Admin\View_Logs_Page;
use WPGraphQL\Logging\Events\EventManager;
use WPGraphQL\Logging\Events\QueryEventLifecycle;
use WPGraphQL\Logging\Logger\Database\DatabaseEntity;

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
	 * Initialize the plugin admin, frontend & api functionality.
	 */
	public function setup(): void {
		Settings_Page::init();
		View_Logs_Page::init();
		QueryEventLifecycle::init();
	}

	/**
	 * Subscribe to an event using the internal EventManager.
	 *
	 * @param string   $event_name Event name from \WPGraphQL\Logging\Events\Events.
	 * @param callable $listener  Listener callable with signature: function(array $payload): void {}.
	 * @param int      $priority  Lower runs earlier.
	 */
	public static function on( string $event_name, callable $listener, int $priority = 10 ): void {
		EventManager::subscribe( $event_name, $listener, $priority );
	}

	/**
	 * Publish an event to subscribers.
	 *
	 * @param string               $event_name Event name from \WPGraphQL\Logging\Events\Events.
	 * @param array<string, mixed> $payload   Arbitrary payload data.
	 */
	public static function emit( string $event_name, array $payload = [] ): void {
		EventManager::publish( $event_name, $payload );
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
		EventManager::subscribe_to_transform( $event_name, $transform, $priority );
	}

	/**
	 * Activation callback for the plugin.
	 */
	public static function activate(): void {
		DatabaseEntity::create_table();
	}

	/**
	 * Deactivation callback for the plugin.
	 *
	 * @since 0.0.1
	 */
	public static function deactivate(): void {
		if ( ! defined( 'WP_GRAPHQL_LOGGING_UNINSTALL_PLUGIN' ) ) {
			return;
		}
		DatabaseEntity::drop_table();
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
