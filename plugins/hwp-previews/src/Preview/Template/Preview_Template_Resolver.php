<?php

declare( strict_types=1 );

namespace HWP\Previews\Preview\Template;

use HWP\Previews\Post\Status\Contracts\Post_Statuses_Config_Interface;
use HWP\Previews\Post\Type\Contracts\Post_Types_Config_Interface;
use HWP\Previews\Preview\Template\Contracts\Preview_Template_Resolver_Interface;
use WP_Post;

class Preview_Template_Resolver implements Preview_Template_Resolver_Interface {

	public const HWP_PREVIEWS_IFRAME_PREVIEW_URL = 'hwp_previews_iframe_preview_url';

	private Post_Types_Config_Interface $types;
	private Post_Statuses_Config_Interface $statuses;

	public function __construct( Post_Types_Config_Interface $types, Post_Statuses_Config_Interface $statuses ) {
		$this->types    = $types;
		$this->statuses = $statuses;
	}

	public function resolve_template_path( WP_Post $post, string $template_dir_path, bool $per_post_type = false ): string {
		if (
			! $template_dir_path ||
			! $this->types->is_post_type_applicable( $post->post_type ) ||
			! $this->statuses->is_post_status_applicable( $post->post_status ) ||
			! is_preview()
		) {
			return '';
		}

		$template      = $template_dir_path . '/hwp-preview.php';
		$template_type = $template_dir_path . '/hwp-preview-' . $post->post_type . '.php';

		if ( $per_post_type && file_exists( $template_type ) ) {
			return $template_type;
		}

		return file_exists( $template ) ? $template : '';
	}
}