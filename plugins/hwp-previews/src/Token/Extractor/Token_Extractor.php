<?php

declare(strict_types=1);

namespace HWP\Previews\Token\Extractor;

use HWP\Previews\Token\Extractor\Contracts\Token_Extractor_Interface;

class Token_Extractor implements Token_Extractor_Interface {

	public function get_token(): string {
		$auth_header = $_SERVER['HTTP_AUTHORIZATION'] ?? false;

		if ( ! $auth_header ) {
			return '';
		}

		list( $token ) = sscanf( $auth_header, 'Bearer %s' );

		return $token;
	}

}