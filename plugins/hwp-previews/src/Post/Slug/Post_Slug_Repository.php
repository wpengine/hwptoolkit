<?php

declare( strict_types=1 );

namespace HWP\Previews\Post\Slug;

use HWP\Previews\Post\Slug\Contracts\Post_Slug_Repository_Interface;
use wpdb;

class Post_Slug_Repository implements Post_Slug_Repository_Interface {
	private wpdb $wpdb;

	public function __construct() {
		global $wpdb;
		$this->wpdb = $wpdb;
	}

	public function is_slug_taken( string $slug, string $post_type, int $post_id ): bool {
		$query = "SELECT post_name FROM {$this->wpdb->posts} WHERE post_name = %s AND post_type = %s AND ID != %d LIMIT 1";

		return (bool) $this->wpdb->get_var( $this->wpdb->prepare( $query, $slug, $post_type, $post_id ) );
	}
}
