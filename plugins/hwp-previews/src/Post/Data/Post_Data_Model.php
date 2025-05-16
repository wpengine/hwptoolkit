<?php

declare(strict_types=1);

namespace HWP\Previews\Post\Data;

use HWP\Previews\Shared\Abstract_Model;

/**
 * Class Post_Data_Model.
 */
final class Post_Data_Model extends Abstract_Model {
	/**
	 * Post ID.
	 *
	 * @var int
	 */
	public int $ID;

	/**
	 * Post status.
	 *
	 * @var string
	 */
	public string $post_status;

	/**
	 * Post type.
	 *
	 * @var string
	 */
	public string $post_type;

	/**
	 * Post slug.
	 *
	 * @var string
	 */
	public string $post_name;

	/**
	 * Post title.
	 *
	 * @var string
	 */
	public string $post_title;

	/**
	 * Constructor where hydration happens according to the mapping.
	 *
	 * @param array<array-key, mixed> $data Array of data to hydrate the model.
	 * @param int                     $post_id Post ID.
	 */
	public function __construct( array $data, int $post_id = 0 ) {
		$this->ID          = (int) ( $data['ID'] ?? $post_id );
		$this->post_status = (string) ( $data['post_status'] ?? '' );
		$this->post_type   = (string) ( $data['post_type'] ?? '' );
		$this->post_name   = (string) ( $data['post_name'] ?? '' );
		$this->post_title  = (string) ( $data['post_title'] ?? '' );
	}
}
