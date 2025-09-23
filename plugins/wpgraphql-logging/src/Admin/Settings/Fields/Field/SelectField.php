<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Admin\Settings\Fields\Field;

/**
 * Select Field class for WPGraphQL Logging settings.
 *
 * This class handles the rendering and sanitization of select dropdown fields in the settings form.
 *
 * @package WPGraphQL\Logging
 *
 * @since 0.0.1
 */
class SelectField extends AbstractSettingsField {
	/**
	 * Constructor.
	 *
	 * @param string                    $id           The settings field ID.
	 * @param string                    $tab          The tab this field should be shown in.
	 * @param string                    $title        The settings field title.
	 * @param array<string|int, string> $options      The options for the select field.
	 * @param string                    $css_class    The settings field class.
	 * @param string                    $description  The description field to show in the tooltip.
	 * @param bool                      $multiple     Whether multiple selections are allowed.
	 *
	 * @phpstan-ignore-next-line constructor.missingParentCall
	 */
	public function __construct(
		readonly string $id,
		readonly string $tab,
		readonly string $title,
		readonly array $options,
		readonly string $css_class = '',
		readonly string $description = '',
		readonly bool $multiple = false
	) {
	}

	/**
	 * Render the select field.
	 *
	 * @param array<string> $option_value The option value.
	 * @param string        $setting_key  The setting key.
	 * @param string        $tab_key      The tab key.
	 *
	 * @return string The rendered field HTML.
	 */
	public function render_field( array $option_value, string $setting_key, string $tab_key ): string {
		$field_name  = $this->get_field_name( $setting_key, $tab_key, $this->get_id() );
		$field_value = $this->get_field_value( $option_value, $tab_key, $this->multiple ? [] : '' );

		// Ensure we have the correct format for comparison.
		$selected_values = $this->multiple ? (array) $field_value : [ (string) $field_value ];

		$html  = '<select ';
		$html .= 'name="' . esc_attr( $field_name ) . ( $this->multiple ? '[]' : '' ) . '" ';
		$html .= 'id="' . esc_attr( $this->get_id() ) . '" ';
		$html .= 'class="' . esc_attr( $this->css_class ) . '" ';

		if ( $this->multiple ) {
			$html .= 'multiple="multiple" ';
		}

		$html .= '>';

		foreach ( $this->options as $value => $label ) {
			$is_selected = in_array( (string) $value, $selected_values, true );
			$html       .= '<option value="' . esc_attr( (string) $value ) . '" ';
			$html       .= selected( $is_selected, true, false ) . '>';
			$html       .= esc_html( $label );
			$html       .= '</option>';
		}

		$html .= '</select>';

		return $html;
	}

	/**
	 * Sanitize the select field value.
	 *
	 * @param mixed $value The field value to sanitize.
	 *
	 * @return mixed The sanitized value.
	 */
	public function sanitize_field( $value ): mixed {
		if ( ! $this->multiple ) {
			return $this->sanitize_single_value( $value );
		}

		return $this->sanitize_multiple_value( (array) $value );
	}

	/**
	 * Sanitize a single value.
	 *
	 * @param string $value The value to sanitize.
	 *
	 * @return string The sanitized value.
	 */
	protected function sanitize_single_value( $value ): string {
		$sanitized_value = sanitize_text_field( (string) $value );
		return array_key_exists( $sanitized_value, $this->options ) ? $sanitized_value : '';
	}

	/**
	 * Sanitize a multiple value.
	 *
	 * @param array<string> $values The values to sanitize.
	 *
	 * @return array<string> The sanitized values.
	 */
	protected function sanitize_multiple_value( array $values ): array {
		$sanitized = [];
		foreach ( $values as $value ) {
			$single = $this->sanitize_single_value( $value );
			if ( '' !== $single ) {
				$sanitized[] = $single;
			}
		}
		return $sanitized;
	}
}
