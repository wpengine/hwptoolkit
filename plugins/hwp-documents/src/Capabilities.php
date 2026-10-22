<?php

declare(strict_types=1);

namespace HWP\Documents;

/**
 * Grants the document capabilities.
 *
 * @package HWP\Documents
 */
final class Capabilities {
	/**
	 * Callback for the 'user_has_cap' filter.
	 *
	 * Users who can manage options can read and edit documents. Other users
	 * need the read or edit capability added to their role or account.
	 *
	 * @param array<string,bool> $allcaps The user's capabilities.
	 *
	 * @return array<string,bool> The user's capabilities.
	 */
	public static function grant_to_admins( array $allcaps ): array {
		if ( isset( $allcaps['manage_options'] ) && true === (bool) $allcaps['manage_options'] ) {
			$allcaps[ Identifiers::READ_CAPABILITY ] = true;
			$allcaps[ Identifiers::EDIT_CAPABILITY ] = true;
		}

		return $allcaps;
	}
}
