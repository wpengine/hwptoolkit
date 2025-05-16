<?php

declare(strict_types=1);

namespace HWP\Previews\Preview\Parameter\Contracts;

use WP_Post;

/**
 * Interface for preview parameters.
 *
 * This interface defines the methods that all preview parameters must implement.
 */
interface Preview_Parameter_Builder_Interface {
	/**
	 * .
	 *
	 * @param \WP_Post $post The post object.
	 * @param string   $page_uri The page URI to be used for the preview.
	 * @param string   $token The token to be used for the preview.
	 *
	 * @return array<string, string>
	 */
	public function build_preview_args( WP_Post $post, string $page_uri, string $token ): array;
}
