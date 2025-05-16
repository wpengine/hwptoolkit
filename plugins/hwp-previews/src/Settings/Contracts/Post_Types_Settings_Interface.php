<?php

declare(strict_types=1);

namespace HWP\Previews\Settings\Contracts;

interface Post_Types_Settings_Interface {
	/**
	 * Gets all enabled for the preview functionality post types.
	 *
	 * @param array<string> $default_value Default post types.
	 *
	 * @return array<string>
	 */
	public function post_types_enabled( array $default_value = [] ): array;

	/**
	 * Gets URL template for the given post type.
	 *
	 * @param string $post_type Post type slug.
	 * @param string $default_value Default URL template.
	 */
	public function url_template( string $post_type, string $default_value = '' ): string;

	/**
	 * If the post type post statuses should have unique slug for the post type.
	 *
	 * @param string $post_type Post type slug.
	 * @param bool   $default_value Default value.
	 */
	public function unique_post_slugs( string $post_type, bool $default_value = false ): bool;

		/**
		 * It the specified post statuses should be allowed to be used as parent post statuses.
		 *
		 * @param string $post_type Post type slug.
		 * @param bool   $default_value Default value.
		 */
	public function post_statuses_as_parent( string $post_type, bool $default_value = false ): bool;

	/**
	 * If the post type preview supposed to be opened in iframe on WP Admin side.
	 *
	 * @param string $post_type Post type slug.
	 * @param bool   $default_value Default value.
	 */
	public function in_iframe( string $post_type, bool $default_value = false ): bool;
}
