<?php

declare(strict_types=1);

namespace HWP\Previews\Shared;

class Helpers {

	/**
	 * Gets the post types that are public as a name => label array.
	 *
	 * @return array<string, string>
	 */
	public static function get_public_post_types(): array {
		return wp_list_pluck(
			get_post_types( [ 'public' => true ], 'objects' ),
			'label',
			'name'
		);
	}

}
