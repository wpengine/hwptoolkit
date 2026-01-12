<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Admin\Settings\Fields\Tab;

/**
 * Interface for settings tabs.
 *
 * Defines the contract for a settings tab that groups related fields.
 * Each tab must provide its metadata and fields for registration in the WordPress Settings API.
 *
 * @package WPGraphQL\Logging
 *
 * @since 0.0.1
 */
interface SettingsTabInterface {
	/**
	 * Get the settings fields for this tab.
	 *
	 * The returned array should be keyed by field ID and contain instances
	 * implementing SettingsFieldInterface. These fields will be rendered
	 * and registered automatically in the admin settings page.
	 *
	 * @return array<string, \WPGraphQL\Logging\Admin\Settings\Fields\SettingsFieldInterface> Fields keyed by their unique ID.
	 */
	public function get_fields(): array;

	/**
	 * Get the unique name/slug for this tab.
	 *
	 * Must be unique within the plugin to avoid conflicts between tabs.
	 * This name is used in URLs, queries, and as the array key for field storage.
	 *
	 * @return string The tab name/slug.
	 */
	public static function get_name(): string;

	/**
	 * Get the human-readable label for this tab.
	 *
	 * The label is displayed in the admin UI as the tab title.
	 * Should be internationalized using esc_html__().
	 *
	 * @return string The tab label.
	 */
	public static function get_label(): string;
}
