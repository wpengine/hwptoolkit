<?php

declare(strict_types=1);

namespace HWP\Previews\Preview\Template\Contracts;

use WP_Post;

interface Preview_Template_Resolver_Interface {

	public function resolve_template_path( WP_Post $post, string $template_dir_path, bool $per_post_type = false ): string;

}