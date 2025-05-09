<?php

declare(strict_types=1);

namespace HWP\Previews\Settings\Fields;

abstract class Abstract_Settings_Field {

	/**
	 * The settings field ID.
	 *
	 * @var string
	 */
	protected string $id;

	/**
	 * The settings field class.
	 *
	 * @var string
	 */
	protected string $class;

	/**
	 * The settings field title.
	 *
	 * @var string
	 */
	private string $title;

	/**
	 * Render the settings field.
	 *
	 * @param array<string, mixed> $option_value Settings value.
	 * @param string               $setting_key The settings key.
	 * @param string               $post_type The post type.
	 *
	 * @return void
	 */
	abstract protected function render_field( array $option_value, string $setting_key, string $post_type ): void;

	/**
	 * Constructor.
	 *
	 * @param string $id The settings field ID.
	 * @param string $title The settings field title.
	 * @param string $css_class The settings field class.
	 */
	public function __construct(
		string $id,
		string $title,
		string $css_class = ''
	) {
		$this->id    = $id;
		$this->title = $title;
		$this->class = $css_class;
	}

	/**
	 * Register the settings field.
	 *
	 * @param string $settings_key The settings key.
	 * @param string $section The settings section.
	 * @param string $post_type The post type.
	 * @param string $page The settings page.
	 *
	 * @return void
	 */
	public function register_settings_field( string $settings_key, string $section, string $post_type, string $page ): void {
		add_settings_field(
			$this->id,
			$this->title,
			[ $this, 'settings_field_callback' ],
			$page,
			$section,
			[
				'settings_key' => $settings_key,
				'post_type'    => $post_type,
			]
		);
	}

	/**
	 * Callback for the settings field.
	 *
	 * @param array<string, mixed> $args The settings field arguments.
	 *
	 * @return void
	 */
	public function settings_field_callback( array $args ): void {
		$settings_key = (string) ( $args['settings_key'] ?? '' );
		$post_type    = (string) ( $args['post_type'] ?? '' );
		$value        = $this->get_setting_value( $settings_key, $post_type );

		$this->render_field( $value, $settings_key, $post_type );
	}

	/**
	 * Get the settings value.
	 *
	 * @param string $settings_key The settings key.
	 * @param string $post_type The post type.
	 *
	 * @return array<string, mixed>
	 */
	private function get_setting_value( string $settings_key, string $post_type ): array {
		$value = get_option( $settings_key, [] );

		if (
			empty( $value ) ||
			! isset( $value[ $post_type ] ) ||
			! is_array( $value[ $post_type ] )
		) {
			return [];
		}

		return $value[ $post_type ];
	}

}
