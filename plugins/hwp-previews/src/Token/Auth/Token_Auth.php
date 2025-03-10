<?php

declare( strict_types=1 );

namespace HWP\Previews\Token\Auth;

use HWP\Previews\Token\Auth\Contracts\Token_Auth_Interface;
use HWP\Previews\Token\Verifier\Contracts\Token_Verifier_Interface;

class Token_Auth implements Token_Auth_Interface {

	public const PREVIEW_NONCE_ACTION = 'hwp_preview_nonce';

	private Token_Verifier_Interface $verifier;

	public function __construct( Token_Verifier_Interface $verifier ) {
		$this->verifier = $verifier;
	}

	public function determine_preview_user( string $token ): int {
		$token = $this->verifier->verify_token( $token, self::PREVIEW_NONCE_ACTION );
		if ( ! $token || empty( $token->data->user->id ) ) {
			return 0;
		}

		return $token->data->user->id;
	}

}