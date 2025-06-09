<?php

declare(strict_types=1);

namespace HWP\Previews\Admin\Settings;

use HWP\Previews\Admin\Settings\Fields\Settings_Field_Collection;

class Settings_Form_Manager {
	/**
	 * Array of available post-types for which settings are registered.
	 *
	 * @var array<string>
	 */
	protected array $post_types = [];

	/**
	 * Array of fields to be registered in the settings sections.
	 *
	 * @var \HWP\Previews\Admin\Settings\Fields\Settings_Field_Collection
	 */
	protected Settings_Field_Collection $fields;

	/**
	 * @param array<string>                                                 $post_types Array of post types for which settings are registered.
	 * @param \HWP\Previews\Admin\Settings\Fields\Settings_Field_Collection $fields Collection of fields to be registered in the settings sections.
	 */
	public function __construct( array $post_types, Settings_Field_Collection $fields ) {
		$this->post_types = $post_types;
		$this->fields     = $fields;
	}

	/**
	 * Render the settings form for all post-types.
	 *
	 * This method creates tabs for each post-type and renders the settings fields for each post-type.
	 */
	public function render_form(): void {
		$this->create_tabs();

		foreach ( $this->post_types as $post_type => $label ) {
			$this->render_post_type_section( $post_type, $label );
		}
	}

	/**
	 * Create the settings tabs for the post-types.
	 *
	 * This method registers the settings group and the settings key for the post-types.
	 */
	protected function create_tabs(): void {

		// @TODO - Refactor with Settings Group.
		$option_group = apply_filters( 'hwp_previews_settings_group', HWP_PREVIEWS_SETTINGS_GROUP );
		$option_name  = apply_filters( 'hwp_previews_settings_key', HWP_PREVIEWS_SETTINGS_KEY );

		register_setting(
			$option_group,
			$option_name,
			[
				'sanitize_callback' => [ $this, 'sanitize_settings' ],
				'type'              => 'array',
				'default'           => [],
			]
		);
	}

	/**
	 * Render the settings section for a specific post-type.
	 *
	 * This method creates a settings section for the given post-type and renders the fields for that section.
	 *
	 * @param string $post_type The post type slug.
	 * @param string $label     The label for the post type section.
	 */
	protected function render_post_type_section( string $post_type, string $label ): void {
		$fields          = $this->fields->get_fields();
		$page_id         = 'hwp_previews_section_' . $post_type;
		$page_uri        = 'hwp-previews-' . $post_type;
		$is_hierarchical = is_post_type_hierarchical( $post_type );

		add_settings_section( $page_id, $label, static fn() => null, $page_uri );

		/** @var \HWP\Previews\Admin\Settings\Fields\Settings_Field_Interface $field */
		foreach ( $fields as $field ) {
			if ( ! $field->should_render_field( $is_hierarchical ) ) {
				continue;
			}

			$field->add_settings_field(
				$page_id,
				$page_uri,
				[
					'post_type'    => $post_type,
					'label'        => $label,
					'settings_key' => HWP_PREVIEWS_SETTINGS_KEY,
				]
			);
		}
	}

	/**
	 * Sanitize and merge new settings per-tab, pruning unknown fields.
	 *
	 * @param array<string,mixed> $new_input New settings input for the specific tab that comes from the form for the sanitization.
	 *
	 * @return array<string,mixed>
	 */
	protected function sanitize_settings( array $new_input ): array {

		// @TODO - Refactor with Settings Group.
		$option_name = apply_filters( 'hwp_previews_settings_key', HWP_PREVIEWS_SETTINGS_KEY );

		$old_input = (array) get_option( $option_name, [] );

		// Remove redundant tabs.
		$post_types = array_keys( $this->post_types );
		$old_input  = array_intersect_key( $old_input, array_flip( $post_types ) );

		$tab = array_keys( $new_input );
		if ( ! isset( $tab[0] ) ) {
			return $old_input; // Wrong settings structure.
		}

		$tab_to_sanitize = (string) $tab[0];
		if ( ! is_array( $new_input[ $tab_to_sanitize ] ) ) {
			return $old_input; // Wrong settings structure.
		}

		// Sanitize the fields in the tab.
		$sanitized_fields = [];
		foreach ( $new_input[ $tab_to_sanitize ] as $key => $value ) {
			$field = $this->fields->get_field( $key );
			if ( is_null( $field ) ) {
				continue; // Skip unknown fields.
			}

			$sanitized_fields[ $key ] = $field->sanitize_field( $value );
		}

		// Merge the sanitized fields with the old input.
		$old_input[ $tab_to_sanitize ] = $sanitized_fields;

		return $old_input;
	}
}
