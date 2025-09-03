<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Admin;

use WPGraphQL\Logging\Admin\View\List\List_Table;
use WPGraphQL\Logging\Logger\Database\LogsRepository;

/**
 * The view logs page class for WPGraphQL Logging.
 *
 * @package WPGraphQL\Logging
 *
 * @since 0.0.1
 */
class View_Logs_Page {
	public const ADMIN_PAGE_SLUG = 'wpgraphql-logging-view';

	/**
	 * The instance of the view logs page.
	 */
	protected static ?View_Logs_Page $instance = null;

	/**
	 * Initializes the view logs page.
	 */
	public static function init(): ?View_Logs_Page {
		if ( ! current_user_can( 'manage_options' ) ) {
			return null;
		}

		if ( ! isset( self::$instance ) || ! ( is_a( self::$instance, self::class ) ) ) {
			self::$instance = new self();
			self::$instance->setup();
		}

		do_action( 'wpgraphql_logging_view_logs_init', self::$instance );

		return self::$instance;
	}

	/**
	 * Sets up the view logs page.
	 */
	public function setup(): void {
		add_action( 'admin_menu', [ $this, 'register_settings_page' ], 10, 0 );
	}

	/**
	 * Registers the settings page for the view logs.
	 */
	public function register_settings_page(): void {

		// Add submenu under GraphQL menu using the correct parent slug.
		add_menu_page(
			esc_html__( 'GraphQL Logs', 'wpgraphql-logging' ),
			esc_html__( 'GraphQL Logs', 'wpgraphql-logging' ),
			'manage_options',
			self::ADMIN_PAGE_SLUG,
			[ $this, 'render_admin_page' ],
			'dashicons-list-view',
			25
		);
		add_submenu_page(
			'graphiql-ide',
			esc_html__( 'GraphQL Logs', 'wpgraphql-logging' ),
			esc_html__( 'GraphQL Logs', 'wpgraphql-logging' ),
			'manage_options',
			self::ADMIN_PAGE_SLUG,
			[ $this, 'render_admin_page' ]
		);
	}

	/**
	 * Renders the admin page for the logs.
	 */
	public function render_admin_page(): void {
		$action = isset( $_GET['action'] ) ? sanitize_text_field( $_GET['action'] ) : 'list'; // @phpcs:ignore WordPress.Security.NonceVerification.Recommended
		switch ( $action ) {
			case 'view':
				$this->render_view_page();
				break;
			default:
				$this->render_list_page();
				break;
		}
	}

	/**
	 * Renders the list page for log entries.
	 */
	protected function render_list_page(): void {
		// Variable required for list template.
		$list_table    = new List_Table( new LogsRepository() ); // @phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable.UnusedVariable
		$list_template = apply_filters(
			'wpgraphql_logging_list_template',
			__DIR__ . '/View/List/Templates/wpgraphql-logger-list.php'
		);
		require_once $list_template; // @phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingVariable
	}

	/**
	 * Renders the view page for a single log entry.
	 */
	protected function render_view_page(): void {
		// Render the view page.
	}
}
