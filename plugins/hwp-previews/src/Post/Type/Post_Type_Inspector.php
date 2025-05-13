<?php

declare(strict_types=1);

namespace HWP\Previews\Post\Type;

use HWP\Previews\Post\Type\Contracts\Post_Type_Inspector_Interface;
use WP_Post_Type;

class Post_Type_Inspector implements Post_Type_Inspector_Interface {

	/**
	 * Checks if the post type supports Gutenberg.
	 *
	 * @param \WP_Post_Type $post_type Post Type object.
	 *
	 * @return bool
	 */
	public function is_gutenberg_supported( WP_Post_Type $post_type ): bool {
		if (
			empty( $post_type->show_in_rest ) ||
			empty( $post_type->supports ) ||
			! is_array( $post_type->supports ) ||
			! in_array( 'editor', $post_type->supports, true )
		) {
			return false;
		}

		return $post_type->show_in_rest;
	}

	/**
	 * Checks if the post type is supported by Classic Editor.
	 *
	 * @param string $post_type Post Type slug.
	 *
	 * @return bool
	 */
	public function is_classic_editor_forced( string $post_type ): bool {
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
