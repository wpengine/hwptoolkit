<?php
namespace WPGraphQL\Webhooks\Handlers;

use WPGraphQL\Webhooks\Entity\Webhook;
use WPGraphQL\Webhooks\Handlers\Interfaces\Handler;

/**
 * Class WebhookHandler
 *
 * Sends the webhook to the configured URL when an event is triggered.
 */
class WebhookHandler implements Handler {

	/**
	 * Handle the event payload for a specific webhook.
	 *
	 * @param Webhook $webhook The Webhook entity instance.
	 * @param array   $payload The event payload data.
	 *
	 * @return void
	 */
	public function handle( Webhook $webhook, array $payload ): void {
		// Log webhook dispatch initiation
		$dispatch_timestamp = current_time( 'mysql' );
		error_log( "\n========== WEBHOOK DISPATCH ==========" );
		error_log( "Timestamp: {$dispatch_timestamp}" );
		error_log( "Webhook: {$webhook->name} (ID: {$webhook->id})" );
		error_log( "Event: {$webhook->event}" );
		error_log( "Target URL: {$webhook->url}" );
		error_log( "Method: {$webhook->method}" );
		
		$args = [ 
			'headers' => $webhook->headers ?: [ 'Content-Type' => 'application/json' ],
			'timeout' => apply_filters( 'graphql_webhooks_timeout', 15 ), // Configurable timeout with a default of 15 seconds
			'blocking' => false,
			'sslverify' => apply_filters( 'graphql_webhooks_sslverify', true, $webhook ),
			'user-agent' => 'WPGraphQL-Webhooks/' . ( defined( 'WPGRAPHQL_WEBHOOKS_VERSION' ) ? WPGRAPHQL_WEBHOOKS_VERSION : '1.0.0' ),
		];
		
		// Apply payload filter
		$payload = apply_filters( 'graphql_webhooks_payload', $payload, $webhook );
		
		// Add webhook metadata to payload
		$payload['_webhook_meta'] = [
			'sent_at' => $dispatch_timestamp,
			'webhook_id' => $webhook->id,
			'webhook_name' => $webhook->name,
			'event_type' => $webhook->event,
		];

		// Handle different HTTP methods
		if ( strtoupper( $webhook->method ) === 'GET' ) {
			$url = add_query_arg( $payload, $webhook->url );
			$args['method'] = 'GET';
			error_log( "Payload (GET query params): " . wp_json_encode( $payload ) );
		} else {
			$url = $webhook->url;
			$args['method'] = strtoupper( $webhook->method );
			$args['body'] = wp_json_encode( $payload );
			
			// Ensure Content-Type header is set for non-GET requests
			if ( empty( $args['headers']['Content-Type'] ) ) {
				$args['headers']['Content-Type'] = 'application/json';
			}
			
			error_log( "Payload ({$args['method']} body): " . $args['body'] );
			error_log( "Payload size: " . strlen( $args['body'] ) . " bytes" );
		}
		
		// Log headers
		error_log( "Headers: " . wp_json_encode( $args['headers'] ) );
		
		// For test mode or debugging, optionally use blocking mode
		if ( apply_filters( 'graphql_webhooks_test_mode', false, $webhook ) ) {
			$args['blocking'] = true;
			error_log( "Test mode enabled - using blocking request" );
		}
		
		error_log( "====================================\n" );
		
		// Send the webhook
		$start_time = microtime( true );
		$response = wp_remote_request( $url, $args );
		$end_time = microtime( true );
		$duration = round( ( $end_time - $start_time ) * 1000, 2 );
		
		// Log response if in blocking mode
		if ( $args['blocking'] ) {
			if ( is_wp_error( $response ) ) {
				error_log( "\n========== WEBHOOK ERROR ==========" );
				error_log( "❌ ERROR: " . $response->get_error_message() );
				error_log( "Duration: {$duration}ms" );
				error_log( "==================================\n" );
			} else {
				$response_code = wp_remote_retrieve_response_code( $response );
				$response_body = wp_remote_retrieve_body( $response );
				
				error_log( "\n========== WEBHOOK RESPONSE ==========" );
				error_log( ( $response_code >= 200 && $response_code < 300 ? "✅" : "⚠️" ) . " Response Code: {$response_code}" );
				error_log( "Duration: {$duration}ms" );
				error_log( "Response Body: " . ( strlen( $response_body ) > 500 ? substr( $response_body, 0, 500 ) . '...' : $response_body ) );
				error_log( "====================================\n" );
			}
		}
		
		// Trigger action after webhook is sent
		do_action( 'graphql_webhooks_sent', $webhook, $payload, $response );
	}
}