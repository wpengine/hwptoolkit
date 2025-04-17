<?php

declare(strict_types=1);

namespace HWP\Previews\Token\Verifier;

use HWP\Previews\Token\Verifier\Contracts\Token_Verifier_Interface;
use HWP\Previews\Token\Manager\Contracts\Token_Manager_Interface;
use stdClass;

class Token_Verifier implements Token_Verifier_Interface {

	private Token_Manager_Interface $token_manager;

	public function __construct( Token_Manager_Interface $token_manager ) {
		$this->token_manager = $token_manager;
	}

	public function verify_token( string $token, string $nonce_action ): ?stdClass {
		$token = $this->token_manager->verify_token( $token );

		if (
			! $token ||
			empty( $token->data->nonce ) ||
			wp_verify_nonce( $token->data->nonce, $nonce_action ) !== 1
		) {
			return null;
		}

		return $token;
	}

}