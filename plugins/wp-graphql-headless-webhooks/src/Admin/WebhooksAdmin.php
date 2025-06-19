<?php
/**
 * Admin interface for managing webhooks.
 *
 * @package WPGraphQL\Webhooks\Admin
 */

declare(strict_types=1);

namespace WPGraphQL\Webhooks\Admin;

use WPGraphQL\Webhooks\Entity\Webhook;
use WPGraphQL\Webhooks\Repository\Interfaces\WebhookRepositoryInterface;

/**
 * Admin interface class for managing webhooks.
 */
class WebhooksAdmin {

	/**
	 * Admin page slug constant.
	 */
	const ADMIN_PAGE_SLUG = 'graphql-webhooks';

	/**
	 * Repository instance.
	 *
	 * @var WebhookRepositoryInterface
	 */
	private WebhookRepositoryInterface $repository;

	/**
	 * Constructor
	 *
	 * @param WebhookRepositoryInterface $repository Webhook repository.
	 */
	public function __construct( WebhookRepositoryInterface $repository ) {
		$this->repository = $repository;

		// Hook into WordPress admin
		add_action( 'admin_menu', [ $this, 'add_admin_menu' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
		
		// Handle form submissions via admin-post.php
		add_action( 'admin_post_graphql_webhook_save', [ $this, 'handle_webhook_save' ] );
		add_action( 'admin_post_graphql_webhook_delete', [ $this, 'handle_webhook_delete' ] );
		
		// Handle admin actions
		add_action( 'admin_init', [ $this, 'handle_admin_actions' ] );
		
		// Handle AJAX webhook test
		add_action( 'wp_ajax_test_webhook', [ $this, 'ajax_test_webhook' ] );
	}

	/**
	 * Initialize admin hooks.
	 */
	public function init(): void {
		add_action( 'admin_init', [ $this, 'handle_actions' ] );
	}

	/**
	 * Add admin menu.
	 */
	public function add_admin_menu(): void {
		add_submenu_page(
			'graphiql-ide',
			__( 'Webhooks', 'wp-graphql-headless-webhooks' ),
			__( 'Webhooks', 'wp-graphql-headless-webhooks' ),
			'manage_options',
			self::ADMIN_PAGE_SLUG,
			[ $this, 'render_admin_page' ]
		);
	}

	/**
	 * Generate admin URL.
	 *
	 * @param array $args Query arguments.
	 * @return string Admin URL.
	 */
	public function get_admin_url( array $args = [] ): string {
		$defaults = [
			'page' => self::ADMIN_PAGE_SLUG,
		];
		$args = array_merge( $defaults, $args );
		return add_query_arg( $args, admin_url( 'admin.php' ) );
	}

	/**
	 * Enqueue admin assets.
	 *
	 * @param string $hook Current admin page hook.
	 */
	public function enqueue_assets( string $hook ): void {
		if ( 'graphql_page_' . self::ADMIN_PAGE_SLUG !== $hook ) {
			return;
		}

		wp_enqueue_style(
			'graphql-webhooks-admin',
			WPGRAPHQL_HEADLESS_WEBHOOKS_PLUGIN_URL . 'assets/css/admin.css',
			[],
			WPGRAPHQL_HEADLESS_WEBHOOKS_VERSION
		);

		wp_enqueue_script(
			'graphql-webhooks-admin',
			WPGRAPHQL_HEADLESS_WEBHOOKS_PLUGIN_URL . 'assets/js/admin.js',
			[ 'jquery' ],
			WPGRAPHQL_HEADLESS_WEBHOOKS_VERSION,
			true
		);

		wp_localize_script(
			'graphql-webhooks-admin',
			'wpGraphQLWebhooks',
			[
				'ajaxUrl'        => admin_url( 'admin-ajax.php' ),
				'restUrl'        => rest_url( 'graphql-webhooks/v1/' ),
				'nonce'          => wp_create_nonce( 'wp_rest' ),
				'confirmDelete'  => __( 'Are you sure you want to delete this webhook?', 'wp-graphql-headless-webhooks' ),
				'headerTemplate' => $this->get_header_row_template(),
			]
		);
	}

	/**
	 * Get header row template for JavaScript.
	 *
	 * @return string HTML template.
	 */
	private function get_header_row_template(): string {
		ob_start();
		include __DIR__ . '/views/partials/webhook-header-row.php';
		return ob_get_clean();
	}

	/**
	 * Handle admin actions.
	 */
	public function handle_actions(): void {
		if ( ! isset( $_GET['page'] ) || self::ADMIN_PAGE_SLUG !== $_GET['page'] ) {
			return;
		}

		if ( isset( $_POST['action'] ) && 'save_webhook' === $_POST['action'] ) {
			$this->handle_webhook_save();
		}

		if ( isset( $_GET['action'] ) && 'delete' === $_GET['action'] && isset( $_GET['webhook_id'] ) ) {
			$this->handle_webhook_delete();
		}
	}

	/**
	 * Verify admin permission.
	 *
	 * @return bool Whether user has permission.
	 */
	private function verify_admin_permission(): bool {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.', 'wp-graphql-headless-webhooks' ) );
			return false;
		}
		return true;
	}

	/**
	 * Verify nonce.
	 *
	 * @param string $nonce_name Nonce name.
	 * @param string $action Nonce action.
	 * @return bool Whether nonce is valid.
	 */
	private function verify_nonce( string $nonce_name, string $action ): bool {
		if ( ! isset( $_REQUEST[ $nonce_name ] ) || ! wp_verify_nonce( $_REQUEST[ $nonce_name ], $action ) ) {
			wp_die( __( 'Security check failed.', 'wp-graphql-headless-webhooks' ) );
			return false;
		}
		return true;
	}

	/**
	 * Handle webhook save
	 */
	public function handle_webhook_save() {
		// Verify permissions and nonce
		if ( ! $this->verify_admin_permission() || ! $this->verify_nonce( 'webhook_save', 'webhook_nonce' ) ) {
			wp_die( __( 'Unauthorized', 'wp-graphql-webhooks' ) );
		}

		$webhook_id = isset( $_POST['webhook_id'] ) ? intval( $_POST['webhook_id'] ) : 0;
		$data       = [
			'name'    => sanitize_text_field( $_POST['webhook_name'] ?? '' ),
			'event'   => sanitize_text_field( $_POST['webhook_event'] ?? '' ),
			'url'     => esc_url_raw( $_POST['webhook_url'] ?? '' ),
			'method'  => sanitize_text_field( $_POST['webhook_method'] ?? 'POST' ),
			'headers' => $this->sanitize_headers( $_POST['webhook_headers'] ?? [] ),
		];

		// Validate data
		$validation = $this->repository->validate_data( $data );
		if ( is_wp_error( $validation ) ) {
			wp_die( $validation->get_error_message() );
		}

		// Save webhook
		if ( $webhook_id > 0 ) {
			$result = $this->repository->update( $webhook_id, $data );
			$redirect_args = $result ? [ 'updated' => 1 ] : [ 'error' => 1 ];
		} else {
			$result = $this->repository->create( $data );
			$redirect_args = $result ? [ 'added' => 1 ] : [ 'error' => 1 ];
		}

		// Redirect back to list page
		wp_redirect( add_query_arg( $redirect_args, $this->get_admin_url() ) );
		exit;
	}

	/**
	 * Handle webhook delete
	 */
	public function handle_webhook_delete() {
		// This method will be called via bulk actions from WP_List_Table
		// Individual deletes are handled through the list table's handle_row_actions
	}

	/**
	 * Handle admin actions
	 */
	public function handle_admin_actions() {
		// Handle bulk actions from WP_List_Table
		if ( isset( $_REQUEST['action'] ) && 'delete' === $_REQUEST['action'] || 
		     isset( $_REQUEST['action2'] ) && 'delete' === $_REQUEST['action2'] ) {
			
			if ( ! $this->verify_admin_permission() || ! $this->verify_nonce( 'bulk-webhooks', '_wpnonce' ) ) {
				return;
			}

			$webhook_ids = isset( $_REQUEST['webhook'] ) ? array_map( 'intval', (array) $_REQUEST['webhook'] ) : [];
			$deleted = 0;

			foreach ( $webhook_ids as $webhook_id ) {
				if ( $this->repository->delete( $webhook_id ) ) {
					$deleted++;
				}
			}

			if ( $deleted > 0 ) {
				wp_redirect( add_query_arg( [ 'deleted' => $deleted ], $this->get_admin_url() ) );
				exit;
			}
		}
	}

	/**
	 * Render the admin page
	 */
	public function render_admin_page() {
		$action = isset( $_GET['action'] ) ? sanitize_text_field( $_GET['action'] ) : 'list';

		switch ( $action ) {
			case 'add':
			case 'edit':
				$this->render_form_page( $action );
				break;
			default:
				$this->render_list_page();
				break;
		}
	}

	/**
	 * Render the list page using WP_List_Table
	 */
	private function render_list_page() {
		// Include the custom list table class
		require_once __DIR__ . '/WebhooksListTable.php';
		
		// Create an instance of our list table
		$list_table = new WebhooksListTable( $this->repository );
		
		// Include the list view template
		include __DIR__ . '/views/webhooks-list.php';
	}

	/**
	 * Render the form page (add/edit)
	 *
	 * @param string $action The action (add or edit).
	 */
	private function render_form_page( $action ) {
		$webhook = null;
		$form_title = 'add' === $action ? __( 'Add New Webhook', 'wp-graphql-webhooks' ) : __( 'Edit Webhook', 'wp-graphql-webhooks' );
		$submit_text = 'add' === $action ? __( 'Add Webhook', 'wp-graphql-webhooks' ) : __( 'Update Webhook', 'wp-graphql-webhooks' );

		// Default values for new webhook
		$name = '';
		$event = '';
		$url = '';
		$method = 'POST';
		$headers = [];

		if ( 'edit' === $action ) {
			$webhook_id = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;
			$webhook = $this->repository->get( $webhook_id );

			if ( ! $webhook ) {
				wp_die( __( 'Webhook not found.', 'wp-graphql-webhooks' ) );
			}

			// Extract values from webhook entity
			$name = $webhook->name;
			$event = $webhook->event;
			$url = $webhook->url;
			$method = $webhook->method;
			$headers = $webhook->headers;
		}

		$events = $this->repository->get_allowed_events();
		$methods = $this->repository->get_allowed_methods();
		$admin = $this; // Pass admin instance to template

		include __DIR__ . '/views/webhook-form.php';
	}

	/**
	 * Sanitize headers
	 *
	 * @param array $headers Headers to sanitize.
	 * @return array Sanitized headers.
	 */
	private function sanitize_headers( array $headers ): array {
		$sanitized_headers = [];

		// Handle the form data structure where headers come as separate arrays
		if ( isset( $headers['name'] ) && isset( $headers['value'] ) ) {
			$names = (array) $headers['name'];
			$values = (array) $headers['value'];

			foreach ( $names as $index => $name ) {
				$name = sanitize_text_field( $name );
				$value = sanitize_text_field( $values[ $index ] ?? '' );

				if ( ! empty( $name ) && ! empty( $value ) ) {
					$sanitized_headers[ $name ] = $value;
				}
			}
		}

		return $sanitized_headers;
	}

	/**
	 * Handle AJAX webhook test request.
	 */
	public function ajax_test_webhook(): void {
		// Verify nonce
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'wp_rest' ) ) {
			wp_send_json_error( [
				'message' => __( 'Invalid security token.', 'wp-graphql-headless-webhooks' ),
				'error_code' => 'invalid_nonce'
			] );
		}

		// Check permissions
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [
				'message' => __( 'You do not have permission to test webhooks.', 'wp-graphql-headless-webhooks' ),
				'error_code' => 'insufficient_permissions'
			] );
		}

		// Get webhook ID
		$webhook_id = isset( $_POST['webhook_id'] ) ? intval( $_POST['webhook_id'] ) : 0;
		if ( ! $webhook_id ) {
			wp_send_json_error( [
				'message' => __( 'Invalid webhook ID.', 'wp-graphql-headless-webhooks' ),
				'error_code' => 'invalid_webhook_id'
			] );
		}

		// Get webhook
		$webhook = $this->repository->get( $webhook_id );
		if ( ! $webhook ) {
			wp_send_json_error( [
				'message' => __( 'Webhook not found.', 'wp-graphql-headless-webhooks' ),
				'error_code' => 'webhook_not_found'
			] );
		}

		// Create test payload
		$test_payload = [
			'event' => 'test_webhook',
			'timestamp' => current_time( 'mysql' ),
			'webhook' => [
				'id' => $webhook->id,
				'name' => $webhook->name,
				'url' => $webhook->url,
			],
			'test_data' => [
				'message' => 'This is a test webhook dispatch',
				'random' => wp_generate_password( 12, false ),
			],
		];

		// Log the test attempt
		error_log( sprintf(
			'[WPGraphQL Webhooks] Testing webhook #%d (%s) to %s',
			$webhook->id,
			$webhook->name,
			$webhook->url
		) );

		// Prepare request args
		$args = [
			'method' => $webhook->method,
			'timeout' => 15,
			'redirection' => 5,
			'httpversion' => '1.1',
			'blocking' => true, // We want to wait for the response
			'headers' => array_merge(
				[
					'Content-Type' => 'application/json',
					'User-Agent' => 'WPGraphQL-Webhooks/' . WPGRAPHQL_HEADLESS_WEBHOOKS_VERSION,
				],
				$webhook->headers
			),
			'body' => wp_json_encode( $test_payload ),
			'sslverify' => apply_filters( 'graphql_webhooks_sslverify', true ),
		];

		// Add webhook metadata to headers
		$args['headers']['X-WPGraphQL-Webhook-Event'] = 'test_webhook';
		$args['headers']['X-WPGraphQL-Webhook-ID'] = (string) $webhook->id;

		// Start timing
		$start_time = microtime( true );

		// Make the request
		$response = wp_remote_request( $webhook->url, $args );
		
		// Calculate duration
		$duration_ms = round( ( microtime( true ) - $start_time ) * 1000, 2 );

		// Check for errors
		if ( is_wp_error( $response ) ) {
			error_log( sprintf(
				'[WPGraphQL Webhooks] Test failed for webhook #%d: %s',
				$webhook->id,
				$response->get_error_message()
			) );

			wp_send_json_error( [
				'message' => sprintf(
					__( 'Failed to send test webhook: %s', 'wp-graphql-headless-webhooks' ),
					$response->get_error_message()
				),
				'error_code' => $response->get_error_code(),
				'error_data' => $response->get_error_data(),
			] );
		}

		// Get response details
		$response_code = wp_remote_retrieve_response_code( $response );
		$response_body = wp_remote_retrieve_body( $response );
		$response_headers = wp_remote_retrieve_headers( $response );

		// Log the response
		error_log( sprintf(
			'[WPGraphQL Webhooks] Test response for webhook #%d: HTTP %d in %sms',
			$webhook->id,
			$response_code,
			$duration_ms
		) );

		// Determine if successful (2xx status codes)
		$is_success = $response_code >= 200 && $response_code < 300;

		// Prepare response data
		$response_data = [
			'success' => $is_success,
			'message' => $is_success 
				? sprintf( __( 'Test webhook sent successfully to %s', 'wp-graphql-headless-webhooks' ), $webhook->url )
				: sprintf( __( 'Webhook returned HTTP %d', 'wp-graphql-headless-webhooks' ), $response_code ),
			'webhook_id' => $webhook->id,
			'webhook_name' => $webhook->name,
			'target_url' => $webhook->url,
			'method' => $webhook->method,
			'response_code' => $response_code,
			'duration_ms' => $duration_ms,
			'timestamp' => current_time( 'c' ),
			'test_payload' => $test_payload,
		];

		// Add response body if available (limit to 1000 chars for UI)
		if ( ! empty( $response_body ) ) {
			$response_data['response_body'] = strlen( $response_body ) > 1000 
				? substr( $response_body, 0, 1000 ) . '...' 
				: $response_body;
		}

		// Send success response (even if webhook returned non-2xx, the test itself succeeded)
		wp_send_json_success( $response_data );
	}
}
