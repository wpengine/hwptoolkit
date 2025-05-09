<?php

declare(strict_types=1);

namespace HWP\Previews\Post\Status;

use HWP\Previews\Post\Status\Contracts\Post_Statuses_Config_Interface;

/**
 * Class Post_Statuses_Config.
 */
class Post_Statuses_Config implements Post_Statuses_Config_Interface {

	/**
	 * The post statuses that are applicable for the plugin.
	 *
	 * @var array<string>
	 */
	private array $post_statuses = [];

	/**
	 * Sets the post statuses that are applicable for the plugin.
	 *
	 * @param array<string> $post_statuses Post statuses that are applicable for the plugin.
	 *
	 * @return $this
	 */
	public function set_post_statuses( array $post_statuses ): self {
		$this->post_statuses = $post_statuses;

		return $this;
	}

	/**
	 * Get the post statuses that are applicable for the plugin.
	 *
	 * @return array<string> Post statuses.
	 */
	public function get_post_statuses(): array {
		return $this->post_statuses;
	}

	/**
	 * TODO: add post status verification to support custom post types in future. Or anything else.
	 *
	 * @param string $post_status Post status to check.
	 *
	 * @return bool
	 */
	public function is_post_status_applicable( string $post_status ): bool {
		return in_array( $post_status, $this->post_statuses, true );
	}

}
