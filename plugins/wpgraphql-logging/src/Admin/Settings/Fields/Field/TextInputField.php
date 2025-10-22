<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Admin\Settings\Fields\Field;

/**
 * Text Input Field class for WPGraphQL Logging settings.
 *
 * This class handles the rendering and sanitization of text input fields in the settings form.
 *
 * @package WPGraphQL\Logging
 *
 * @since 0.0.1
 */
class TextInputField extends AbstractSettingsField {
	/**
	 * Constructor.
	 *
	 * @param string $id             The settings field ID.
	 * @param string $tab            The tab this field should be shown in.
	 * @param string $title          The settings field title.
	 * @param string $css_class      The settings field class.
	 * @param string $description    The description field to show in the tooltip.
	 * @param string $placeholder    The placeholder text.
	 * @param string $default_value  The default value.
	 *
	 * @phpstan-ignore-next-line constructor.missingParentCall
	 */
	public function __construct(
		readonly string $id,
		readonly string $tab,
		readonly string $title,
		readonly string $css_class = '',
		readonly string $description = '',
		readonly string $placeholder = '',
		readonly string $default_value = ''
	) {
	}

	/**
	 * Render the text input field.
	 *
	 * @param array<string, mixed> $option_value The option value.
	 * @param string               $setting_key  The setting key.
	 * @param string               $tab_key      The tab key.
	 *
	 * @return string The rendered field HTML.
	 */
	public function render_field( array $option_value, string $setting_key, string $tab_key ): string {
		$field_name  = $this->get_field_name( $setting_key, $tab_key, $this->get_id() );
		$field_value = $this->get_field_value( $option_value, $tab_key, $this->default_value );


		return sprintf(
			'<input type="%1$s" name="%2$s" aria-labelledby="%2$s-tooltip" value="%3$s" placeholder="%4$s" class="%5$s" />',
			esc_attr( $this->get_input_type() ),
			esc_attr( $field_name ),
			esc_attr( $field_value ),
			esc_attr( $this->placeholder ),
			esc_attr( $this->css_class )
		);
	}

	/**
	 * Sanitize the text input field value.
	 *
	 * @param mixed $value The field value to sanitize.
	 *
	 * @return string The sanitized string value.
	 */
	public function sanitize_field( $value ): string {
		return sanitize_text_field( (string) $value );
	}

	/**
	 * Get the input type.
	 *
	 * @return string The input type.
	 */
	protected function get_input_type(): string {
		return 'text';
	}
}
