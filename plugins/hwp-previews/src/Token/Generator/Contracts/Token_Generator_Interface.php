<?php

declare( strict_types=1 );

namespace HWP\Previews\Token\Generator\Contracts;

interface Token_Generator_Interface {

	public function generate_token( array $data, string $nonce, int $exp = 360 ): string;

}