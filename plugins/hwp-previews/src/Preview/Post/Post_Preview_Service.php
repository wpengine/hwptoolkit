<?php

declare(strict_types=1);

namespace HWP\Previews\Preview\Post;

class Post_Preview_Service {
	/**
	 * The allowed post-types for previews.
	 *
	 * @var array<string>
	 */
	protected $post_types = [];

	/**
	 * The allowed post-statuses for previews.
	 *
	 * @var array<string>
	 */
	protected $post_statuses = [];

	/**
	 * The allowed post-statuses for parent post-types (hierarchical).
	 *
	 * @var array<string>
	 */
	protected $parent_post_statuses = [];

	/**
	 * Constructor for the Post_Preview_Service class.
	 *
	 * Initializes the allowed post-types and statuses for previews.
	 */
	public function __construct() {
		$this->set_post_types();
		$this->set_post_statuses();
		$this->set_post_parent_statuses();
	}

	/**
	 * @return array<string>
	 */
	public function get_allowed_post_types(): array {
		return $this->post_types;
	}

	/**
	 * Get the post statuses.
	 *
	 * @return array<string>
	 */
	public function get_post_statuses(): array {
		return $this->post_statuses;
	}

	/**
	 * Get the post types.
	 *
	 * @return array<string>
	 */
	public function get_post_types(): array {
		return $this->post_types;
	}

	/**
	 * Get the parent post statuses
	 *
	 * @return array<string>
	 */
	public function get_parent_post_statuses(): array {
		return $this->parent_post_statuses;
	}

	/**
	 * Sets the allowed post types for previews.
	 */
	protected function set_post_types(): void {
		$post_types = get_post_types( [ 'public' => true ], 'objects' );

		$result = [];

		foreach ( $post_types as $post_type ) {
			$result[ $post_type->name ] = $post_type->label;
		}

		$this->post_types = apply_filters( 'hwp_previews_filter_available_post_types', $result );
	}

	/**
	 * Sets the allowed post statuses for previews.
	 */
	protected function set_post_statuses(): void {

		$post_statuses = [
			'publish',
			'future',
			'draft',
			'pending',
			'private',
			'auto-draft',
		];

		$this->post_statuses = apply_filters( 'hwp_previews_filter_available_post_statuses', $post_statuses );
	}

	/**
	 * Sets the allowed post statuses for parent post-types (hierarchael).
	 */
	protected function set_post_parent_statuses(): void {

		$post_statuses = [
			'publish',
			'future',
			'draft',
			'pending',
			'private',
		];

		$this->parent_post_statuses = apply_filters( 'hwp_previews_filter_available_parent_post_statuses', $post_statuses );
	}
}
