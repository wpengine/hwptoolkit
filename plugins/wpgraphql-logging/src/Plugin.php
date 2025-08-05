<?php

declare(strict_types=1);

namespace WPGraphQL\Logging;

use WPGraphQL\Logging\Events\QueryEventLifecycle;
use WPGraphQL\Logging\Hooks\PluginHooks;
use WPGraphQL\Logging\MCP\McpAnalyzeLogsPrompt;
use WPGraphQL\Logging\MCP\McpLoggerResource;
use WPGraphQL\Logging\MCP\McpLoggerTool;

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
		PluginHooks::init();
		QueryEventLifecycle::init();

		// POC
		McpLoggerTool::init();
		McpLoggerResource::init();
		McpAnalyzeLogsPrompt::init();
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
	 * Disable unserializing of the class.
	 *
	 * @codeCoverageIgnore
	 */
	public function __wakeup(): void {
		// De-serializing instances of the class is forbidden.
		_doing_it_wrong( __METHOD__, 'De-serializing instances of the plugin Main class is not allowed.', '0.0.1' );
	}
}
