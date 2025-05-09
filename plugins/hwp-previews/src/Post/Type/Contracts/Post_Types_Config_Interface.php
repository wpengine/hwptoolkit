<?php

declare(strict_types=1);

namespace HWP\Previews\Post\Type\Contracts;

use WP_Post_Type;

interface Post_Types_Config_Interface {

	/**
	 * Get the post types that are applicable for previews.
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
	 * @param \WP_Post_Type $post_type The post type object.
	 */
	public function is_hierarchical( WP_Post_Type $post_type ): bool;

	/**
	 * Check if a post type supports Gutenberg.
	 *
	 * @param \WP_Post_Type $post_type The post type object.
	 */
	public function supports_gutenberg( WP_Post_Type $post_type ): bool;

	/**
	 * Gets all publicly available post types as key value array, where key is a post type slug and value is a label.
	 *
	 * @return array<string, string>
	 */
	public function get_publicly_available_post_types(): array;

}
