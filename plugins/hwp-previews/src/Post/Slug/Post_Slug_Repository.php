<?php

declare(strict_types=1);

namespace HWP\Previews\Post\Slug;

use HWP\Previews\Post\Slug\Contracts\Post_Slug_Repository_Interface;

/**
 * Post Slug Repository functionality.
 */
class Post_Slug_Repository implements Post_Slug_Repository_Interface {
	/**
	 * .
	 *
	 * @param string $slug .
	 * @param string $post_type .
	 * @param int    $post_id .
	 */
	public function is_slug_taken( string $slug, string $post_type, int $post_id ): bool {
		/**
		 * .
		 *
		 * @var \wpdb $wpdb
		 */
		global $wpdb;

		/** @psalm-suppress TooManyArguments */
		return (bool) $wpdb->get_var( // phpcs:ignore WordPress.DB
			$wpdb->prepare(
				// @phpstan-ignore-next-line
				"SELECT post_name FROM {$wpdb->posts} WHERE post_name = %s AND post_type = %s AND ID != %d LIMIT 1",
				$slug,
				$post_type,
				$post_id
			)
		);
	}
}
