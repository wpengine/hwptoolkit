<?php

declare( strict_types=1 );

namespace HWP\Previews\Token\REST;

use HWP\Previews\Token\REST\Contracts\Token_REST_Controller_Interface;
use HWP\Previews\Token\Verifier\Contracts\Token_Verifier_Interface;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * TODO: Maybe separate concerns a bit more. This class is doing a lot of things.
 */
class Token_REST_Controller implements Token_REST_Controller_Interface {

	public const TOKEN_PARAMETER = 'token';

	private Token_Verifier_Interface $verifier;
	private string $nonce_action;

	public function __construct( Token_Verifier_Interface $verifier, string $nonce_action = '' ) {
		$this->verifier     = $verifier;
		$this->nonce_action = $nonce_action;
	}

	public function register_routes( string $namespace ): void {
		$result = register_rest_route( $namespace, '/verify-preview-token', [
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => [ $this, 'route_callback' ],
			'permission_callback' => '__return_true', // todo: is that correct?
			'args'                => [
				self::TOKEN_PARAMETER => [
					'required'          => true,
					'validate_callback' => function ( $param ) {
						return is_string( $param ) && ! empty( $param );
					}
				]
			]
		] );

		if ( ! $result ) {
			// todo: Do something with the error?
		}
	}

	public function route_callback( WP_REST_Request $request ): WP_REST_Response {
		if ( ! $request->has_valid_params() ) {
			return new WP_REST_Response( [ 'error' => 'Body does not have valid parameters' ], 400 );
		}

		$token = (string) $request->get_param( self::TOKEN_PARAMETER );

		if ( ! $token ) {
			return new WP_REST_Response( [ 'error' => 'A token is required' ], 400 );
		}

		$success = $this->verifier->verify_token( $token, $this->nonce_action );

		if ( ! $success ) {
			return new WP_REST_Response( [ 'valid' => false, 'error' => 'The token is not valid.' ], 500 );
		}

		return new WP_REST_Response( [ 'valid' => true, 'data' => [ 'success' => true ] ], 200 );
	}

}