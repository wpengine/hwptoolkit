<?php

declare(strict_types=1);

namespace HWP\Previews\Token\Generator;

use HWP\Previews\Token\Generator\Contracts\Token_Generator_Interface;
use HWP\Previews\Token\Manager\Contracts\Token_Manager_Interface;

class Token_Generator implements Token_Generator_Interface {

	private Token_Manager_Interface $token_manager;

	public function __construct( Token_Manager_Interface $token_manager ) {
		$this->token_manager = $token_manager;
	}

	public function generate_token( array $data, string $nonce, int $exp = 360 ): string {
		$data['data']['nonce'] = wp_create_nonce( $nonce );

		return $this->token_manager->generate_token( $data, $exp );
	}

}