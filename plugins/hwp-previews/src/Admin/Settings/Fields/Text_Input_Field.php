<?php

declare(strict_types=1);

namespace HWP\Previews\Admin\Settings\Fields;

class Text_Input_Field extends Abstract_Settings_Field {
	/**
	 * The default value for the field.
	 *
	 * @var string
	 */
	private string $default;

	/**
	 * Constructor.
	 *
	 * @param string $id The settings field ID.
	 * @param string $title The settings field title.
	 * @param string $description The settings field description.
	 * @param string $default_value The default value for the field.
	 * @param string $css_class The settings field class.
	 */
	public function __construct( string $id, string $title, string $description = '', string $default_value = '', string $css_class = '' ) {
		parent::__construct( $id, $title, $css_class, $description );

		$this->default = $default_value;
	}

	/**
	 * Render the field.
	 *
	 * @param array<string, mixed> $option_value The value of the field.
	 * @param string               $setting_key The settings key.
	 * @param string               $post_type The post type.
	 */
	protected function render_field( array $option_value, string $setting_key, string $post_type ): void {
		printf(
			'<input type="text" name="%1$s[%2$s][%3$s]" value="%4$s" placeholder="%5$s" class="%6$s" />',
			esc_attr( $setting_key ),
			esc_attr( $post_type ),
			esc_attr( $this->id ),
			esc_attr( (string) ( $option_value[ $this->id ] ?? $this->default ) ),
			esc_attr( $this->default ),
			esc_attr( $this->class )
		);
	}
}
