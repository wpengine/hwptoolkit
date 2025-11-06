<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Admin;

use WPGraphQL\Logging\Admin\View\Download\DownloadLogService;
use WPGraphQL\Logging\Admin\View\List\ListTable;
use WPGraphQL\Logging\Logger\Api\LogServiceInterface;
use WPGraphQL\Logging\Logger\Store\LogStoreService;

/**
 * The view logs page class for WPGraphQL Logging.
 *
 * @package WPGraphQL\Logging
 *
 * @since 0.0.1
 */
class ViewLogsPage {
	/**
	 * The admin page slug.
	 *
	 * @var string
	 */
	public const ADMIN_PAGE_SLUG = 'wpgraphql-logging-view';

	/**
	 * The nonce for the view page.
	 *
	 * @var string
	 */
	public const ADMIN_PAGE_VIEW_NONCE = 'wp_graphql_logging_admin';

	/**
	 * The nonce for the download page.
	 *
	 * @var string
	 */
	public const ADMIN_PAGE_DOWNLOAD_NONCE = 'wp_graphql_logging_download';

	/**
	 * The hook suffix for the admin page.
	 *
	 * @var string
	 */
	protected string $page_hook = '';

	/**
	 * The instance of the view logs page.
	 *
	 * @var self|null
	 */
	protected static ?self $instance = null;

	/**
	 * Initializes the view logs page.
	 */
	public static function init(): ?self {
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

		// Add top-level menu page.
		$this->page_hook = add_menu_page(
			esc_html__( 'GraphQL Logs', 'wpgraphql-logging' ),
			esc_html__( 'GraphQL Logs', 'wpgraphql-logging' ),
			'manage_options',
			self::ADMIN_PAGE_SLUG,
			[ $this, 'render_admin_page' ],
			'dashicons-chart-line',
		);

		// Add "View All Logs" as the first submenu item (replaces the duplicate top-level menu item).
		add_submenu_page(
			self::ADMIN_PAGE_SLUG,
			esc_html__( 'All Logs', 'wpgraphql-logging' ),
			esc_html__( 'All Logs', 'wpgraphql-logging' ),
			'manage_options',
			self::ADMIN_PAGE_SLUG,
			[ $this, 'render_admin_page' ]
		);

		// Updates the list table when filters are applied.
		add_action( 'load-' . $this->page_hook, [ $this, 'process_page_actions_before_rendering' ], 10, 0 );

		// Enqueue scripts for the admin page.
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_scripts_styles' ] );
	}

	/**
	 * Enqueues scripts and styles for the admin page.
	 *
	 * @param string $hook_suffix The current admin page.
	 */
	public function enqueue_admin_scripts_styles( string $hook_suffix ): void {
		if ( $hook_suffix !== $this->page_hook ) {
			return;
		}

		if ( file_exists( trailingslashit( WPGRAPHQL_LOGGING_PLUGIN_DIR ) . 'assets/js/view/jquery-ui-timepicker-addon.js' ) ) {
			wp_enqueue_script( 'jquery-ui-datepicker' );
			wp_enqueue_script( 'jquery-ui-slider' );
			wp_enqueue_script(
				'wpgraphql-logging-jquery-ui-timepicker-addon-js',
				trailingslashit( WPGRAPHQL_LOGGING_PLUGIN_URL ) . 'assets/js/view/jquery-ui-timepicker-addon.js',
				[ 'jquery-ui-datepicker', 'jquery-ui-slider' ],
				WPGRAPHQL_LOGGING_VERSION,
				true,
			);
		}

		if ( file_exists( trailingslashit( WPGRAPHQL_LOGGING_PLUGIN_DIR ) . 'assets/js/view/wp-graphql-logging-view.js' ) ) {
			wp_enqueue_script(
				'wpgraphql-logging-view-js',
				trailingslashit( WPGRAPHQL_LOGGING_PLUGIN_URL ) . 'assets/js/view/wp-graphql-logging-view.js',
				[ 'jquery' ],
				WPGRAPHQL_LOGGING_VERSION,
				true
			);
		}


		if ( file_exists( trailingslashit( WPGRAPHQL_LOGGING_PLUGIN_DIR ) . 'assets/css/view/jquery-ui-timepicker-addon.min.css' ) ) {
			wp_enqueue_style(
				'wpgraphql-logging-jquery-ui-timepicker-addon-css',
				trailingslashit( WPGRAPHQL_LOGGING_PLUGIN_URL ) . 'assets/css/view/jquery-ui-timepicker-addon.min.css',
				[],
				WPGRAPHQL_LOGGING_VERSION
			);
		}


		if ( file_exists( trailingslashit( WPGRAPHQL_LOGGING_PLUGIN_DIR ) . 'assets/css/view/jquery-ui.css' ) ) {
			wp_enqueue_style(
				'wpgraphql-logging-jquery-ui-css',
				trailingslashit( WPGRAPHQL_LOGGING_PLUGIN_URL ) . 'assets/css/view/jquery-ui.css',
				[],
				WPGRAPHQL_LOGGING_VERSION
			);
		}


		// Allow other plugins to enqueue their own scripts/styles.
		do_action( 'wpgraphql_logging_view_logs_admin_enqueue_scripts', $hook_suffix );
	}

	/**
	 * Renders the admin page for the logs.
	 *
	 * @phpcs:disable WordPress.Security.NonceVerification.Recommended
	 */
	public function render_admin_page(): void {

		$action = isset( $_REQUEST['action'] ) && is_string( $_REQUEST['action'] )
			? sanitize_text_field( $_REQUEST['action'] )
			: 'list';

		switch ( $action ) {
			case 'view':
				$this->render_view_page();
				break;
			case 'download':
				// Handled in process_page_actions_before_rendering.
				break;
			default:
				$this->render_list_page();
				break;
		}
	}

	/**
	 * Processes actions for the page, such as filtering and downloading logs.
	 * This runs before any HTML is output.
	 */
	public function process_page_actions_before_rendering(): void {

		// Nonce handled in process_log_download and process_filters_redirect.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET['action'] ) && 'download' === $_GET['action'] ) {
			$this->process_log_download();
		}

		$this->process_filters_redirect();
	}

	/**
	 * Process filter form submission and redirect to a GET request.
	 * This runs before any HTML is output.
	 */
	public function process_filters_redirect(): void {
		// Handle POST from filter form and redirect to GET.
		$nonce = $this->get_post_value( 'wpgraphql_logging_nonce' );
		if ( ! is_string( $nonce ) ) {
			return;
		}

		// Verify nonce for security.
		if ( false === wp_verify_nonce( $nonce, 'wpgraphql_logging_filter' ) ) {
			return;
		}

		$redirect_url = $this->get_redirect_url();

		wp_safe_redirect( $redirect_url );
		exit;
	}

	/**
	 * Constructs the redirect URL with filter parameters.
	 *
	 * @return string The constructed redirect URL.
	 */
	public function get_redirect_url(): string {
		$redirect_url = menu_page_url( self::ADMIN_PAGE_SLUG, false );

		$possible_filters = [
			'start_date',
			'end_date',
			'level_filter',
			'orderby',
			'order',
		];
		$filters          = [];
		foreach ( $possible_filters as $key ) {
			$value = $this->get_post_value( $key );
			if ( null !== $value ) {
				$filters[ $key ] = $value;
			}
		}

		$redirect_url = add_query_arg( array_filter( $filters, static function ( $value ) {
			return '' !== $value;
		} ), $redirect_url );

		return (string) apply_filters( 'wpgraphql_logging_filter_redirect_url', $redirect_url, $filters );
	}

	/**
	 * Retrieves and sanitizes a value from the $_POST superglobal.
	 *
	 * @param string $key The key to retrieve from $_POST.
	 *
	 * @phpcs:disable WordPress.Security.NonceVerification.Missing
	 *
	 * @return string|null The sanitized value or null if not set or invalid.
	 */
	protected function get_post_value(string $key): ?string {
		if ( ! array_key_exists( $key, $_POST ) || ! is_string( $_POST[ $key ] ) ) {
			return null;
		}
		$post_value = sanitize_text_field( wp_unslash( $_POST[ $key ] ) );
		return '' !== $post_value ? $post_value : null;
	}

	/**
	 * Renders the list page for log entries.
	 */
	protected function render_list_page(): void {
		$list_table    = new ListTable( $this->get_log_service() ); // @phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable.UnusedVariable
		$list_template = __DIR__ . '/View/Templates/WPGraphQLLoggerList.php';
		require_once $list_template; // @phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingVariable
	}

	/**
	 * Renders the list page for log entries.
	 */
	protected function process_log_download(): void {
		$log_id = isset( $_GET['log'] ) ? absint( $_GET['log'] ) : 0;
		$this->verify_admin_page_nonce( self::ADMIN_PAGE_DOWNLOAD_NONCE . '_' . $log_id );
		if ( 0 === (int) $log_id ) {
			wp_die( esc_html__( 'Invalid log ID.', 'wpgraphql-logging' ) );
		}
		$downloader = new DownloadLogService( $this->get_log_service() );
		$downloader->generate_csv( $log_id );
	}

	/**
	 * Renders the view page for a single log entry.
	 */
	protected function render_view_page(): void {
		$log_id = isset( $_GET['log'] ) ? absint( $_GET['log'] ) : 0;
		$this->verify_admin_page_nonce( self::ADMIN_PAGE_VIEW_NONCE . '_' . $log_id );

		if ( 0 === (int) $log_id ) {
			echo '<div class="notice notice-error"><p>' . esc_html__( 'Invalid log ID.', 'wpgraphql-logging' ) . '</p></div>';
			return;
		}

		$log_service = $this->get_log_service();
		$log         = $log_service->find_entity_by_id( $log_id );

		if ( is_null( $log ) ) {
			echo '<div class="notice notice-error"><p>' . esc_html__( 'Log not found.', 'wpgraphql-logging' ) . '</p></div>';
			return;
		}

		$log_template = __DIR__ . '/View/Templates/WPGraphQLLoggerView.php';

		require_once $log_template; // @phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingVariable
	}

	/**
	 * Verifies the admin page nonce.
	 *
	 * @param string $nonce The nonce to verify.
	 */
	protected function verify_admin_page_nonce(string $nonce): void {
		if ( ! current_user_can( 'manage_options' ) || ! is_admin() ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'wpgraphql-logging' ) );
		}
		check_admin_referer( $nonce );
	}

	/**
	 * Retrieves the log service instance.
	 *
	 * @return \WPGraphQL\Logging\Logger\Api\LogServiceInterface The log service instance.
	 */
	protected function get_log_service(): LogServiceInterface {
		return LogStoreService::get_log_service();
	}
}
