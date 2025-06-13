<?php
/**
 * Webhooks Admin UI
 *
 * @package WPGraphQL\Webhooks\Admin
 */

namespace WPGraphQL\Webhooks\Admin;

use WPGraphQL\Webhooks\Repository\WebhookRepository;

/**
 * Class WebhooksAdmin
 */
class WebhooksAdmin {

	/**
	 * Admin page slug constant
	 */
	const ADMIN_PAGE_SLUG = 'webhooks';

	/**
	 * Repository instance
	 *
	 * @var WebhookRepository
	 */
	private WebhookRepository $repository;

	/**
	 * Constructor
	 *
	 * @param WebhookRepository $repository Webhook repository instance.
	 */
	public function __construct( WebhookRepository $repository ) {
		$this->repository = $repository;
	}

	/**
	 * Initialize admin hooks
	 */
	public function init(): void {
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'handle_actions' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		
		// Register admin-post.php handlers
		add_action( 'admin_post_graphql_webhook_save', array( $this, 'handle_webhook_save' ) );
		add_action( 'admin_post_graphql_webhook_bulk_delete', array( $this, 'handle_bulk_delete' ) );
		
		// Register AJAX handlers
		add_action( 'wp_ajax_test_webhook', array( $this, 'handle_test_webhook' ) );
	}

	/**
	 * Add admin menu
	 */
	public function add_admin_menu(): void {
		add_submenu_page(
			'graphiql-ide',
			__( 'Webhooks', 'wp-graphql-headless-webhooks' ),
			__( 'Webhooks', 'wp-graphql-headless-webhooks' ),
			'manage_options',
			self::ADMIN_PAGE_SLUG,
			array( $this, 'render_admin_page' )
		);
	}

	/**
	 * Get admin URL helper
	 *
	 * @param array $args Query arguments.
	 * @return string
	 */
	public function get_admin_url( array $args = array() ): string {
		$defaults = array( 'page' => self::ADMIN_PAGE_SLUG );
		$args     = wp_parse_args( $args, $defaults );
		return admin_url( 'admin.php?' . http_build_query( $args ) );
	}

	/**
	 * Enqueue admin assets
	 *
	 * @param string $hook_suffix Current admin page.
	 */
	public function enqueue_assets( string $hook_suffix ): void {
		// Only load on our admin page - check if we're on the webhooks page
		if ( ! isset( $_GET['page'] ) || $_GET['page'] !== self::ADMIN_PAGE_SLUG ) {
			return;
		}

		$plugin_url = plugin_dir_url( dirname( __DIR__ ) );

		wp_enqueue_style(
			'wp-graphql-webhooks-admin',
			$plugin_url . 'src/Admin/assets/admin.css',
			array(),
			'1.0.0'
		);

		wp_enqueue_script(
			'wp-graphql-webhooks-admin',
			$plugin_url . 'src/Admin/assets/admin.js',
			array( 'jquery' ),
			'1.0.0',
			true
		);

		wp_localize_script(
			'wp-graphql-webhooks-admin',
			'wpGraphQLWebhooks',
			array(
				'restUrl'        => rest_url( 'graphql-webhooks/v1/' ),
				'nonce'          => wp_create_nonce( 'wp_rest' ),
				'ajaxUrl'        => admin_url( 'admin-ajax.php' ),
				'headerTemplate' => $this->get_header_row_template(),
				'confirmDelete'  => __( 'Are you sure you want to delete this webhook?', 'wp-graphql-headless-webhooks' ),
			)
		);
	}

	/**
	 * Get header row template for JavaScript
	 *
	 * @return string
	 */
	private function get_header_row_template(): string {
		ob_start();
		include __DIR__ . '/views/partials/webhook-header-row.php';
		return ob_get_clean();
	}

	/**
	 * Handle admin actions
	 */
	public function handle_actions(): void {
		if ( ! isset( $_GET['page'] ) || self::ADMIN_PAGE_SLUG !== $_GET['page'] ) {
			return;
		}

		// Only handle delete action here since save is handled by admin-post.php
		if ( isset( $_GET['action'] ) && 'delete' === $_GET['action'] ) {
			$this->handle_webhook_delete();
		}
	}

	/**
	 * Verify admin permission
	 *
	 * @return bool
	 */
	private function verify_admin_permission(): bool {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'wp-graphql-headless-webhooks' ) );
			return false;
		}
		return true;
	}

	/**
	 * Verify nonce
	 *
	 * @param string $action Nonce action.
	 * @param string $nonce_field Nonce field name.
	 * @return bool
	 */
	private function verify_nonce( string $action, string $nonce_field = '_wpnonce' ): bool {
		if ( ! isset( $_REQUEST[ $nonce_field ] ) || ! wp_verify_nonce( $_REQUEST[ $nonce_field ], $action ) ) {
			wp_die( esc_html__( 'Security check failed.', 'wp-graphql-headless-webhooks' ) );
			return false;
		}
		return true;
	}

	/**
	 * Handle webhook save
	 */
	public function handle_webhook_save(): void {
		if ( ! $this->verify_admin_permission() || ! $this->verify_nonce( 'graphql_webhook_save', 'graphql_webhook_nonce' ) ) {
			return;
		}

		$webhook_id = isset( $_POST['webhook_id'] ) ? intval( $_POST['webhook_id'] ) : 0;
		$name       = sanitize_text_field( $_POST['webhook_name'] ?? '' );
		$event      = sanitize_text_field( $_POST['webhook_event'] ?? '' );
		$url        = esc_url_raw( $_POST['webhook_url'] ?? '' );
		$method     = sanitize_text_field( $_POST['webhook_method'] ?? 'POST' );

		// Process headers
		$headers = array();
		if ( ! empty( $_POST['webhook_headers']['name'] ) && is_array( $_POST['webhook_headers']['name'] ) ) {
			foreach ( $_POST['webhook_headers']['name'] as $index => $header_name ) {
				$header_name  = sanitize_text_field( $header_name );
				$header_value = sanitize_text_field( $_POST['webhook_headers']['value'][ $index ] ?? '' );
				if ( ! empty( $header_name ) && ! empty( $header_value ) ) {
					$headers[ $header_name ] = $header_value;
				}
			}
		}

		if ( $webhook_id > 0 ) {
			$result = $this->repository->update( $webhook_id, $name, $event, $url, $method, $headers );
		} else {
			$result = $this->repository->create( $name, $event, $url, $method, $headers );
		}

		if ( is_wp_error( $result ) ) {
			$redirect_url = $this->get_admin_url( array( 'error' => urlencode( $result->get_error_message() ) ) );
		} else {
			$redirect_url = $this->get_admin_url( array( 'updated' => 'true' ) );
		}

		wp_safe_redirect( $redirect_url );
		exit;
	}

	/**
	 * Handle webhook delete
	 */
	public function handle_webhook_delete(): void {
		$webhook_id = isset( $_GET['webhook_id'] ) ? intval( $_GET['webhook_id'] ) : 0;
		
		if ( ! $this->verify_admin_permission() || ! $this->verify_nonce( 'delete_webhook_' . $webhook_id ) ) {
			return;
		}

		if ( $webhook_id > 0 ) {
			$this->repository->delete( $webhook_id );
		}

		wp_safe_redirect( $this->get_admin_url( array( 'deleted' => 'true' ) ) );
		exit;
	}

	/**
	 * Handle bulk delete action
	 */
	public function handle_bulk_delete(): void {
		if ( ! $this->verify_admin_permission() || ! $this->verify_nonce( 'bulk_delete_webhooks' ) ) {
			return;
		}

		$bulk_action = $_POST['bulk_action'] ?? $_POST['bulk_action2'] ?? '';
		$webhook_ids = $_POST['webhook_ids'] ?? array();

		if ( 'delete' === $bulk_action && ! empty( $webhook_ids ) ) {
			$deleted_count = 0;
			foreach ( $webhook_ids as $webhook_id ) {
				$webhook_id = intval( $webhook_id );
				if ( $webhook_id > 0 && $this->repository->delete( $webhook_id ) ) {
					$deleted_count++;
				}
			}

			$redirect_args = array();
			if ( $deleted_count > 0 ) {
				$redirect_args['deleted'] = 'true';
				$redirect_args['count'] = $deleted_count;
			} else {
				$redirect_args['error'] = __( 'Failed to delete webhooks.', 'wp-graphql-headless-webhooks' );
			}

			wp_safe_redirect( $this->get_admin_url( $redirect_args ) );
			exit;
		}

		// If no valid action, redirect back
		wp_safe_redirect( $this->get_admin_url() );
		exit;
	}

	/**
	 * Handle webhook test via AJAX
	 */
	public function handle_test_webhook(): void {
		// Verify nonce
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'wp_rest' ) ) {
			wp_send_json_error( __( 'Security check failed.', 'wp-graphql-headless-webhooks' ) );
		}

		// Verify permissions
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Insufficient permissions.', 'wp-graphql-headless-webhooks' ) );
		}

		$webhook_id = isset( $_POST['webhook_id'] ) ? intval( $_POST['webhook_id'] ) : 0;
		if ( ! $webhook_id ) {
			wp_send_json_error( __( 'Invalid webhook ID.', 'wp-graphql-headless-webhooks' ) );
		}

		// Get the webhook
		$webhook = $this->repository->get( $webhook_id );
		if ( ! $webhook ) {
			wp_send_json_error( __( 'Webhook not found.', 'wp-graphql-headless-webhooks' ) );
		}

		// Create test payload based on the event type
		$test_payload = $this->get_test_payload_for_event( $webhook->event );

		// Send the webhook using a synchronous request for testing
		$args = [ 
			'headers' => $webhook->headers ?: [ 'Content-Type' => 'application/json' ],
			'timeout' => 10,
			'blocking' => true, // We need blocking for test to get response
		];

		$payload = apply_filters( 'graphql_webhooks_payload', $test_payload, $webhook );

		if ( strtoupper( $webhook->method ) === 'GET' ) {
			$url = add_query_arg( $payload, $webhook->url );
			$args['method'] = 'GET';
		} else {
			$url = $webhook->url;
			$args['method'] = strtoupper( $webhook->method );
			$args['body'] = wp_json_encode( $payload );
			if ( empty( $args['headers']['Content-Type'] ) ) {
				$args['headers']['Content-Type'] = 'application/json';
			}
		}

		$response = wp_remote_request( $url, $args );

		if ( is_wp_error( $response ) ) {
			wp_send_json_error( $response->get_error_message() );
		}

		// Get response details
		$response_code = wp_remote_retrieve_response_code( $response );
		$response_body = wp_remote_retrieve_body( $response );
		
		// Strip HTML tags and decode entities from response body
		$response_body = wp_strip_all_tags( $response_body );
		$response_body = html_entity_decode( $response_body, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
		
		// Send structured response data
		wp_send_json_success( array( 
			'message' => __( 'Webhook sent successfully!', 'wp-graphql-headless-webhooks' ),
			'response_code' => $response_code,
			'response_body' => substr( $response_body, 0, 200 ) // Limit response body to 200 chars
		) );
	}

	/**
	 * Get test payload for a specific event
	 *
	 * @param string $event Event type.
	 * @return array
	 */
	private function get_test_payload_for_event( string $event ): array {
		$base_payload = array(
			'event'     => $event,
			'timestamp' => current_time( 'mysql' ),
			'test'      => true,
			'test_mode' => true, // Additional flag to clearly indicate test mode
			'message'   => 'This is a TEST webhook payload - no production data was affected',
		);

		// Add event-specific test data
		switch ( $event ) {
			case 'smart_cache_created':
			case 'smart_cache_updated':
			case 'smart_cache_deleted':
				$base_payload['data'] = array(
					'key'        => 'test:post:999999',
					'action'     => str_replace( 'smart_cache_', '', $event ),
					'purge_url'  => home_url( '/test-graphql-endpoint' ),
					'test_note'  => 'This is test data - no actual cache was purged',
				);
				break;

			case 'smart_cache_nodes_purged':
				$base_payload['data'] = array(
					'key'   => 'test:list:post',
					'nodes' => array(
						array( 'id' => 'test_node_1', 'type' => 'post' ),
						array( 'id' => 'test_node_2', 'type' => 'term' ),
					),
					'test_note' => 'This is test data - no actual nodes were purged',
				);
				break;

			case 'post.published':
			case 'post.updated':
			case 'post.deleted':
				$base_payload['data'] = array(
					'id'        => 999999,
					'title'     => 'Test Post (Not Real)',
					'status'    => 'test',
					'author'    => 0,
					'test_note' => 'This is test data - no actual post exists',
				);
				break;

			case 'user.created':
				$base_payload['data'] = array(
					'id'        => 999999,
					'username'  => 'test_webhook_user',
					'email'     => 'test@webhook.local',
					'role'      => 'test',
					'test_note' => 'This is test data - no actual user exists',
				);
				break;

			default:
				$base_payload['data'] = array(
					'message'   => 'This is a test webhook payload',
					'test_note' => 'This is test data for event: ' . $event,
				);
				break;
		}

		/**
		 * Filter the test payload for webhook testing
		 *
		 * @param array  $base_payload The test payload data
		 * @param string $event        The event type being tested
		 */
		return apply_filters( 'graphql_webhooks_test_payload', $base_payload, $event );
	}

	/**
	 * Render admin page
	 */
	public function render_admin_page(): void {
		$action = $_GET['action'] ?? '';
		$admin  = $this;

		// Display admin notices
		if ( isset( $_GET['updated'] ) ) {
			$message = __( 'Webhook saved successfully.', 'wp-graphql-headless-webhooks' );
			$type    = 'success';
			include __DIR__ . '/views/admin-notice.php';
		}

		if ( isset( $_GET['deleted'] ) ) {
			$count = isset( $_GET['count'] ) ? intval( $_GET['count'] ) : 1;
			if ( $count > 1 ) {
				$message = sprintf(
					_n(
						'%d webhook deleted successfully.',
						'%d webhooks deleted successfully.',
						$count,
						'wp-graphql-headless-webhooks'
					),
					$count
				);
			} else {
				$message = __( 'Webhook deleted successfully.', 'wp-graphql-headless-webhooks' );
			}
			$type = 'success';
			include __DIR__ . '/views/admin-notice.php';
		}

		if ( isset( $_GET['error'] ) ) {
			$message = sanitize_text_field( $_GET['error'] );
			$type    = 'error';
			include __DIR__ . '/views/admin-notice.php';
		}

		// Render appropriate view
		if ( 'add' === $action || 'edit' === $action ) {
			$webhook_id = isset( $_GET['webhook_id'] ) ? intval( $_GET['webhook_id'] ) : 0;
			$webhook    = null;

			if ( 'edit' === $action && $webhook_id > 0 ) {
				$webhook = $this->repository->get( $webhook_id );
				if ( ! $webhook ) {
					wp_die( esc_html__( 'Webhook not found.', 'wp-graphql-headless-webhooks' ) );
				}
			}

			$events  = $this->repository->get_allowed_events();
			$methods = $this->repository->get_allowed_methods();
			
			// Convert simple array to associative array for the form
			$methods = array_combine($methods, $methods);

			// Set form variables
			$form_title  = 'edit' === $action ? __( 'Edit Webhook', 'wp-graphql-headless-webhooks' ) : __( 'Add New Webhook', 'wp-graphql-headless-webhooks' );
			$submit_text = 'edit' === $action ? __( 'Update Webhook', 'wp-graphql-headless-webhooks' ) : __( 'Add Webhook', 'wp-graphql-headless-webhooks' );

			// Set default values for new webhook
			if ( 'add' === $action ) {
				$webhook_id = 0;
				$name    = '';
				$event   = '';
				$url     = '';
				$method  = 'POST';
				$headers = array();
			} else {
				// Extract values from webhook entity
				$webhook_id = $webhook->id;
				$name    = $webhook->name;
				$event   = $webhook->event;
				$url     = $webhook->url;
				$method  = $webhook->method;
				$headers = $webhook->headers;
			}

			include __DIR__ . '/views/webhook-form.php';
		} else {
			$webhooks = $this->repository->get_all();
			include __DIR__ . '/views/webhooks-list.php';
		}
	}
}
