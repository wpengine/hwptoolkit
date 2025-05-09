<?php

declare(strict_types=1);

namespace HWP\Previews\Post\Slug\Contracts;

use WP_Post;

/**
 * Interface Post_Slug_Manager_Interface.
 */
interface Post_Slug_Manager_Interface {

	/**
	 * Forces unique post slug for the given post.
	 *
	 * @param \WP_Post $post The post object to apply unique post slug for.
	 *
	 * @return string
	 */
	public function force_unique_post_slug( WP_Post $post ): string;

	/**
	 * Generates a unique slug for based on the parameters.
	 *
	 * @param string        $slug The slug to be checked.
	 * @param string        $post_type The post type of the post.
	 * @param int           $post_id The ID of the post.
	 * @param array<string> $reserved_slugs Array of reserved slugs.
	 *
	 * @return string
	 */
	public function generate_unique_slug( string $slug, string $post_type, int $post_id, array $reserved_slugs ): string;

}
