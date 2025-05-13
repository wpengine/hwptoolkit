<?php

declare(strict_types=1);

namespace HWP\Previews\Post\Parent;

use HWP\Previews\Post\Parent\Contracts\Post_Parent_Manager_Interface;
use HWP\Previews\Post\Status\Contracts\Post_Statuses_Config_Interface;
use HWP\Previews\Post\Type\Contracts\Post_Types_Config_Interface;

/**
 * Class Post_Parent_Manager.
 *
 * Manages the parent post status for a given post type.
 */
class Post_Parent_Manager implements Post_Parent_Manager_Interface {

	/**
	 * All statuses that can be used as parent for a post type.
	 *
	 * @var array<string>
	 */
	public const POST_STATUSES = [ 'publish', 'future', 'draft', 'pending', 'private' ];

	/**
	 * Post types configuration.
	 *
	 * @var \HWP\Previews\Post\Type\Contracts\Post_Types_Config_Interface
	 */
	private Post_Types_Config_Interface $post_types;

	/**
	 * Post statuses configuration.
	 *
	 * @var \HWP\Previews\Post\Status\Contracts\Post_Statuses_Config_Interface
	 */
	private Post_Statuses_Config_Interface $post_statuses;

	/**
	 * Post_Parent_Manager constructor.
	 *
	 * @param \HWP\Previews\Post\Type\Contracts\Post_Types_Config_Interface      $post_types Post types configuration.
	 * @param \HWP\Previews\Post\Status\Contracts\Post_Statuses_Config_Interface $post_statuses Post statuses configuration.
	 */
	public function __construct( Post_Types_Config_Interface $post_types, Post_Statuses_Config_Interface $post_statuses ) {
		$this->post_types    = $post_types;
		$this->post_statuses = $post_statuses;
	}

	/**
	 * Get the post statuses that can be used as parent for a given post type.
	 *
	 * @param string $post_type Post Type slug.
	 *
	 * @return array<string>
	 */
	public function get_post_statuses_as_parent( string $post_type ): array {
		if (
			! $this->post_types->is_post_type_applicable( $post_type ) ||
			! $this->post_types->is_hierarchical( $post_type )
		) {
			return [];
		}

		return array_intersect( self::POST_STATUSES, $this->post_statuses->get_post_statuses() );
	}

}
