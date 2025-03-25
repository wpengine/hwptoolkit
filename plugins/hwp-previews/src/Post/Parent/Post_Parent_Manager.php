<?php

declare( strict_types=1 );

namespace HWP\Previews\Post\Parent;

use HWP\Previews\Post\Parent\Contracts\Post_Parent_Manager_Interface;
use HWP\Previews\Post\Status\Contracts\Post_Statuses_Config_Interface;
use HWP\Previews\Post\Type\Contracts\Post_Types_Config_Interface;
use WP_Post_Type;

class Post_Parent_Manager implements Post_Parent_Manager_Interface {

	public const POST_STATUSES = [ 'publish', 'future', 'draft', 'pending', 'private' ];

	private Post_Types_Config_Interface $post_types;
	private Post_Statuses_Config_Interface $post_statuses;

	public function __construct( Post_Types_Config_Interface $post_types, Post_Statuses_Config_Interface $post_statuses ) {
		$this->post_types    = $post_types;
		$this->post_statuses = $post_statuses;
	}

	public function get_post_statuses_as_parent( WP_Post_Type $post_type ): array {
		if (
			! $this->post_types->is_post_type_applicable( $post_type->name ) ||
			! $this->post_types->is_hierarchical( $post_type )
		) {
			return [];
		}

		return array_intersect( self::POST_STATUSES, $this->post_statuses->get_post_statuses() );
	}

}