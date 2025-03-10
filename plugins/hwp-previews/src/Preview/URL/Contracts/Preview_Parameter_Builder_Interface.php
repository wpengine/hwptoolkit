<?php

declare( strict_types=1 );

namespace HWP\Previews\Preview\URL\Contracts;

use WP_Post;

interface Preview_Parameter_Builder_Interface {

	public function build_preview_args( WP_Post $post, string $token ): array;

}