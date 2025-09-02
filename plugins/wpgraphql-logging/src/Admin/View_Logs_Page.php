<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Admin;

use WPGraphQL\Logging\Admin\View\Grid_Service;

class View_Logs_Page {

	public const ADMIN_PAGE_SLUG = 'wpgraphql-logging';

	protected static ?View_Logs_Page $instance = null;

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

	public function setup(): void {
		add_action( 'admin_menu', [ $this, 'register_settings_page' ], 10, 0);
	}

	public function register_settings_page(): void {

		// Add submenu under GraphQL menu using the correct parent slug
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

	public function render_admin_page(): void {
		// For now, just hello world
		echo '<h1>Hello World</h1>';

		// Here is where we will render the grid
		$grid_service = new Grid_Service();
		$grid_service->render();
	}
}
