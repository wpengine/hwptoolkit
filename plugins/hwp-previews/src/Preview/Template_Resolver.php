<?php

declare(strict_types=1);

namespace HWP\Previews\Preview;

use WP_Post;

class Template_Resolver {
	/**
	 * The query variable used to pass the preview URL to the iframe template.
	 *
	 * @var string
	 */
	public const HWP_PREVIEWS_IFRAME_PREVIEW_URL = 'hwp_previews_iframe_preview_url';

	/**
	 * The current post object.
	 *
	 * @var \WP_Post
	 */
	protected WP_Post $post;

	/**
	 * Allowed post types for preview links.
	 *
	 * @var array<string>
	 */
	private array $post_types = [];

	/**
	 * Allowed post statuses for preview links.
	 *
	 * @var array<string>
	 */
	private array $post_statuses = [];

	/**
	 * Constructor.
	 *
	 * @param \WP_Post      $post The post object.
	 * @param array<string> $post_types Post types that are applicable for preview links.
	 * @param array<string> $post_statuses Post statuses that are applicable for preview links.
	 */
	public function __construct( WP_Post $post, array $post_types = [], array $post_statuses = [] ) {
		$this->post          = $post;
		$this->post_types    = $post_types;
		$this->post_statuses = $post_statuses;
	}

	/**
	 * Check if the current post is allowed for preview in an iframe.
	 *
	 * @return bool True if the post is allowed, false otherwise.
	 */
	public function is_allowed(): bool {

		$post_type  = $this->post->post_type;
		$post_types = $this->post_types;
		if ( ! in_array( $post_type, $post_types, true ) ) {
			return false;
		}

		$post_status   = $this->post->post_status;
		$post_statuses = $this->post_statuses;

		return in_array( $post_status, $post_statuses, true );
	}

	/**
	 * Get the path to the iframe template.
	 *
	 * @return string|null The path to the iframe template, or null if it does not exist.
	 */
	public function get_iframe_template(): ?string {

		/**
		 * The filter 'hwp_previews_template_path' allows changing the template file path.
		 */
		$template_dir_path = (string) apply_filters(
			'hwp_previews_template_path',
			trailingslashit( HWP_PREVIEWS_PLUGIN_DIR ) . 'src/Templates/iframe.php',
		);

		if ( ! file_exists( $template_dir_path ) ) {
			return '';
		}

		return $template_dir_path;
	}

	/**
	 * Set the query variable that contains the preview URL for the iframe.
	 *
	 * @param string $template_url
	 */
	public function set_query_variable( string $template_url ): void {
		set_query_var( self::HWP_PREVIEWS_IFRAME_PREVIEW_URL, $template_url );
	}

	/**
	 * Get the query variable that contains the preview URL for the iframe.
	 */
	public static function get_query_variable(): string {
		return (string) get_query_var( self::HWP_PREVIEWS_IFRAME_PREVIEW_URL );
	}
}
