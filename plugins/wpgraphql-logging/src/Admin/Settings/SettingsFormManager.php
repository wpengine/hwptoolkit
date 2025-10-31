<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Admin\Settings;

use WPGraphQL\Logging\Admin\Settings\ConfigurationHelper;
use WPGraphQL\Logging\Admin\Settings\Fields\SettingsFieldCollection;

/**
 * Settings Form Manager class for WPGraphQL Logging.
 *
 * This class manages the settings form for different tabs, allowing for the registration and sanitization of settings fields.
 *
 * @package WPGraphQL\Logging
 *
 * @since 0.0.1
 */
class SettingsFormManager {
	/**
	 * @param \WPGraphQL\Logging\Admin\Settings\Fields\SettingsFieldCollection $field_collection        Collection of fields to be registered in the settings sections.
	 * @param \WPGraphQL\Logging\Admin\Settings\ConfigurationHelper            $configuration_helper        The configuration helper instance to access settings.
	 */
	public function __construct(readonly SettingsFieldCollection $field_collection, readonly ConfigurationHelper $configuration_helper) {
		/**
		 * Fire off init action.
		 *
		 * @param \WPGraphQL\Logging\Admin\Settings\SettingsFormManager $instance the instance of the settings class.
		 */
		do_action( 'wpgraphql_logging_settings_form_manager_init', $this );
	}

	/**
	 * Render the settings form for all tabs.
	 *
	 * This method creates tabs for each setting section and renders the settings fields for each tab.
	 */
	public function render_form(): void {
		$option_group = $this->get_settings_group();
		$option_name  = $this->get_option_key();

		register_setting(
			$option_group,
			$option_name,
			[
				'sanitize_callback' => [ $this, 'sanitize_settings' ],
				'type'              => 'array',
				'default'           => [],
			]
		);

		foreach ( $this->field_collection->get_tabs() as $tab ) {
			$this->render_tab_section( $tab::get_name(), $tab::get_label() );
		}
	}

	/**
	 * Sanitize and merge new settings per-tab, pruning unknown fields.
	 *
	 * @param array<string, mixed>|null $new_input New settings input for the specific tab that comes from the form for the sanitization.
	 *
	 * @return array<string,mixed>
	 */
	public function sanitize_settings( ?array $new_input ): array {
		if ( is_null( $new_input ) ) {
			return [];
		}

		$option_name = $this->get_option_key();

		$old_input = (array) get_option( $option_name, [] );

		// Remove redundant tabs.
		$tabs     = $this->field_collection->get_tabs();
		$tab_keys = array_keys( $tabs );

		if ( empty( $tab_keys ) ) {
			return $old_input;
		}

		$old_input = array_intersect_key( $old_input, array_flip( $tab_keys ) );

		$tab = array_keys( $new_input );
		if ( ! isset( $tab[0] ) ) {
			return $old_input;
		}

		$tab_to_sanitize = $tab[0];
		if ( ! is_array( $new_input[ $tab_to_sanitize ] ) ) {
			return $old_input;
		}

		$sanitized_fields = [];
		foreach ( $new_input[ $tab_to_sanitize ] as $key => $value ) {
			$field = $this->field_collection->get_field( $key );

			// Skip unknown fields.
			if ( is_null( $field ) ) {
				continue;
			}

			$sanitized_fields[ $key ] = $field->sanitize_field( $value );
		}

		// Merge the sanitized fields with the old input.
		$old_input[ $tab_to_sanitize ] = $sanitized_fields;

		return $old_input;
	}

	/**
	 * Get the option key for the settings group.
	 */
	public function get_option_key(): string {
		return $this->configuration_helper->get_option_key();
	}

	/**
	 * Get the settings group for the options.
	 */
	public function get_settings_group(): string {
		return $this->configuration_helper->get_settings_group();
	}

	/**
	 * Render the settings section for a specific tab.
	 *
	 * This method creates a settings section for the given tab and renders the fields for that section.
	 *
	 * @param string $tab_key The tab key.
	 * @param string $label   The label for the tab section.
	 */
	protected function render_tab_section( string $tab_key, string $label ): void {
		$fields   = $this->field_collection->get_fields();
		$page_id  = 'wpgraphql_logging_section_' . $tab_key;
		$page_uri = 'wpgraphql-logging-' . $tab_key;

		add_settings_section( $page_id, $label, static fn() => null, $page_uri );

		/** @var \WPGraphQL\Logging\Admin\Settings\Fields\SettingsFieldInterface $field */
		foreach ( $fields as $field ) {
			if ( ! $field->should_render_for_tab( $tab_key ) ) {
				continue;
			}

			$field->add_settings_field(
				$page_id,
				$page_uri,
				[
					'tab_key'      => $tab_key,
					'label'        => $label,
					'settings_key' => $this->get_option_key(),
				]
			);
		}
	}
}
