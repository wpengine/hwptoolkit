<?php

declare(strict_types=1);

namespace HWP\Previews\Preview\Parameter\Contracts;

use WP_Post;

/**
 * Interface for preview parameters.
 *
 * Implementations should provide a way to retrieve the name, description, and value of the parameter.
 */
interface Preview_Parameter_Interface {
	/**
	 * Get the name of the parameter.
	 * The name here doesn't represent the URL parameter name, but the name of the parameter itself.
	 */
	public function get_name(): string;

	/**
	 * Get the description of the parameter.
	 */
	public function get_description(): string;

	/**
	 * Get the value of the parameter for a given post.
	 * No need to URL-encode here.
	 *
	 * @param \WP_Post $post The post object.
	 */
	public function get_value( WP_Post $post ): string;
}
