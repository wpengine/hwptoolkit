<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Hooks;

use WPGraphQL\Logging\Logger\Database\DatabaseEntity;

/**
 * Hooks for the WPGraphQL Logging plugin.
 *
 * @package WPGraphQL\Logging
 *
 * @since 0.0.1
 */
class PluginHooks {
	/**
	 * The single instance of the class.
	 *
	 * @var \WPGraphQL\Logging\Hooks\PluginHooks|null
	 */
	private static ?PluginHooks $instance = null;

	/**
	 * Get or create the single instance of the class.
	 */
	public static function init(): PluginHooks {
		if ( null === self::$instance ) {
			self::$instance = new self();
			self::$instance->register();
		}
		return self::$instance;
	}

	/**
	 * Activation callback for the plugin.
	 */
	public static function activate_plugin(): void {
		DatabaseEntity::create_table();
	}

	/**
	 * Deactivation callback for the plugin.
	 */
	public static function deactivate_plugin(): void {
		// @TODO: Add configuration to determine if the table should be dropped on deactivation.
		DatabaseEntity::drop_table();
	}

	/**
	 * Register actions and filters.
	 */
	protected function register(): void {
		add_action( 'wpgraphql_logging_activate', [ self::class, 'activate_plugin' ], 10, 0 );
		add_action( 'wpgraphql_logging_deactivate', [ self::class, 'deactivate_plugin' ], 10, 0 );
	}
}
