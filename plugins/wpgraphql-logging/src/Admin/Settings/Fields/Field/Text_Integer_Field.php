<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Admin\Settings\Fields\Field;

use WPGraphQL\Logging\Admin\Settings\Fields\Field\Text_Input_Field;

/**
 * Text Integer Field class for WPGraphQL Logging settings.
 *
 * This class handles the rendering and sanitization of text input fields in the settings form.
 *
 * @package WPGraphQL\Logging
 *
 * @since 0.0.1
 */
class Text_Integer_Field extends Text_Input_Field {
	/**
	 * Sanitize the text input field value.
	 *
	 * @param mixed $value The field value to sanitize.
	 *
	 * @return string The sanitized string value.
	 */
	public function sanitize_field( $value ): string {
		$value = sanitize_text_field( (string) $value );
		return (string) intval( $value );
	}

	/**
	 * Get the input type.
	 *
	 * @return string The input type.
	 */
	protected function get_input_type(): string {
		return 'number';
	}
}
