<?php

declare(strict_types=1);

namespace HWP\Previews\Post\Status\Contracts;

interface Post_Statuses_Config_Interface {
	/**
	 * Set the post statuses that are applicable for the plugin.
	 *
	 * @param array<string> $post_statuses The post statuses to set.
	 */
	public function set_post_statuses( array $post_statuses ): self;

	/**
	 * Get the post statuses that are applicable for the plugin.
	 *
	 * @return array<string>
	 */
	public function get_post_statuses(): array;

	/**
	 * Check if a given post status is applicable for the plugin.
	 *
	 * @param string $post_status Post status slug.
	 */
	public function is_post_status_applicable( string $post_status ): bool;
}
