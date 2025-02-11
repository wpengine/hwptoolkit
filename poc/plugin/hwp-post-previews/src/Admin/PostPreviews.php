<?php

declare( strict_types=1 );

namespace HWP\PostPreviews\Admin;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use WP_Post;
use WP_REST_Request;
use WP_REST_Response;

// Note: This is a POC so this would need to be tidied up
class PostPreviews {

	public function init() {
		add_filter( 'preview_post_link', [ $this, 'preview_post_link' ], 10, 2 );
		add_action( 'rest_api_init', [ $this, 'register_rest_routes' ] );
	}

	public function preview_post_link( $url, $post ) {

		if ( ! $post || ! is_a( $post, 'WP_Post' ) ) {
			return $url;
		}

		$options = get_option( Settings::SETTINGS_NAME ) ?: [];
		if ( empty( $options ) ) {
			return $url;
		}

		$enabled      = apply_filters( 'hwp_post_previews_enabled', $options['hwp_post_previews_enabled'] ?? 0, $post );
		$frontend_url = apply_filters( 'hwp_post_previews_frontend_url', $options['hwp_post_previews_frontend_url'] ?? '', $post );

		if ( ! $enabled || empty( $frontend_url ) ) {
			return $url;
		}

		/**
		 * @see https://nextjs.org/docs/pages/building-your-application/configuring/draft-mode
		 */
		$query_args = [
			'slug'    => str_replace( home_url(), '', get_permalink( $post ) ),
			'p'       => $post->ID,
			'type'    => $post->post_type,
			'status'  => $post->post_status,
			'preview' => 'true',
			'secret'  => $this->generate_post_preview_token( $post )
		];
		$url        = add_query_arg( $query_args, $frontend_url );

		return apply_filters( 'hwp_post_preview_link', $url, $query_args, $frontend_url, $post );
	}

	/**
	 * @param WP_Post $post
	 *
	 * @return string
	 */
	protected function generate_post_preview_token( WP_Post $post ): string {

		$secret_key = $this->get_post_preview_secret_key();

		$payload = [
			'post_id' => $post->ID,
			'exp'     => time() + $this->get_post_preview_expiry_time(),
			'nonce'   => wp_create_nonce( 'wp_rest' )
		];

		$payload = apply_filters( 'hwp_post_preview_token_payload', $payload, $post );

		/**
		 * POC - Should be moved to an service
		 */
		$token = JWT::encode( $payload, $secret_key, 'HS256' );

		return apply_filters( 'hwp_post_preview_token', $token, $payload, $post );
	}

	/**
	 * @return string
	 */
	public function get_post_preview_secret_key(): string {
		return defined( 'HWP_SECRET_KEY' ) ? HWP_SECRET_KEY : 'hwp-post-previews';
	}

	/**
	 * @return int
	 */
	public function get_post_preview_expiry_time(): int {
		return apply_filters( 'hwp_post_preview_token_expiry_time', 600 );
	}

	public function register_rest_routes() {
		register_rest_route( 'hwp/post-previews/v1', '/verify-token', [
			'methods'  => 'POST',
			'callback' => [ $this, 'verify_token' ],
		] );
	}

	public function verify_token( WP_REST_Request $request ): WP_REST_Response {

		if ( ! $request->has_valid_params() ) {
			return new WP_REST_Response( [ 'error' => 'Body does not have valid parameters' ], 400 );
		}

		/**
		 * POC - More research needed to make sure this is the best approach
		 */
		$body  = json_decode( $request->get_body() );
		$token = apply_filters( 'hwp_post_preview_verify_token_value', $body->secret ?? '', $request );

		if ( ! $token ) {
			return new WP_REST_Response( [ 'error' => 'A token is required' ], 400 );
		}

		try {
			$decoded = JWT::decode( $token, new Key( $this->get_post_preview_secret_key(), 'HS256' ) );

			if ( $decoded->exp < time() ) {
				// Note: HTTP status is based on RFC for Oauth 2.0 https://www.rfc-editor.org/rfc/rfc6750
				return new WP_REST_Response( [ 'valid' => false, 'error' => 'Token has expired' ], 401 );
			}

			return new WP_REST_Response( [ 'valid' => true, 'data' => [ 'success' => true ] ], 200 );

		} catch ( \Exception $e ) {
			return new WP_REST_Response( [ 'valid' => false, 'error' => $e->getMessage() ], 500 );
		}
	}
}