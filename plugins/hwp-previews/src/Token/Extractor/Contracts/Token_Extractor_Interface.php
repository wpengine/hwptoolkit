<?php

declare(strict_types=1);

namespace HWP\Previews\Token\Extractor\Contracts;

interface Token_Extractor_Interface {

	public function get_token(): string;

}