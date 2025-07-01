<?php
/**
 * REST API endpoint for testing webhooks.
 *
 * @package WPGraphQL\Webhooks\Rest
 */

declare(strict_types=1);

namespace WPGraphQL\Webhooks\Rest;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
use WPGraphQL\Webhooks\Repository\WebhookRepository;

/**
 * REST endpoint for testing webhooks.
 */
class WebhookTestEndpoint {

	/**
	 * Repository instance.
	 *
	 * @var WebhookRepository
	 */
	private WebhookRepository $repository;

	/**
	 * Constructor.
	 *
	 * @param WebhookRepository $repository Repository instance.
	 */
	public function __construct( WebhookRepository $repository ) {
		$this->repository = $repository;
	}

	/**
	 * Register REST routes.
	 */
	public function register_routes(): void {
		register_rest_route(
			'graphql-webhooks/v1',
			'/test',
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'test_webhook' ],
				'permission_callback' => [ $this, 'permission_callback' ],
				'args'                => [
					'webhook_id' => [
						'required'          => true,
						'type'              => 'integer',
						'sanitize_callback' => 'absint',
					],
				],
			]
		);
	}

	/**
	 * Permission callback.
	 *
	 * @return bool Whether user has permission.
	 */
	public function permission_callback(): bool {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Test a webhook.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response.
	 */
	public function test_webhook( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$webhook_id = $request->get_param( 'webhook_id' );
		$webhook    = $this->repository->get( $webhook_id );

		if ( ! $webhook ) {
			return new WP_Error(
				'webhook_not_found',
				__( 'Webhook not found.', 'wpgraphql-webhooks' ),
				[ 'status' => 404 ]
			);
		}

		// Log test initiation
		$test_timestamp = current_time( 'mysql' );
		error_log( "\n========== WEBHOOK TEST INITIATED ==========" );
		error_log( "Timestamp: {$test_timestamp}" );
		error_log( "Webhook ID: {$webhook_id}" );
		error_log( "Webhook Name: {$webhook->name}" );
		error_log( "Target URL: {$webhook->url}" );
		error_log( "HTTP Method: {$webhook->method}" );
		error_log( "Event: {$webhook->event}" );
		error_log( "Headers: " . wp_json_encode( $webhook->headers ) );
		error_log( "==========================================\n" );

		// Create test payload
		$test_payload = [
			'event' => 'test',
			'timestamp' => $test_timestamp,
			'webhook' => [
				'id' => $webhook->id,
				'name' => $webhook->name,
				'event' => $webhook->event,
			],
			'test_data' => [
				'message' => 'This is a test webhook payload',
				'triggered_by' => wp_get_current_user()->user_login,
				'site_url' => get_site_url(),
			],
		];

		// Allow filtering of test payload
		$test_payload = apply_filters( 'graphql_webhooks_test_payload', $test_payload, $webhook );

		// Trigger test event with enhanced logging
		$start_time = microtime( true );
		
		try {
			do_action( 'wpgraphql_webhooks_test_event', $webhook, $test_payload );
			
			$end_time = microtime( true );
			$duration = round( ( $end_time - $start_time ) * 1000, 2 ); // Convert to milliseconds
			
			error_log( "\n========== WEBHOOK TEST COMPLETED ==========" );
			error_log( "✅ SUCCESS: Test webhook dispatched" );
			error_log( "Duration: {$duration}ms" );
			error_log( "Completed at: " . current_time( 'mysql' ) );
			error_log( "==========================================\n" );

			return new WP_REST_Response(
				[
					'success' => true,
					'message' => __( 'Test webhook dispatched successfully.', 'wpgraphql-webhooks' ),
					'details' => [
						'webhook_id' => $webhook_id,
						'webhook_name' => $webhook->name,
						'target_url' => $webhook->url,
						'method' => $webhook->method,
						'duration_ms' => $duration,
						'timestamp' => $test_timestamp,
						'payload_size' => strlen( wp_json_encode( $test_payload ) ) . ' bytes',
					],
				],
				200
			);
		} catch ( \Exception $e ) {
			error_log( "\n========== WEBHOOK TEST ERROR ==========" );
			error_log( "❌ ERROR: " . $e->getMessage() );
			error_log( "Stack trace: " . $e->getTraceAsString() );
			error_log( "========================================\n" );

			return new WP_Error(
				'webhook_test_failed',
				sprintf(
					/* translators: %s: error message */
					__( 'Failed to dispatch test webhook: %s', 'wpgraphql-webhooks' ),
					$e->getMessage()
				),
				[ 'status' => 500 ]
			);
		}
	}
}
