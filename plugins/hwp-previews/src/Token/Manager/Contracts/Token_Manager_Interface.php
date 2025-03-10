<?php

declare(strict_types=1);

namespace HWP\Previews\Token\Manager\Contracts;

use stdClass;

interface Token_Manager_Interface {

	public function generate_token( array $data, int $expiration = 360 ): string;

	/**
	 * @param string $token
	 *
	 * @return stdClass|null
	 */
	public function verify_token( string $token ): ?stdClass;

	/**
	 * Refresh an existing token
	 *
	 * @param string $token The token to refresh
	 * @param int $expiration New expiration time in seconds
	 * @return ?string The refreshed token or null on failure.
	 */
	public function refresh_token(string $token, int $expiration = 360): ?string;

}