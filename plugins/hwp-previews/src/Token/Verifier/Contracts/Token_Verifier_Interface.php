<?php

declare( strict_types=1 );

namespace HWP\Previews\Token\Verifier\Contracts;

use stdClass;

interface Token_Verifier_Interface {

	public function verify_token( string $token, string $nonce_action ): ?stdClass;

}