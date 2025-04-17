<?php

declare( strict_types=1 );

namespace HWP\Previews\Preview\Parameters\Contracts;

use WP_Post;

interface Preview_Parameter_Builder_Interface {

	public function build_preview_args( WP_Post $post, string $page_uri, string $token ): array;

}