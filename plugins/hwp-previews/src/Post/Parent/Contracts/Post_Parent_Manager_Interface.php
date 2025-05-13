<?php

declare(strict_types=1);

namespace HWP\Previews\Post\Parent\Contracts;

interface Post_Parent_Manager_Interface {

	/**
	 * Get the post statuses that are applicable as hierarchical for the plugin.
	 *
	 * @param string $post_type Post Type slug.
	 *
	 * @return array<string>
	 */
	public function get_post_statuses_as_parent( string $post_type ): array;

}
