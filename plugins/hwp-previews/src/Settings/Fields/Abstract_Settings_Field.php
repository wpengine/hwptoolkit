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
	 * The settings field settings key.
	 *
	 * @var string
	 */
	private string $settings_key;

	/**
	 * The settings field post type.
	 *
	 * @var string
	 */
	private string $post_type;

	/**
	 * The description field to show in the tooltip.
	 *
	 * @var string
	 */
	private string $description;

	/**
	 * Render the settings field.
	 *
	 * @param array<string, mixed> $option_value Settings value.
	 * @param string               $setting_key The settings key.
	 * @param string               $post_type The post type.
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
		string $css_class = '',
		string $description = ''
	) {
		$this->id          = $id;
		$this->title       = $title;
		$this->class       = $css_class;
		$this->description = $description;
	}

	/**
	 * Set the settings key.
	 *
	 * @param string $settings_key The settings key.
	 *
	 * @return $this
	 */
	public function set_settings_key( string $settings_key ): self {
		$this->settings_key = $settings_key;

		return $this;
	}

	/**
	 * Set the post type.
	 *
	 * @param string $post_type The post type.
	 *
	 * @return $this
	 */
	public function set_post_type( string $post_type ): self {
		$this->post_type = $post_type;

		return $this;
	}

	/**
	 * Register the settings field.
	 *
	 * @param string $section The settings section.
	 * @param string $page The settings page.
	 */
	public function register_settings_field( string $section, string $page ): void {

		add_settings_field(
			$this->id,
			$this->title,
			[ $this, 'settings_field_callback' ],
			$page,
			$section
		);
	}

	/**
	 * Callback for the settings field.
	 */
	public function settings_field_callback(): void {
		printf(
			'<div tabindex="0" aria-describedby="%2$s-tooltip" class="hwp-previews-tooltip">
				<span class="dashicons dashicons-editor-help"></span>
				<span id="%2$s-tooltip" class="tooltip-text description">%1$s</span>
			</div>',
			esc_attr( $this->description ),
			esc_attr( $this->settings_key )
		);

		$this->render_field(
			$this->get_setting_value( $this->settings_key, $this->post_type ),
			$this->settings_key,
			$this->post_type
		);
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
