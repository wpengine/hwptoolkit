<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Admin\Settings\Fields\Field;

use WPGraphQL\Logging\Admin\Settings\Fields\SettingsFieldInterface;

/**
 * Abstract Settings Field class for WPGraphQL Logging.
 *
 * This class provides a base implementation for settings fields, including rendering, sanitization, and field registration.
 *
 * @package WPGraphQL\Logging
 *
 * @since 0.0.1
 */
abstract class AbstractSettingsField implements SettingsFieldInterface {
	/**
	 * Constructor.
	 *
	 * @param string $id           The settings field ID.
	 * @param string $tab          The tab this field should be shown in.
	 * @param string $title        The settings field title.
	 * @param string $css_class    The settings field class.
	 * @param string $description  The description field to show in the tooltip.
	 */
	public function __construct(
		readonly string $id,
		readonly string $tab,
		readonly string $title,
		readonly string $css_class = '',
		readonly string $description = ''
	) {
	}

	/**
	 * Get the settings field ID.
	 */
	public function get_id(): string {
		return $this->id;
	}

	/**
	 * Whether the field should be rendered for a specific tab.
	 *
	 * @param string $tab_key The tab key.
	 */
	public function should_render_for_tab( string $tab_key ): bool {
		return $tab_key === $this->tab;
	}

	/**
	 * Register the settings field.
	 *
	 * @param string               $section The section ID.
	 * @param string               $page    The page URI.
	 * @param array<string, mixed> $args    The field arguments.
	 */
	public function add_settings_field( string $section, string $page, array $args ): void {
		/** @psalm-suppress InvalidArgument */
		add_settings_field(
			$this->get_id(),
			$this->title,
			[ $this, 'render_field_callback' ],
			$page,
			$section,
			array_merge(
				$args,
				[
					'class'       => $this->css_class,
					'description' => $this->description,
				]
			)
		);
	}

	/**
	 * Callback function to render the field.
	 *
	 * @param array<string, mixed> $args The field arguments.
	 */
	public function render_field_callback( array $args ): void {
		$tab_key      = (string) ( $args['tab_key'] ?? '' );
		$settings_key = (string) ( $args['settings_key'] ?? '' );

		$option_value = (array) get_option( $settings_key, [] );

		$id = $this->get_field_name( $settings_key, $tab_key, $this->get_id() );

		printf(
			'<span class="wpgraphql-logging-tooltip">
				<span class="dashicons dashicons-editor-help"></span>
				<span id="%2$s-tooltip" class="tooltip-text description">%1$s</span>
			</span>',
			esc_attr( $this->description ),
			esc_attr( $id ),
		);

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- render_field method handles escaping internally
		echo $this->render_field( $option_value, $settings_key, $tab_key );
	}

	/**
	 * Generate a field name for form inputs.
	 *
	 * @param string $settings_key The settings key.
	 * @param string $tab_key      The tab key.
	 * @param string $field_id     The field ID.
	 */
	protected function get_field_name( string $settings_key, string $tab_key, string $field_id ): string {
		return "{$settings_key}[{$tab_key}][{$field_id}]";
	}

	/**
	 * Get the current field value.
	 *
	 * @param array<string> $option_value The option value.
	 * @param string        $tab_key      The tab key.
	 * @param mixed         $default_value      The default value.
	 */
	protected function get_field_value( array $option_value, string $tab_key, $default_value = '' ): mixed {
		if ( ! array_key_exists( $tab_key, $option_value ) ) {
			return $default_value;
		}

		/** @var array<string, mixed> $tab_value */
		$tab_value = $option_value[ $tab_key ]; // @phpstan-ignore varTag.nativeType
		$id        = $this->get_id();
		if ( empty( $id ) ) {
			return $default_value;
		}

		if ( ! array_key_exists( $id, $tab_value ) ) {
			return $default_value;
		}

		$field_value = $tab_value[ $id ];

		if ( is_null( $field_value ) ) {
			return $default_value;
		}
		return $field_value;
	}
}
