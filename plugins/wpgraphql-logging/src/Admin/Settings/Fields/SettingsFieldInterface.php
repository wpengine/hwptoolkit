<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Admin\Settings\Fields;

/**
 * Interface for settings fields
 *
 * @package WPGraphQL\Logging
 *
 * @since 0.0.1
 */
interface SettingsFieldInterface {
	/**
	 * Render the settings field
	 *
	 * @param array<string> $option_value The option value.
	 * @param string        $setting_key  The setting key.
	 * @param string        $tab_key      The tab key.
	 */
	public function render_field( array $option_value, string $setting_key, string $tab_key ): string;

	/**
	 * Get the field ID
	 */
	public function get_id(): string;

	/**
	 * Whether the field should be rendered for a specific tab
	 *
	 * @param string $tab_key The tab key.
	 */
	public function should_render_for_tab( string $tab_key ): bool;

	/**
	 * Add the settings field
	 *
	 * @param string               $section The section ID.
	 * @param string               $page    The page ID.
	 * @param array<string, mixed> $args    The field arguments.
	 */
	public function add_settings_field( string $section, string $page, array $args ): void;

	/**
	 * Sanitize field value
	 *
	 * @param mixed $value
	 */
	public function sanitize_field( $value ): mixed;
}
