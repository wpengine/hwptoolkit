<?php

declare(strict_types=1);

namespace HWP\Previews\Admin\Settings\Fields\Field;

/**
 * Checkbox field class
 *
 * This class represents a checkbox field in the settings of the HWP Previews plugin.
 *
 * @package HWP\Previews
 *
 * @since 0.0.1
 */
class Checkbox_Field extends Abstract_Settings_Field {
	/**
	 * The default value for the field.
	 *
	 * @var bool
	 */
	protected bool $default;

	/**
	 * Constructor.
	 *
	 * @param string $id The settings field ID.
	 * @param bool   $is_hierarchical Whether the field is hierarchical.
	 * @param string $title The settings field title.
	 * @param string $description The settings field description.
	 * @param bool   $default_value The default value for the field.
	 * @param string $css_class The settings field class.
	 */
	public function __construct( string $id, bool $is_hierarchical, string $title, string $description = '', bool $default_value = false, string $css_class = '' ) {
		parent::__construct( $id, $is_hierarchical, $title, $css_class, $description );

		$this->default = $default_value;
	}

	/**
	 * Render the checkbox settings field.
	 *
	 * @param array<string> $option_value Settings value.
	 * @param string        $setting_key The settings key.
	 * @param string        $post_type The post type.
	 */
	public function render_field( $option_value, $setting_key, $post_type ): string {
		$enabled = isset( $option_value[ $this->id ] )
			? (bool) $option_value[ $this->id ]
			: $this->default;

		return sprintf(
			'<input type="checkbox" name="%1$s[%2$s][%3$s]" value="1" %4$s class="%5$s" />',
			esc_attr( $setting_key ),
			esc_attr( $post_type ),
			esc_attr( $this->id ),
			checked( 1, $enabled, false ),
			sanitize_html_class( $this->class )
		);
	}

	/**
	 * @param mixed $value
	 */
	public function sanitize_field( $value ): bool {
		return ! empty( $value );
	}
}
