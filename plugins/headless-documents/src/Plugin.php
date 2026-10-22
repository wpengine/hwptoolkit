<?php

declare(strict_types=1);

namespace Headless\Documents;

use Headless\Documents\Rest\Documents_Controller;
use Headless\Documents\Store\Document_Table;

/**
 * Plugin class for Headless Documents.
 *
 * Registers the plugin's action and filter hooks.
 *
 * @package Headless\Documents
 */
final class Plugin {
	/**
	 * The instance of the plugin.
	 *
	 * @var \Headless\Documents\Plugin|null
	 */
	protected static ?Plugin $instance = null;

	/**
	 * Private constructor to prevent direct instantiation.
	 */
	protected function __construct() {
	}

	/**
	 * Initialize the plugin.
	 */
	public static function init(): self {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
			self::$instance->setup();
		}

		/**
		 * Fires after the plugin is initialized.
		 *
		 * @param \Headless\Documents\Plugin $instance The instance of the plugin class.
		 */
		do_action( 'headless_documents_init', self::$instance );

		return self::$instance;
	}

	/**
	 * Register the plugin's hooks.
	 */
	public function setup(): void {
		add_action( 'init', [ Document_Table::class, 'maybe_install' ], 10, 0 );
		add_filter( 'wpmu_drop_tables', [ Document_Table::class, 'drop_with_site' ], 10, 1 );
		add_filter( 'user_has_cap', [ Capabilities::class, 'grant_to_admins' ], 10, 1 );
		add_action( 'rest_api_init', [ Documents_Controller::class, 'register_routes' ], 10, 0 );
	}

	/**
	 * Throw error on object clone.
	 *
	 * @codeCoverageIgnore
	 */
	public function __clone() {
		// Cloning instances of the class is forbidden.
		_doing_it_wrong( __METHOD__, 'The plugin Plugin class should not be cloned.', '0.1.0' );
	}

	/**
	 * Disable unserializing of the class.
	 *
	 * @codeCoverageIgnore
	 */
	public function __wakeup(): void {
		// De-serializing instances of the class is forbidden.
		_doing_it_wrong( __METHOD__, 'De-serializing instances of the plugin Main class is not allowed.', '0.1.0' );
	}
}
