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
	public static function init(): ?ViewLogsPage {
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
	 *
	 * @psalm-suppress HookNotFound
	 */
	public function register_settings_page(): void {

		// Add submenu under GraphQL menu using the correct parent slug.
		$this->page_hook = add_menu_page(
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

		// Updates the list table when filters are applied.
		add_action( 'load-' . $this->page_hook, [ $this, 'process_page_actions_before_rendering' ], 10, 0 );

		// Enqueue scripts for the admin page.
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_scripts' ], 9, 1 ); // Need to load before adding SRI attributes.
		add_action( 'script_loader_tag', [ $this, 'add_sri_to_scripts' ], 10, 2 );
		add_action( 'style_loader_tag', [ $this, 'add_sri_to_styles' ], 10, 2 );
	}

	/**
	 * Enqueues scripts and styles for the admin page.
	 *
	 * @param string $hook_suffix The current admin page.
	 */
	public function enqueue_admin_scripts( string $hook_suffix ): void {
		if ( $hook_suffix !== $this->page_hook ) {
			return;
		}

		wp_enqueue_script( 'jquery-ui-datepicker' );
		wp_enqueue_script( 'jquery-ui-slider' );

		wp_enqueue_script(
			'jquery-ui-timepicker-addon',
			'https://cdnjs.cloudflare.com/ajax/libs/jquery-ui-timepicker-addon/1.6.3/jquery-ui-timepicker-addon.min.js',
			[ 'jquery-ui-datepicker', 'jquery-ui-slider' ],
			'1.6.3',
			true,
		);

		wp_enqueue_style(
			'jquery-ui-timepicker-addon-style',
			'https://cdnjs.cloudflare.com/ajax/libs/jquery-ui-timepicker-addon/1.6.3/jquery-ui-timepicker-addon.min.css',
			[],
			'1.6.3'
		);

		wp_enqueue_style( 'jquery-ui-style', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css', [], '1.12.1' );

		// Add inline script to initialize the datetimepicker.
		wp_add_inline_script(
			'jquery-ui-timepicker-addon',
			'jQuery(document).ready(function($){ $(".wpgraphql-logging-datepicker").datetimepicker({ dateFormat: "yy-mm-dd", timeFormat: "HH:mm:ss" }); });'
		);

		// Add nonce to sorting links.
		wp_add_inline_script(
			'jquery',
			'jQuery(document).ready(function($){
				var nonce = $("#wpgraphql-logging-sort-nonce").val();
				if ( nonce ) {
					$("th.sortable a").each(function(){
						this.href = this.href + "&_wpnonce=" + nonce;
					});
				}
			});'
		);

		// Allow other plugins to enqueue their own scripts/styles.
		do_action( 'wpgraphql_logging_view_logs_admin_enqueue_scripts', $hook_suffix );
	}

	/**
	 * Adds integrity and crossorigin attributes to scripts.
	 *
	 * @param string $tag The script tag.
	 * @param string $handle The script handle.
	 *
	 * @link https://www.srihash.org/ to generate the integrity and crossorigin attributes.
	 */
	public function add_sri_to_scripts($tag, $handle): void {

		$scripts = [
			'jquery-ui-timepicker-addon' => [
				'integrity'   => 'sha512-s5u/JBtkPg+Ff2WEr49/cJsod95UgLHbC000N/GglqdQuLnYhALncz8ZHiW/LxDRGduijLKzeYb7Aal9h3codZA==',
				'crossorigin' => 'anonymous',
			],
		];

		$scripts = apply_filters( 'wpgraphql_logging_view_logs_add_sri_to_scripts', $scripts );

		if ( ! isset( $scripts[ $handle ] ) ) {
			return;
		}

		$integrity   = $scripts[ $handle ]['integrity'];
		$crossorigin = $scripts[ $handle ]['crossorigin'];

		$tag = str_replace(
			' src=',
			' integrity="' . esc_attr( $integrity ) . '" crossorigin="' . esc_attr( $crossorigin ) . '" src=',
			$tag
		);

		echo wp_kses( $tag, [
			'script' => [
				'src'         => [],
				'integrity'   => [],
				'crossorigin' => [],
				'type'        => [],
				'id'          => [],
			],
			'link'   => [
				'href'        => [],
				'integrity'   => [],
				'crossorigin' => [],
				'rel'         => [],
				'type'        => [],
				'id'          => [],
			],
		] );
	}

	/**
	 * Adds integrity and crossorigin attributes to styles.
	 *
	 * @param string $tag The script tag.
	 * @param string $handle The script handle.
	 *
	 * @link https://www.srihash.org/ to generate the integrity and crossorigin attributes.
	 */
	public function add_sri_to_styles($tag, $handle): void {

		$styles = [
			'jquery-ui-timepicker-addon-style' => [
				'integrity'   => 'sha512-LT9fy1J8pE4Cy6ijbg96UkExgOjCqcxAC7xsnv+mLJxSvftGVmmc236jlPTZXPcBRQcVOWoK1IJhb1dAjtb4lQ==',
				'crossorigin' => 'anonymous',
			],
			'jquery-ui-style'                  => [
				'integrity'   => 'sha512-sOC1C3U/7L42Ao1++jwVCpnllhbxnfD525JBZE2h1+cYnLg3aIE3G1RBWKSr/9cF5LxB1CxPckAvHqzz7O4apQ==',
				'crossorigin' => 'anonymous',
			],
		];
		$styles = apply_filters( 'wpgraphql_logging_view_logs_add_sri_to_styles', $styles );

		if ( ! isset( $styles[ $handle ] ) ) {
			return;
		}

		$integrity   = $styles[ $handle ]['integrity'];
		$crossorigin = $styles[ $handle ]['crossorigin'];

		$tag = str_replace(
			' href=',
			' integrity="' . esc_attr( $integrity ) . '" crossorigin="' . esc_attr( $crossorigin ) . '" href=',
			$tag
		);

		echo wp_kses( $tag, [
			'link' => [
				'src'         => [],
				'integrity'   => [],
				'crossorigin' => [],
				'type'        => [],
				'id'          => [],
			],
		] );
	}

	/**
	 * Renders the admin page for the logs.
	 */
	public function render_admin_page(): void {
		/** @psalm-suppress PossiblyInvalidArgument */
		$action = sanitize_text_field( $_REQUEST['action'] ?? 'link' ); // @phpcs:ignore WordPress.Security.NonceVerification.Recommended

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
		// Check for a download request.
		if ( isset( $_GET['action'] ) && 'download' === $_GET['action'] ) { // @phpcs:ignore WordPress.Security.NonceVerification.Recommended
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
		$redirect_url = apply_filters( 'wpgraphql_logging_filter_redirect_url', $redirect_url, $filters );
		return (string) $redirect_url;
	}

	/**
	 * Retrieves and sanitizes a value from the $_POST superglobal.
	 *
	 * @param string $key The key to retrieve from $_POST.
	 *
	 * @return string|null The sanitized value or null if not set or invalid.
	 */
	protected function get_post_value(string $key): ?string {
		$value = $_POST[ $key ] ?? null; // @phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		if ( ! is_string( $value ) || '' === $value ) {
			return null;
		}
		$value = wp_unslash( $value );
		if ( ! is_string( $value ) ) {
			return null;
		}
		return sanitize_text_field( $value );
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
		if ( ! current_user_can( 'manage_options' ) || ! is_admin() ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'wpgraphql-logging' ) );
		}

		$log_id = isset( $_GET['log'] ) ? absint( $_GET['log'] ) : 0;
		if ( $log_id > 0 ) {
			check_admin_referer( 'wpgraphql-logging-download_' . $log_id );
		}
		$downloader = new DownloadLogService( $this->get_log_service() );
		$downloader->generate_csv( $log_id );
	}

	/**
	 * Renders the view page for a single log entry.
	 */
	protected function render_view_page(): void {
		$log_id = isset( $_GET['log'] ) ? absint( $_GET['log'] ) : 0; // @phpcs:ignore WordPress.Security.NonceVerification.Recommended

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
	 * Retrieves the log service instance.
	 *
	 * @return \WPGraphQL\Logging\Logger\Api\LogServiceInterface The log service instance.
	 */
	protected function get_log_service(): LogServiceInterface {
		return LogStoreService::get_log_service();
	}
}
