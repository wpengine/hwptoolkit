<?php

declare(strict_types=1);

namespace HWP\Previews\Post\Type;

use HWP\Previews\Post\Type\Contracts\Post_Types_Config_Interface;
use WP_Post_Type;

/**
 * Class Post_Types_Config.
 */
class Post_Types_Config implements Post_Types_Config_Interface {

	/**
	 * Post types that are applicable for preview links.
	 *
	 * @var array<string>
	 */
	private array $post_types = [];

	/**
	 * Sets the post types that are applicable for preview links.
	 *
	 * @param array<string> $post_types Post types that are applicable for preview links.
	 *
	 * @return $this
	 */
	public function set_post_types( array $post_types ): self {
		$this->post_types = $post_types;

		return $this;
	}

	/**
	 * Get the post types that are applicable for preview links.
	 *
	 * @return array<string>
	 */
	public function get_post_types(): array {
		return $this->post_types;
	}

	/**
	 * Check if the post type is applicable for preview links.
	 *
	 * @param string $post_type Post type to check.
	 *
	 * @return bool
	 */
	public function is_post_type_applicable( string $post_type ): bool {
		return in_array( $post_type, $this->post_types, true ) && post_type_exists( $post_type );
	}

	/**
	 * Check if the post type is hierarchical.
	 *
	 * @param \WP_Post_Type $post_type Post type object.
	 *
	 * @return bool
	 */
	public function is_hierarchical( WP_Post_Type $post_type ): bool {
		return $post_type->hierarchical;
	}

	/**
	 * Check if the post type supports Gutenberg editor.
	 *
	 * @param \WP_Post_Type $post_type Post type object.
	 *
	 * @return bool
	 */
	public function supports_gutenberg( WP_Post_Type $post_type ): bool {
		if (
			empty( $post_type->show_in_rest ) ||
			empty( $post_type->supports ) ||
			! is_array( $post_type->supports ) ||
			! in_array( 'editor', $post_type->supports, true )
		) {
			return false;
		}

		if ( ! is_plugin_active( 'classic-editor/classic-editor.php' ) ) {
			return true;
		}

		$classic_editor_settings = (array) get_option( 'classic-editor-settings', [] );

		return ! (
			! empty( $classic_editor_settings['post_types'] ) &&
			is_array( $classic_editor_settings['post_types'] ) &&
			in_array( $post_type->name, $classic_editor_settings['post_types'], true )
		);
	}

	/**
	 * Gets all publicly available post types as key value array, where key is a post type slug and value is a label.
	 *
	 * @return array<string, string>
	 */
	public function get_public_post_types(): array {
		$post_types = get_post_types( [ 'public' => true ], 'objects' );
		$result     = [];

		foreach ( $post_types as $post_type ) {
			$result[ $post_type->name ] = $post_type->label;
		}

		return $result;
	}

}
