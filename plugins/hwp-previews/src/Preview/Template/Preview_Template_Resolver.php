<?php

declare(strict_types=1);

namespace HWP\Previews\Preview\Template;

use HWP\Previews\Post\Status\Contracts\Post_Statuses_Config_Interface;
use HWP\Previews\Post\Type\Contracts\Post_Types_Config_Interface;
use HWP\Previews\Preview\Template\Contracts\Preview_Template_Resolver_Interface;
use WP_Post;

class Preview_Template_Resolver implements Preview_Template_Resolver_Interface {
	/**
	 * .
	 *
	 * @var string
	 */
	public const HWP_PREVIEWS_IFRAME_PREVIEW_URL = 'hwp_previews_iframe_preview_url';

	/**
	 * .
	 *
	 * @var \HWP\Previews\Post\Type\Contracts\Post_Types_Config_Interface
	 */
	private Post_Types_Config_Interface $types;

	/**
	 * .
	 *
	 * @var \HWP\Previews\Post\Status\Contracts\Post_Statuses_Config_Interface
	 */
	private Post_Statuses_Config_Interface $statuses;

	/**
	 * .
	 *
	 * @param \HWP\Previews\Post\Type\Contracts\Post_Types_Config_Interface      $types .
	 * @param \HWP\Previews\Post\Status\Contracts\Post_Statuses_Config_Interface $statuses .
	 */
	public function __construct( Post_Types_Config_Interface $types, Post_Statuses_Config_Interface $statuses ) {
		$this->types    = $types;
		$this->statuses = $statuses;
	}

	/**
	 * Resolves the template path for the preview.
	 *
	 * @param \WP_Post $post The post object.
	 * @param string   $template_path The template path.
	 *
	 * @return string The resolved template path.
	 */
	public function resolve_template_path( WP_Post $post, string $template_path ): string {
		if (
			empty( $template_path ) ||
			! $this->types->is_post_type_applicable( $post->post_type ) ||
			! $this->statuses->is_post_status_applicable( $post->post_status ) ||
			! is_preview()
		) {
			return '';
		}

		return file_exists( $template_path ) ? $template_path : '';
	}
}
