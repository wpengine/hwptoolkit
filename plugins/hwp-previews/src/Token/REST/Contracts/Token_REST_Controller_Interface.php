<?php

declare(strict_types=1);

namespace HWP\Previews\Token\REST\Contracts;

interface Token_REST_Controller_Interface {

	public function register_routes( string $namespace ): void;

}