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
 * Class WebhooksAdmin
 *
 * Provides the WordPress admin UI for managing GraphQL webhooks.
 *
 */
class WebhooksAdmin {

	/**
	 * The admin page slug for the webhooks UI.
	 *
	 * @var string
	 */
	const ADMIN_PAGE_SLUG = 'graphql-webhooks';

	/**
	 * Webhook repository instance.
	 *
	 * @var WebhookRepositoryInterface
	 */
	private WebhookRepositoryInterface $repository;

	/**
	 * WebhooksAdmin constructor.
	 *
	 * @param WebhookRepositoryInterface $repository Webhook repository instance.
	 */
	public function __construct( WebhookRepositoryInterface $repository ) {
		$this->repository = $repository;

		add_action( 'admin_menu', [ $this, 'add_admin_menu' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
		add_action( 'admin_post_graphql_webhook_save', [ $this, 'handle_webhook_save' ] );
		add_action( 'admin_post_graphql_webhook_delete', [ $this, 'handle_webhook_delete' ] );
		add_action( 'admin_init', [ $this, 'handle_admin_actions' ] );
		add_action( 'wp_ajax_test_webhook', [ $this, 'ajax_test_webhook' ] );
	}

	/**
	 * Registers the webhooks submenu under the GraphQL admin menu.
	 *
	 * @return void
	 */
	public function add_admin_menu(): void {
		// Add submenu under GraphQL menu using the correct parent slug
		add_submenu_page(
			'graphiql-ide',
			__( 'GraphQL Webhooks', 'wp-graphql-headless-webhooks' ),
			__( 'Webhooks', 'wp-graphql-headless-webhooks' ),
			'manage_options',
			self::ADMIN_PAGE_SLUG,
			[ $this, 'render_admin_page' ]
		);
	}

	/**
	 * Generates the admin URL for the webhooks page.
	 *
	 * @param array $args Optional. Additional query arguments.
	 * @return string The admin URL.
	 */
	public function get_admin_url( array $args = [] ): string {
		$defaults = [ 
			'page' => self::ADMIN_PAGE_SLUG,
		];
		$args = array_merge( $defaults, $args );
		return add_query_arg( $args, admin_url( 'admin.php' ) );
	}

	/**
	 * Enqueues admin CSS and JS assets for the webhooks UI.
	 *
	 * @param string $hook The current admin page hook.
	 * @return void
	 */
	public function enqueue_assets( string $hook ): void {
		// Only enqueue on our admin page
		if ( false === strpos( $hook, self::ADMIN_PAGE_SLUG ) ) {
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
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'restUrl' => rest_url( 'graphql-webhooks/v1/' ),
				'nonce' => wp_create_nonce( 'wp_rest' ),
				'confirmDelete' => __( 'Are you sure you want to delete this webhook?', 'wp-graphql-headless-webhooks' ),
				'headerTemplate' => $this->get_header_row_template(),
			]
		);
	}

	/**
	 * Returns the HTML template for the webhook header row (for JS rendering).
	 *
	 * @return string HTML template.
	 */
	private function get_header_row_template(): string {
		ob_start();
		include __DIR__ . '/views/partials/webhook-header-row.php';
		return ob_get_clean();
	}

	/**
	 * Handles admin actions from the webhooks page.
	 *
	 * @return void
	 */
	public function handle_actions(): void {
		if ( ! isset( $_GET['page'] ) || self::ADMIN_PAGE_SLUG !== $_GET['page'] ) {
			return;
		}

		if ( isset( $_POST['action'] ) && 'save_webhook' === $_POST['action'] ) {
			$this->handle_webhook_save();
		}
	}

	/**
	 * Checks if the current user has permission to manage options.
	 *
	 * @return bool True if user has permission, false otherwise.
	 */
	private function verify_admin_permission(): bool {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.', 'wp-graphql-headless-webhooks' ) );
			return false;
		}
		return true;
	}

	/**
	 * Verifies a nonce for security.
	 *
	 * @param string $nonce_name Nonce field name.
	 * @param string $action     Nonce action.
	 * @return bool True if nonce is valid, false otherwise.
	 */
	private function verify_nonce( string $nonce_name, string $action ): bool {
		if ( ! isset( $_REQUEST[ $nonce_name ] ) || ! wp_verify_nonce( $_REQUEST[ $nonce_name ], $action ) ) {
			wp_die( __( 'Security check failed.', 'wp-graphql-headless-webhooks' ) );
			return false;
		}
		return true;
	}

	/**
	 * Handles saving of a webhook (add or update).
	 *
	 * @return void
	 */
	public function handle_webhook_save() {
		if ( ! $this->verify_admin_permission() || ! $this->verify_nonce( 'webhook_nonce', 'webhook_save' ) ) {
			wp_die( __( 'Unauthorized', 'wp-graphql-webhooks' ) );
		}

		$webhook_id = isset( $_POST['webhook_id'] ) ? intval( $_POST['webhook_id'] ) : 0;
		if ( ! $this->verify_admin_permission() || ! $this->verify_nonce( 'webhook_nonce', 'webhook_save' ) ) {
			wp_die( __( 'Unauthorized', 'wp-graphql-webhooks' ) );
		}

		$webhook_id = isset( $_POST['webhook_id'] ) ? intval( $_POST['webhook_id'] ) : 0;
		$webhook = new Webhook(
			$webhook_id,
			sanitize_text_field( $_POST['webhook_name'] ?? '' ),
			sanitize_text_field( $_POST['webhook_event'] ?? '' ),
			esc_url_raw( $_POST['webhook_url'] ?? '' ),
			sanitize_text_field( $_POST['webhook_method'] ?? 'POST' ),
			$this->sanitize_headers( $_POST['webhook_headers'] ?? [] )
		);

		$validation = $this->repository->validate( $webhook );
		if ( is_wp_error( $validation ) ) {
			wp_die( $validation->get_error_message() );
		}

		if ( $webhook_id > 0 ) {
			$result = $this->repository->update( $webhook_id, $webhook );
			$redirect_args = $result ? [ 'updated' => 1 ] : [ 'error' => 1 ];
		} else {
			$result = $this->repository->create( $webhook );
			$redirect_args = $result ? [ 'added' => 1 ] : [ 'error' => 1 ];
		}


		wp_redirect( add_query_arg( $redirect_args, $this->get_admin_url() ) );
		exit;
	}

	/**
	 * Handles deleting a webhook.
	 *
	 * @return void
	 */
	public function handle_webhook_delete() {
		// To be implemented: Individual deletes are handled through the list table's handle_row_actions.
	}

	/**
	 * Handles bulk admin actions (such as bulk delete).
	 *
	 * @return void
	 */
	public function handle_admin_actions() {
		if (
			( isset( $_REQUEST['action'] ) && 'delete' === $_REQUEST['action'] ) ||
			( isset( $_REQUEST['action2'] ) && 'delete' === $_REQUEST['action2'] )
		) {
			if ( ! $this->verify_admin_permission() || ! $this->verify_nonce( 'bulk-webhooks', '_wpnonce' ) ) {
				return;
			}

			$webhook_id = intval( $_GET['webhook'] );
			$nonce = isset( $_GET['_wpnonce'] ) ? $_GET['_wpnonce'] : '';

			if ( ! wp_verify_nonce( $nonce, 'delete-webhook-' . $webhook_id ) ) {
				wp_die( __( 'Security check failed.', 'wp-graphql-headless-webhooks' ) );
			}

			if ( $this->repository->delete( $webhook_id ) ) {
				wp_redirect( add_query_arg( [ 'deleted' => 1 ], remove_query_arg( [ 'action', 'webhook', '_wpnonce' ], $this->get_admin_url() ) ) );
				exit;
			}
		}

		// Handle bulk delete actions from WP_List_Table
		if ( isset( $_POST['action'] ) && 'delete' === $_POST['action'] || 
		     isset( $_POST['action2'] ) && 'delete' === $_POST['action2'] ) {
			
			if ( ! $this->verify_admin_permission() ) {
				return;
			}

			// Check bulk action nonce
			if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'bulk-webhooks' ) ) {
				wp_die( __( 'Security check failed.', 'wp-graphql-headless-webhooks' ) );
			}

			$webhook_ids = isset( $_POST['webhook'] ) ? array_map( 'intval', (array) $_POST['webhook'] ) : [];
			$deleted = 0;

			foreach ( $webhook_ids as $webhook_id ) {
				if ( $this->repository->delete( $webhook_id ) ) {
					$deleted++;
				}
			}

			if ( $deleted > 0 ) {
				wp_redirect( add_query_arg( [ 'deleted' => $deleted ], remove_query_arg( [ 'action', 'action2', 'webhook', '_wpnonce' ], $this->get_admin_url() ) ) );
				exit;
			}
		}
	}

	/**
	 * Renders the webhooks admin page.
	 *
	 * @return void
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
	 * Renders the list page using WP_List_Table.
	 *
	 * @return void
	 */
	private function render_list_page() {
		require_once __DIR__ . '/WebhooksListTable.php';
		$list_table = new WebhooksListTable( $this->repository );
		include __DIR__ . '/views/webhooks-list.php';
	}

	/**
	 * Renders the form page for adding or editing a webhook.
	 *
	 * @param string $action The action (add or edit).
	 * @return void
	 */
	private function render_form_page( $action ) {
		$webhook = null;
		$form_title = 'add' === $action ? __( 'Add New Webhook', 'wp-graphql-webhooks' ) : __( 'Edit Webhook', 'wp-graphql-webhooks' );
		$submit_text = 'add' === $action ? __( 'Add Webhook', 'wp-graphql-webhooks' ) : __( 'Update Webhook', 'wp-graphql-webhooks' );

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

			$name = $webhook->name;
			$event = $webhook->event;
			$url = $webhook->url;
			$method = $webhook->method;
			$headers = $webhook->headers;
		}

		$events = $this->repository->get_allowed_events();
		$methods = $this->repository->get_allowed_methods();
		$admin = $this;

		include __DIR__ . '/views/webhook-form.php';
	}

	/**
	 * Sanitizes webhook headers from the form input.
	 *
	 * @param array $headers Headers to sanitize.
	 * @return array Sanitized headers.
	 */
	private function sanitize_headers( array $headers ): array {
		$sanitized_headers = [];

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
	 * Handles AJAX requests to test a webhook.
	 *
	 * @return void
	 */
	public function ajax_test_webhook(): void {
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'wp_rest' ) ) {
			wp_send_json_error( [ 
				'message' => __( 'Invalid security token.', 'wp-graphql-headless-webhooks' ),
				'error_code' => 'invalid_nonce'
			] );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 
				'message' => __( 'You do not have permission to test webhooks.', 'wp-graphql-headless-webhooks' ),
				'error_code' => 'insufficient_permissions'
			] );
		}

		$webhook_id = isset( $_POST['webhook_id'] ) ? intval( $_POST['webhook_id'] ) : 0;
		if ( ! $webhook_id ) {
			wp_send_json_error( [ 
				'message' => __( 'Invalid webhook ID.', 'wp-graphql-headless-webhooks' ),
				'error_code' => 'invalid_webhook_id'
			] );
		}

		$webhook = $this->repository->get( $webhook_id );
		if ( ! $webhook ) {
			wp_send_json_error( [ 
				'message' => __( 'Webhook not found.', 'wp-graphql-headless-webhooks' ),
				'error_code' => 'webhook_not_found'
			] );
		}

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

		$args = [ 
			'method' => $webhook->method,
			'timeout' => 15,
			'redirection' => 5,
			'httpversion' => '1.1',
			'blocking' => true,
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

		$args['headers']['X-WPGraphQL-Webhook-Event'] = 'test_webhook';
		$args['headers']['X-WPGraphQL-Webhook-ID'] = (string) $webhook->id;

		$start_time = microtime( true );
		$response = wp_remote_request( $webhook->url, $args );
		$duration_ms = round( ( microtime( true ) - $start_time ) * 1000, 2 );

		if ( is_wp_error( $response ) ) {
			wp_send_json_error( [ 
				'message' => sprintf(
					__( 'Failed to send test webhook: %s', 'wp-graphql-headless-webhooks' ),
					$response->get_error_message()
				),
				'error_code' => $response->get_error_code(),
				'error_data' => $response->get_error_data(),
			] );
		}

		$response_code = wp_remote_retrieve_response_code( $response );
		$response_body = wp_remote_retrieve_body( $response );

		$is_success = $response_code >= 200 && $response_code < 300;

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

		if ( ! empty( $response_body ) ) {
			$response_data['response_body'] = strlen( $response_body ) > 1000
				? substr( $response_body, 0, 1000 ) . '...'
				: $response_body;
		}

		wp_send_json_success( $response_data );
	}
}
