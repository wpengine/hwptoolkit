<?php

declare(strict_types=1);

namespace HWP\Previews\Admin\Settings\Fields\Field;

/**
 * Text input class
 *
 * This class represents a text input field in the settings of the HWP Previews plugin.
 *
 * @package HWP\Previews
 *
 * @since 0.0.1
 */
class Text_Input_Field extends Abstract_Settings_Field {
	/**
	 * The default value for the field.
	 *
	 * @var string
	 */
	protected string $default;

	/**
	 * Constructor.
	 *
	 * @param string $id The settings field ID.
	 * @param bool   $is_hierarchical Whether the field is hierarchical.
	 * @param string $title The settings field title.
	 * @param string $description The settings field description.
	 * @param string $default_value The default value for the field.
	 * @param string $css_class The settings field class.
	 */
	public function __construct( string $id, bool $is_hierarchical, string $title, string $description = '', string $default_value = '', string $css_class = '' ) {
		parent::__construct( $id, $is_hierarchical, $title, $css_class, $description );

		$this->default = $default_value;
	}

	/**
	 * Render the field.
	 *
	 * @param array<string> $option_value The value of the field.
	 * @param string        $setting_key The settings key.
	 * @param string        $post_type The post type.
	 */
	public function render_field( array $option_value, string $setting_key, string $post_type ): string {

		$default_value = $this->default;
		if ( ! empty( $default_value ) && str_contains( $default_value, '%s' ) ) {
			$default_value = sprintf( $default_value, $post_type );
		}

		// Get option value for the current post type.
		$option_escaped_value = null;
		if ( array_key_exists( $this->get_id(), $option_value ) ) {
			$option_escaped_value = $option_value[ $this->get_id() ];
			if ( str_contains( $option_escaped_value, '%s' ) ) {
				$option_escaped_value = sprintf( $option_escaped_value, $post_type );
			}
		}


		return sprintf(
			'<input type="%1$s" name="%2$s[%3$s][%4$s]" value="%5$s" placeholder="%6$s" class="%7$s" />',
			$this->get_input_type(),
			esc_attr( $setting_key ),
			esc_attr( $post_type ),
			esc_attr( $this->get_id() ),
			$this->escape_render_input_value( ( $option_escaped_value ?? $default_value ) ),
			$this->escape_render_input_value( $default_value ),
			esc_attr( $this->class )
		);
	}

	/**
	 * @param mixed $value
	 */
	public function sanitize_field( $value ): string {
		return sanitize_text_field( (string) $value );
	}

	/**
	 * Get the input type for the field.
	 *
	 * @return string The input type.
	 */
	public function get_input_type(): string {
		return 'text';
	}

	/**
	 * Escape the value for rendering in the input field.
	 *
	 * @param string $value
	 */
	protected function escape_render_input_value(string $value): string {
		return esc_attr( $value );
	}
}
