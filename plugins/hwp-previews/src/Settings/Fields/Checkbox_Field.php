<?php

declare(strict_types=1);

namespace HWP\Previews\Settings\Fields;

class Checkbox_Field extends Abstract_Settings_Field {

	/**
	 * The default value for the field.
	 *
	 * @var bool
	 */
	private bool $default;

	/**
	 * Constructor.
	 *
	 * @param string $id The settings field ID.
	 * @param string $title The settings field title.
	 * @param bool   $default_value The default value for the field.
	 * @param string $css_class The settings field class.
	 */
	public function __construct( string $id, string $title, bool $default_value = false, string $css_class = '' ) {
		parent::__construct( $id, $title, $css_class );

		$this->default = $default_value;
	}

	/**
	 * Render the checkbox settings field.
	 *
	 * @param array<string, mixed> $option_value Settings value.
	 * @param string               $setting_key The settings key.
	 * @param string               $post_type The post type.
	 *
	 * @return void
	 */
	protected function render_field( $option_value, $setting_key, $post_type ): void {
		$enabled = isset( $option_value[ $this->id ] )
			? (bool) $option_value[ $this->id ]
			: $this->default;

		printf(
			'<input type="checkbox" name="%1$s[%2$s][%3$s]" value="1" %4$s class="%5$s" />',
			esc_attr( $setting_key ),
			esc_attr( $post_type ),
			esc_attr( $this->id ),
			checked( 1, $enabled, false ),
			sanitize_html_class( $this->class )
		);
	}

}
