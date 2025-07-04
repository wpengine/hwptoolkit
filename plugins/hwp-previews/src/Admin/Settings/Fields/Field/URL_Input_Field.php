<?php

declare(strict_types=1);

namespace HWP\Previews\Admin\Settings\Fields\Field;

/**
 * URL input class
 *
 * This class represents a url input field in the settings of the HWP Previews plugin.
 *
 * @package HWP\Previews
 *
 * @since 0.0.1
 */
class URL_Input_Field extends Text_Input_Field {
	/**
	 * @param mixed $value
	 */
	public function sanitize_field( $value ): string {
		$value = sanitize_text_field( (string) $value );
		if ( '' === $value ) {
			return '';
		}

		// Validate the URL format.
		if ( false !== wp_http_validate_url( $value ) ) {
			return $value;
		}

		// Clean and fix the URL and allow curly braces for parameter replacement.
		$value = $this->fix_url( $value );

		// Sanitize while preserving placeholders.
		$value = str_replace( [ '{', '}' ], [ '___OPEN___', '___CLOSE___' ], $value );
		$value = esc_url_raw( $value );
		return str_replace( [ '___OPEN___', '___CLOSE___' ], [ '{', '}' ], $value );
	}

	/**
	 * URL input field constructor.
	 *
	 * Note: We did not change escape_render_input_value method to esc_url as this would remove any brackets from the URL.
	 */
	public function get_input_type(): string {
		return 'url';
	}

	/**
	 * Fixes the URL by removing HTML tags, trimming whitespace, encoding spaces, and adding a protocol if missing.
	 *
	 * @param string $value
	 */
	private function fix_url( string $value ): string {


		if ( '' === $value ) {
			return '';
		}

		// Remove <script> tags and their content to prevent XSS.
		$value = preg_replace( '#<script.*?>.*?</script>#is', '', $value );

		if ( ! is_string( $value ) || '' === trim( $value ) ) {
			return '';
		}

		// Remove HTML tags except curly braces, trim, encode spaces, add protocol.
		$value = preg_replace( '/<(?!\{)[^>]+>/', '', $value );
		$value = trim( str_replace( ' ', '%20', (string) $value ) );

		if ( '' === $value ) {
			return '';
		}

		$has_protocol = preg_match( '/^https?:\/\//i', $value ) === 1;
		if ( $has_protocol ) {
			return $value;
		}
		$protocol = is_ssl() ? 'https://' : 'http://';
		return $protocol . ltrim( $value, '/' );
	}
}
