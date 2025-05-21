<?php

declare(strict_types=1);

namespace HWP\Previews\Post\Slug\Contracts;

interface Post_Slug_Repository_Interface {
	/**
	 * Verifies if a slug is already taken for a given post type and post ID.
	 *
	 * @param string $slug Post slug.
	 * @param string $post_type Post type.
	 * @param int    $post_id Post ID.
	 */
	public function is_slug_taken( string $slug, string $post_type, int $post_id ): bool;
}
