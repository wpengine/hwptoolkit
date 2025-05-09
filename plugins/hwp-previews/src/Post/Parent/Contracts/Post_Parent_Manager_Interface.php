<?php

declare(strict_types=1);

namespace HWP\Previews\Post\Parent\Contracts;

use WP_Post_Type;

interface Post_Parent_Manager_Interface {

	/**
	 * Get the post statuses that are applicable as hierarchical for the plugin.
	 *
	 * @param \WP_Post_Type $post_type Post type object.
	 *
	 * @return array<string>
	 */
	public function get_post_statuses_as_parent( WP_Post_Type $post_type ): array;

}
