<?php
declare(strict_types=1);

namespace HWP\Previews\Post\Status;

use HWP\Previews\Post\Status\Contracts\Post_Statuses_Config_Interface;

class Post_Statuses_Config implements Post_Statuses_Config_Interface {

	/**
	 * @var string[]
	 */
	private array $post_statuses;

	public function __construct( array $post_statuses ) {
		$this->post_statuses = $post_statuses;
	}

	public function get_post_statuses(): array {
		return $this->post_statuses;
	}

	/**
	 * TODO: add post status verification to support custom post types in future. Or anything else.
	 *
	 * @param string $post_status
	 *
	 * @return bool
	 */
	public function is_post_status_applicable( string $post_status ): bool {
		return in_array( $post_status, $this->post_statuses, true );
	}

}