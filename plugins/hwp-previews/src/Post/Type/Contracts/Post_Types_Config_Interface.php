<?php

declare( strict_types=1 );

namespace HWP\Previews\Post\Type\Contracts;

use WP_Post_Type;

interface Post_Types_Config_Interface {

	public function get_post_types(): array;

	public function is_post_type_applicable( string $post_type ): bool;

	public function is_hierarchical( WP_Post_Type $post_type ): bool;

	public function supports_gutenberg( WP_Post_Type $post_type ): bool;

}
