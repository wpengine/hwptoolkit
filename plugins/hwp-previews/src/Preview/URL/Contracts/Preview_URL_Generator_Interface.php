<?php

declare( strict_types=1 );

namespace HWP\Previews\Preview\URL\Contracts;

use WP_Post;

interface Preview_URL_Generator_Interface {

	public function generate_url( WP_Post $post, string $frontend_url, string $page_uri, array $args, string $draft_route = '' ): string;

}