<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Admin;

/**
 * The admin notice class for WPGraphQL Logging.
 *
 * @package WPGraphQL\Logging
 *
 * @since 0.0.1
 */
class AdminNotice {
	/**
	 * The admin page slug.
	 *
	 * @var string
	 */
	public const ADMIN_NOTICE_KEY = 'wpgraphql-logging-admin-notice';

	/**
	 * The instance of the admin notice.
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

		do_action( 'wpgraphql_logging_admin_notice_init', self::$instance );

		return self::$instance;
	}

	/**
	 * Setup the admin notice.
	 */
	public function setup(): void {
		$key          = self::ADMIN_NOTICE_KEY;
		$is_dismissed = $this->is_notice_dismissed();

		// Exit if the notice has been dismissed.
		if ( $is_dismissed ) {
			return;
		}

		add_action( 'admin_notices', [ $this, 'register_admin_notice' ], 10, 0 );
		add_action( 'wp_ajax_' . $key, [ $this, 'process_ajax_request' ], 10, 0 );
	}

	/**
	 * Register admin notice to inform users about WPGraphQL Logging.
	 */
	public function register_admin_notice(): void {
		$template = __DIR__ . '/View/Templates/WPGraphQLLoggerNotice.php';

		if ( ! file_exists( $template ) ) {
			return;
		}

		require $template; // phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingVariable
	}

	/**
	 * Process the AJAX request.
	 */
	public function process_ajax_request(): void {
		$key = self::ADMIN_NOTICE_KEY;
		if ( ! isset( $_POST['action'] ) || esc_attr( $key ) !== $_POST['action'] ) {
			return;
		}

		check_ajax_referer( $key );

		self::dismiss_admin_notice();
	}

	/**
	 * Check if the admin notice is dismissed.
	 */
	public function is_notice_dismissed(): bool {
		$key = self::ADMIN_NOTICE_KEY;
		return (bool) get_user_meta( get_current_user_id(), $key, true );
	}

	/**
	 * Dismiss the admin notice.
	 */
	public static function dismiss_admin_notice(): void {
		$key = self::ADMIN_NOTICE_KEY;
		update_user_meta( get_current_user_id(), $key, 1 );
	}
}
