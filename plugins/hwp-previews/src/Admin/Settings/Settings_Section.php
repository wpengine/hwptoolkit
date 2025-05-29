<?php

declare(strict_types=1);

namespace HWP\Previews\Admin\Settings;

class Settings_Section {
	/**
	 * Slug-name to identify the section. Used in the 'id' attribute of tags.
	 *
	 * @var string
	 */
	private string $id;

	/**
	 * Settings section title.
	 *
	 * @var string
	 */
	private string $title;

	/**
	 * The slug-name of the settings page on which to show the section.
	 *
	 * @var string
	 */
	private string $page;

	/**
	 * Array of fields to be registered in the section.
	 *
	 * @var array<\HWP\Previews\Admin\Settings\Fields\Abstract_Settings_Field>
	 */
	private array $fields;

	/**
	 * Constructor.
	 *
	 * @param string                                                             $id Page slug.
	 * @param string                                                             $title Settings section title.
	 * @param string                                                             $page The slug of the settings page.
	 * @param array<\HWP\Previews\Admin\Settings\Fields\Abstract_Settings_Field> $fields Array of fields to be registered in the section.
	 */
	public function __construct(
		string $id,
		string $title,
		string $page,
		array $fields
	) {
		$this->id     = $id;
		$this->title  = $title;
		$this->page   = $page;
		$this->fields = $fields;
	}

	/**
	 * Register the settings section.
	 *
	 * @param string $settings_key The settings key.
	 * @param string $post_type    The post type.
	 * @param string $page         The page slug.
	 */
	public function register_section( string $settings_key, string $post_type, string $page ): void {
		add_settings_section(
			$this->id,
			$this->title,
			static fn() => null,
			$this->page
		);

		foreach ( $this->fields as $field ) {
			$field->set_settings_key( $settings_key );
			$field->set_post_type( $post_type );

			$field->register_settings_field( $this->id, $page );
		}
	}
}
