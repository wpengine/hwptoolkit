<?php

declare(strict_types=1);

namespace HWP\Previews\Preview\Link;

use HWP\Previews\Post\Status\Contracts\Post_Statuses_Config_Interface;
use HWP\Previews\Post\Type\Contracts\Post_Types_Config_Interface;
use WP_Post;

/**
 * Doc Comment.
 */
class Preview_Link_Service {
	/**
	 * Post types config.
	 *
	 * @var \HWP\Previews\Post\Type\Contracts\Post_Types_Config_Interface
	 */
	private Post_Types_Config_Interface $types;

	/**
	 * Post statuses config.
	 *
	 * @var \HWP\Previews\Post\Status\Contracts\Post_Statuses_Config_Interface
	 */
	private Post_Statuses_Config_Interface $statuses;

	/**
	 * Preview link resolver.
	 *
	 * @var \HWP\Previews\Preview\Link\Preview_Link_Placeholder_Resolver
	 */
	private Preview_Link_Placeholder_Resolver $resolver;

	/**
	 * Constructor.
	 *
	 * @param \HWP\Previews\Post\Type\Contracts\Post_Types_Config_Interface      $types Post types config.
	 * @param \HWP\Previews\Post\Status\Contracts\Post_Statuses_Config_Interface $statuses Post statuses config.
	 * @param \HWP\Previews\Preview\Link\Preview_Link_Placeholder_Resolver       $resolver Preview link resolver.
	 */
	public function __construct(
		Post_Types_Config_Interface $types,
		Post_Statuses_Config_Interface $statuses,
		Preview_Link_Placeholder_Resolver $resolver
	) {
		$this->types    = $types;
		$this->statuses = $statuses;
		$this->resolver = $resolver;
	}

	/**
	 * Generate a preview post link.
	 *
	 * @param string   $preview_url_template Preview URL template.
	 * @param \WP_Post $post The post object.
	 */
	public function generate_preview_post_link( string $preview_url_template, WP_Post $post ): string {
		if (
			empty( $preview_url_template ) ||
			! $this->types->is_post_type_applicable( $post->post_type ) ||
			! $this->statuses->is_post_status_applicable( $post->post_status )
		) {
			return '';
		}

		return $this->resolver->resolve_placeholders( $preview_url_template, $post );
	}
}
