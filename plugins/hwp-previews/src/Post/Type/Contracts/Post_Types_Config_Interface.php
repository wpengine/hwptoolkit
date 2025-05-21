<?php

declare(strict_types=1);

namespace HWP\Previews\Post\Type\Contracts;

interface Post_Types_Config_Interface {
	/**
	 * Set the post types that are applicable for previews.
	 *
	 * @param array<string> $post_types The post type to check.
	 */
	public function set_post_types( array $post_types ): self;

	/**
	 * Get the post types that are applicable for previews.
	 *
	 * @return array<string> Post types that are applicable for previews.
	 */
	public function get_post_types(): array;

	/**
	 * Check if a post type is applicable for previews.
	 *
	 * @param string $post_type The post type to check.
	 */
	public function is_post_type_applicable( string $post_type ): bool;

	/**
	 * Check if a post type is hierarchical.
	 *
	 * @param string $post_type The post type.
	 */
	public function is_hierarchical( string $post_type ): bool;

	/**
	 * Check if a post type supports Gutenberg.
	 *
	 * @param string $post_type Post Type slug.
	 */
	public function gutenberg_editor_enabled( string $post_type ): bool;

	/**
	 * Gets all publicly available post types as key value array, where key is a post type slug and value is a label.
	 *
	 * @return array<string, string>
	 */
	public function get_public_post_types(): array;
}
