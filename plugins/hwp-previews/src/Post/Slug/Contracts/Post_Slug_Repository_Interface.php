<?php

declare( strict_types=1 );

namespace HWP\Previews\Post\Slug\Contracts;

interface Post_Slug_Repository_Interface {

	public function is_slug_taken( string $slug, string $post_type, int $post_id ): bool;

}