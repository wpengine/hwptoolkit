<?php

declare( strict_types=1 );

namespace HWP\Previews\Post\Data;

use HWP\Previews\Shared\Model;

class Post_Data_Model extends Model {

	public int $ID;
	public string $post_status;
	public string $post_type;
	public string $post_name;
	public string $post_title;

	/**
	 * @param array<string, mixed> $data
	 * @param int $post_id
	 */
	public function __construct( array $data, int $post_id = 0 ) {
		$this->ID          = (int) ( $data['ID'] ?? $post_id );
		$this->post_status = (string) ( $data['post_status'] ?? '' );
		$this->post_type   = (string) ( $data['post_type'] ?? '' );
		$this->post_name   = (string) ( $data['post_name'] ?? '' );
		$this->post_title  = (string) ( $data['post_title'] ?? '' );
	}

}