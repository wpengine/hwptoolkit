<?php

declare(strict_types=1);

namespace HWP\Previews\Post\Type\Contracts;

use WP_Post_Type;

interface Post_Type_Inspector_Interface {
	/**
	 * Checks if the post type supports Gutenberg.
	 *
	 * @param \WP_Post_Type $post_type Post Type object.
	 */
	public function is_gutenberg_supported( WP_Post_Type $post_type ): bool;

	/**
	 * Checks if the post type is supported by Classic Editor.
	 *
	 * @param string $post_type Post Type slug.
	 */
	public function is_classic_editor_forced( string $post_type ): bool;
}
