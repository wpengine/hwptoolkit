<?php

declare( strict_types = 1 );

namespace HWP\Previews\Post\Parent\Contracts;

use WP_Post_Type;

interface Post_Parent_Manager_Interface {

	public function get_post_statuses_as_parent( WP_Post_Type $post_type ): array;

}