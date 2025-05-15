<?php

declare(strict_types=1);

namespace HWP\Previews\Post\Slug\Contracts;

use WP_Post;
use wp_rewrite;
interface Post_Slug_Manager_Interface {

	public function force_unique_post_slug( WP_Post $post, wp_rewrite $wp_rewrite ): string;

	public function generate_unique_slug( string $slug, string $post_type, int $post_id, array $reserved_slugs ): string;

}