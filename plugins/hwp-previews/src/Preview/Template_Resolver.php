<?php

declare(strict_types=1);

namespace HWP\Previews\Preview;

use HWP\Previews\Preview\Helper\Settings_Helper;
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
	 * @var \WP_Post|null
	 */
	protected ?WP_Post $post = null;

	public function __construct() {
		$post = get_post();
		if ( $post instanceof WP_Post ) {
			$this->post = $post;
		}
	}

	public function is_allowed(): bool {

		if ( ! is_preview() ) {
			return false;
		}

		if ( ! $this->post instanceof WP_Post ) {
			return false;
		}

		// @TODO check
		// if (
		// empty( $template_path ) ||
		// ! $this->types->is_post_type_applicable( $post->post_type ) ||
		// ! $this->statuses->is_post_status_applicable( $post->post_status ) ||
		// ! is_preview()
		// ) {
		// return '';
		// }

		$settings_helper = Settings_Helper::get_instance();
		if ( ! $settings_helper->in_iframe( $this->post->post_type ) ) {
			return false;
		}


		return is_preview();
	}

	public function get_post(): ?WP_Post {
		return $this->post;
	}

	public function get_iframe_template(): string {

		/**
		 * The filter 'hwp_previews_template_path' allows to change the template directory path.
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
