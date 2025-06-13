<?php

declare(strict_types=1);

namespace HWP\Previews\Admin\Settings\Fields\Field;

use HWP\Previews\Admin\Settings\Fields\Settings_Field_Interface;

abstract class Abstract_Settings_Field implements Settings_Field_Interface {
	/**
	 * The settings field ID.
	 *
	 * @var string
	 */
	protected string $id;

	/**
	 * Whether the field is hierarchical.
	 *
	 * @var bool
	 */
	protected bool $is_hierarchical = false;

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
	 * The description field to show in the tooltip.
	 *
	 * @var string
	 */
	private string $description;

	/**
	 * Constructor.
	 *
	 * @param string $id The settings field ID.
	 * @param bool   $is_hierarchical Whether the field is hierarchical.
	 * @param string $title The settings field title.
	 * @param string $css_class The settings field class.
	 * @param string $description The description field to show in the tooltip.
	 */
	public function __construct(
		string $id,
		bool $is_hierarchical,
		string $title,
		string $css_class = '',
		string $description = ''
	) {
		$this->id              = $id;
		$this->is_hierarchical = $is_hierarchical;
		$this->title           = $title;
		$this->class           = $css_class;
		$this->description     = $description;
	}

	/**
	 * Get the settings field ID.
	 */
	public function get_id(): string {
		return $this->id;
	}

	/**
	 * Where the field is hierarchical or not.
	 */
	public function is_hierarchical(): bool {
		return $this->is_hierarchical;
	}

		/**
		 * Get the settings field title.
		 */
	public function get_title(): string {
		return $this->title;
	}

		/**
		 * Get the description field.
		 */
	public function get_description(): string {
		return $this->description;
	}

	/**
	 * Register the settings field.
	 *
	 * @param string               $section The settings section.
	 * @param string               $page The settings page.
	 * @param array<string, mixed> $args The arguments for the settings field.
	 *
	 * @psalm-suppress ArgumentTypeCoercion
	 */
	public function add_settings_field( string $section, string $page, array $args ): void {
		add_settings_field(
			$this->id,
			$this->get_title(),
			[ $this, 'settings_field_callback' ],
			$page,
			$section,
			$args
		);
	}

	/**
	 * Callback for the settings field.
	 *
	 * @param array<string, mixed> $args The arguments for the settings field.
	 */
	public function settings_field_callback( array $args ): void {

		$post_type    = $args['post_type'] ?? 'post';
		$settings_key = $args['settings_key'] ?? HWP_PREVIEWS_SETTINGS_KEY;

		printf(
			'<div tabindex="0" aria-describedby="%2$s-tooltip" class="hwp-previews-tooltip">
				<span class="dashicons dashicons-editor-help"></span>
				<span id="%2$s-tooltip" class="tooltip-text description">%1$s</span>
			</div>',
			esc_attr( $this->get_description() ),
			esc_attr( $settings_key )
		);

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->render_field(
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			$this->get_setting_value( $settings_key, $post_type ),
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			$settings_key,
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			$post_type,
		);
	}

	/**
	 * Whethere to render the field or not based on the post-type hierarchy.
	 *
	 * @param bool $post_type_hierarchal
	 */
	public function should_render_field( bool $post_type_hierarchal ): bool {

		// If the post-type is hierarchical, then we always render the field as we don't need to check the field hierarchy.
		if ( true === $post_type_hierarchal ) {
			return true;
		}

		return ! $this->is_hierarchical();
	}

	/**
	 * Get the settings value.
	 *
	 * @param string $settings_key The settings key.
	 * @param string $post_type The post type.
	 *
	 * @return array<string, mixed>
	 */
	public function get_setting_value( string $settings_key, string $post_type ): array {
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
