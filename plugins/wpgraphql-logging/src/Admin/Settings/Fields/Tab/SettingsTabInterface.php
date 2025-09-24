<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Admin\Settings\Fields\Tab;

/**
 * Interface for settings field tabs.
 *
 * This interface defines the contract for tab classes that group related settings fields together.
 * Each tab implementation should provide a name and a collection of fields.
 *
 * @package WPGraphQL\Logging
 *
 * @since 0.0.1
 */
interface SettingsTabInterface {
	/**
	 * Get the name of the tab.
	 *
	 * @return string The tab name/identifier.
	 */
	public function get_name(): string;

	/**
	 * Get the label of the tab.
	 *
	 * @return string The tab label.
	 */
	public function get_label(): string;

	/**
	 * Get the fields for this tab.
	 *
	 * @return array<string, \WPGraphQL\Logging\Admin\Settings\Fields\SettingsFieldInterface> Array of fields keyed by field ID.
	 */
	public function get_fields(): array;
}
