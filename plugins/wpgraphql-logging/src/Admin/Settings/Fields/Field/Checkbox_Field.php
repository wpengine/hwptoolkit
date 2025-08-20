<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Admin\Settings\Fields\Field;

/**
 * Checkbox Field class for WPGraphQL Logging settings.
 *
 * This class handles the rendering and sanitization of checkbox input fields in the settings form.
 *
 * @package WPGraphQL\Logging
 *
 * @since 0.0.1
 */
class Checkbox_Field extends Abstract_Settings_Field {
	/**
	 * Render the checkbox field.
	 *
	 * @param array<string> $option_value The option value.
	 * @param string        $setting_key  The setting key.
	 * @param string        $tab_key      The tab key.
	 *
	 * @return string The rendered field HTML.
	 */
	public function render_field( array $option_value, string $setting_key, string $tab_key ): string {
		$field_name  = $this->get_field_name( $setting_key, $tab_key, $this->get_id() );
		$field_value = $this->get_field_value( $option_value, $tab_key, false );
		$is_checked  = is_bool( $field_value ) ? $field_value : false;

		return sprintf(
			'<input type="checkbox" name="%1$s" aria-labelledby="%1$s-tooltip" value="1" %2$s class="%3$s" />',
			esc_attr( $field_name ),
			checked( 1, $is_checked, false ),
			sanitize_html_class( $this->css_class )
		);
	}

	/**
	 * Sanitize the checkbox field value.
	 *
	 * @param mixed $value The field value to sanitize.
	 *
	 * @return bool The sanitized boolean value.
	 */
	public function sanitize_field( $value ): bool {
		return (bool) $value;
	}
}
