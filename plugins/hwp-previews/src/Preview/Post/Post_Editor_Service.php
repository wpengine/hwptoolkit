<?php

declare(strict_types=1);

namespace HWP\Previews\Preview\Post;

use WP_Post_Type;


/**
 * Post Editor Service class
 *
 * This class provides methods to check if the post-type supports Gutenberg editor
 *
 * @package HWP\Previews
 *
 * @since 0.0.1
 */
class Post_Editor_Service {
	/**
	 * Check if the post-type supports Gutenberg editor and if the classic editor is not being forced.
	 *
	 * @param string $post_type Post-Type slug.
	 */
	public function gutenberg_editor_enabled(string $post_type): bool {
		$post_type_object = get_post_type_object( $post_type );
		if ( ! $post_type_object instanceof WP_Post_Type ) {
			return false;
		}

		return $this->is_gutenberg_supported( $post_type_object ) &&
				! $this->is_classic_editor_forced( $post_type );
	}

	/**
	 * Checks if the post-type supports Gutenberg.
	 *
	 * @param \WP_Post_Type $post_type Post Type object.
	 */
	public function is_gutenberg_supported( WP_Post_Type $post_type ): bool {

		if ( empty( $post_type->show_in_rest ) ) {
			return false;
		}

		if ( ! post_type_supports( $post_type->name, 'editor' ) ) {
			return false;
		}

		return $post_type->show_in_rest;
	}

	/**
	 * Checks if the post-type is supported by Classic Editor.
	 */
	public function is_classic_editor_forced(string $post_type): bool {
		if (
			! function_exists( 'is_plugin_active' ) ||
			! is_plugin_active( 'classic-editor/classic-editor.php' )
		) {
			return false;
		}

		// If this post type is listed in Classic Editor settings, Gutenberg is disabled.
		$settings = (array) get_option( 'classic-editor-settings', [] );

		return ! empty( $settings['post_types'] ) &&
				is_array( $settings['post_types'] ) &&
				in_array( $post_type, $settings['post_types'], true );
	}
}
