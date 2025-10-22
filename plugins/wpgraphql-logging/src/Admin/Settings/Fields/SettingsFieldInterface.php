<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Admin\Settings\Fields;

/**
 * Interface for settings fields.
 *
 * Defines the contract for a settings field in WPGraphQL Logging.
 * Implementing classes must handle rendering, sanitization, and integration with WordPress Settings API.
 *
 * @package WPGraphQL\Logging
 * @since 0.0.1
 */
interface SettingsFieldInterface {

    /**
     * Render the settings field.
     *
     * This should return the HTML for the field. Always escape output using esc_html(), esc_attr(), or wp_kses_post() as appropriate.
     *
     * @param array<string, mixed> $option_value The option value(s) for this field.
     * @param string               $setting_key  The setting key associated with this field.
     * @param string               $tab_key      The tab key that this field belongs to.
     *
     * @return string Rendered HTML for the field.
     */
    public function render_field(array $option_value, string $setting_key, string $tab_key): string;

    /**
     * Get the unique field ID.
     *
     * Must be unique within the tab/page to avoid conflicts.
     *
     * @return string The field ID.
     */
    public function get_id(): string;

    /**
     * Determine if the field should render for a specific tab.
     *
     * @param string $tab_key The tab key to check.
     *
     * @return bool True if the field should render for this tab.
     */
    public function should_render_for_tab(string $tab_key): bool;

    /**
     * Add the field to WordPress Settings API.
     *
     * Implementing classes should call add_settings_field() internally with proper sanitization callback.
     *
     * @param string               $section The settings section ID.
     * @param string               $page    The settings page ID.
     * @param array<string, mixed> $args    Additional field arguments.
     */
    public function add_settings_field(string $section, string $page, array $args): void;

    /**
     * Sanitize the field value.
     *
     * Must ensure that all output saved to the database is safe.
     * For text: use sanitize_text_field(), for HTML: wp_kses_post(), etc.
     *
     * @param mixed $value The raw value from user input.
     *
     * @return mixed The sanitized value to store in the database.
     */
    public function sanitize_field(mixed $value): mixed;
}
