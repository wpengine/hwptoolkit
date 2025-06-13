<?php

declare(strict_types=1);

namespace HWP\Previews\Post\Type;

use HWP\Previews\Post\Type\Contracts\Post_Type_Inspector_Interface;
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
	 * Post type inspector.
	 *
	 * @var \HWP\Previews\Post\Type\Contracts\Post_Type_Inspector_Interface
	 */
	private Post_Type_Inspector_Interface $inspector;

	/**
	 * Class constructor.
	 *
	 * @param \HWP\Previews\Post\Type\Contracts\Post_Type_Inspector_Interface $inspector Post Type inspector.
	 */
	public function __construct( Post_Type_Inspector_Interface $inspector ) {
		$this->inspector = $inspector;
	}

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
	 * @param string $post_type Post Type slug.
	 */
	public function is_post_type_applicable( string $post_type ): bool {
		return in_array( $post_type, $this->post_types, true ) && post_type_exists( $post_type );
	}

	/**
	 * Check if the post type is hierarchical.
	 *
	 * @param string $post_type Post Type slug.
	 */
	public function is_hierarchical( string $post_type ): bool {
		return is_post_type_hierarchical( $post_type );
	}

	/**
	 * Check if the post type supports Gutenberg editor and if the classic editor is not being forced.
	 *
	 * @param string $post_type Post Type slug.
	 */
	public function gutenberg_editor_enabled( string $post_type ): bool {
		$post_type_object = get_post_type_object( $post_type );
		if ( ! $post_type_object instanceof WP_Post_Type ) {
			return false;
		}

		return $this->inspector->is_gutenberg_supported( $post_type_object ) &&
				! $this->inspector->is_classic_editor_forced( $post_type );
	}

	/**
	 * Gets all publicly available post types as key value array, where key is a post type slug and value is a label.
	 *
	 * @return array<string, string>
	 */
	public function get_public_post_types(): array {
		$post_types = get_post_types( [ 'public' => true ], 'objects' );

		$result = [];

		foreach ( $post_types as $post_type ) {
			$result[ $post_type->name ] = $post_type->label;
		}

		return apply_filters( 'hwp_previews_filter_available_post_types', $result );
	}
}
