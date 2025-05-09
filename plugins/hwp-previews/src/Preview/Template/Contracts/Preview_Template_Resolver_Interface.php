<?php

declare(strict_types=1);

namespace HWP\Previews\Preview\Template\Contracts;

use WP_Post;

interface Preview_Template_Resolver_Interface {

	/**
	 * Resolve the template path for a given post. Returns the path to the template file.
	 *
	 * @param \WP_Post $post Post object.
	 * @param string   $template_path Path to the template.
	 *
	 * @return string
	 */
	public function resolve_template_path( WP_Post $post, string $template_path ): string;

}
