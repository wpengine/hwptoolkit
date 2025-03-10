<?php

declare( strict_types=1 );

namespace HWP\Previews\Post\Slug;

use HWP\Previews\Post\Slug\Contracts\Post_Slug_Manager_Interface;
use HWP\Previews\Post\Slug\Contracts\Post_Slug_Repository_Interface;
use HWP\Previews\Post\Status\Contracts\Post_Statuses_Config_Interface;
use HWP\Previews\Post\Type\Contracts\Post_Types_Config_Interface;
use WP_Post;
use wp_rewrite;

class Post_Slug_Manager implements Post_Slug_Manager_Interface {

	private Post_Types_Config_Interface $types;
	private Post_Statuses_Config_Interface $statuses;
	private Post_Slug_Repository_Interface $slug_repository;


	public function __construct(
		Post_Types_Config_Interface $types,
		Post_Statuses_Config_Interface $statuses,
		Post_Slug_Repository_Interface $slug_repository
	) {
		$this->types           = $types;
		$this->statuses        = $statuses;
		$this->slug_repository = $slug_repository;
	}

	public function force_unique_post_slug( WP_Post $post, wp_rewrite $wp_rewrite ): string {
		if (
			! $post->ID ||
			! $this->types->is_post_type_applicable( $post->post_type ) ||
			! $this->statuses->is_post_status_applicable( $post->post_status )
		) {
			return '';
		}

		$slug  = $post->post_name ?: sanitize_title( $post->post_title, "$post->post_status-$post->ID" );
		$feeds = is_array( $wp_rewrite->feeds ) ? $wp_rewrite->feeds : [];

		return $this->generate_unique_slug( $slug, $post->post_type, $post->ID, array_merge( $feeds, [ 'embed' ] ) );
	}

	/**
	 * From WordPress core: wp-includes/post.php
	 *
	 * @param string $slug
	 * @param string $post_type
	 * @param int $post_id
	 * @param array $reserved_slugs
	 *
	 * @return string
	 */
	public function generate_unique_slug( string $slug, string $post_type, int $post_id, array $reserved_slugs ): string {
		$slug = $slug ?: 'undefined';

		if ( ! $this->slug_repository->is_slug_taken( $slug, $post_type, $post_id ) && ! in_array( $slug, $reserved_slugs, true ) ) {
			return $slug;
		}

		$suffix = 2;
		do {
			$new_slug = _truncate_post_slug( $slug, 200 - ( strlen( (string) $suffix ) + 1 ) ) . "-$suffix";
			$suffix ++;
		} while ( $this->slug_repository->is_slug_taken( $new_slug, $post_type, $post_id ) );

		return $new_slug;
	}

}