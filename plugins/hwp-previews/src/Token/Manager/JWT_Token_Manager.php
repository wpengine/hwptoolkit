<?php

declare( strict_types=1 );

namespace HWP\Previews\Token\Manager;

use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use HWP\Previews\Token\Manager\Contracts\Token_Manager_Interface;
use stdClass;

class JWT_Token_Manager implements Token_Manager_Interface {

	protected string $secret;
	protected string $alg;

	public function __construct( string $secret, string $alg = 'HS256' ) {
		$this->secret = $secret;
		$this->alg    = $alg;
	}

	public function generate_token( array $data, int $expiration = 360 ): string {
		$issued_at  = time();
		$token_data = array_merge( $data, [
			'iat' => $issued_at,
			'exp' => $issued_at + $expiration,
			'iss' => get_site_url(),
		] );

		try {
			return JWT::encode( $token_data, $this->secret, $this->alg );
		} catch ( Exception $e ) {
			// TODO: Exception handling.
			return '';
		}
	}

	/**
	 * Todo: proper exception handling.
	 *
	 * @param string $token
	 *
	 * @return stdClass|null
	 */
	public function verify_token( string $token, int $leeway = 60 ): ?stdClass {
		if ( empty( $token ) ) {
			return null;
		}

		JWT::$leeway = $leeway;

		try {
			$decoded = JWT::decode( $token, new Key( $this->secret, $this->alg ) );

			// Check if the token has expired.
			if ( isset( $decoded->exp ) && time() > $decoded->exp ) {
				return null;
			}

			// Check if the token was issued by this server.
			if ( isset( $token->iss ) && ! in_array( $token->iss, (array) get_bloginfo('url')) ) {
				// See https://github.com/wp-graphql/wp-graphql-jwt-authentication/issues/111
				add_filter( 'graphql_response_status_code', fn() => 401 );

				return null;
			}

			// TODO: Any more checks here?

			return $decoded;
		} catch ( Exception $e ) {
			return null;
		}
	}

	public function refresh_token( string $token, int $expiration = 360 ): ?string {
		$token_data = $this->verify_token( $token );

		if ( null === $token_data ) {
			return null;
		}

		// Convert stdClass to array
		$data = json_decode( json_encode( $token_data ), true );

		// Remove timing claims that will be re-added
		unset( $data['iat'] );
		unset( $data['exp'] );
		unset( $data['iss'] );

		$refreshed_token = $this->generate_token( $data, $expiration );

		return empty( $refreshed_token ) ? null : $refreshed_token;
	}
}