<?php

declare(strict_types=1);

namespace HWP\Previews\Preview\Service;

class Template_Resolver_Service {
	/**
	 * The query variable used to pass the preview URL to the iframe template.
	 *
	 * @var string
	 */
	public const HWP_PREVIEWS_IFRAME_PREVIEW_URL = 'hwp_previews_iframe_preview_url';

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
