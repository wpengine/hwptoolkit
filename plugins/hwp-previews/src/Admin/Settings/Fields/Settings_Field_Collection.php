<?php

declare(strict_types=1);

namespace HWP\Previews\Admin\Settings\Fields;

use HWP\Previews\Admin\Settings\Fields\Field\Checkbox_Field;
use HWP\Previews\Admin\Settings\Fields\Field\Text_Input_Field;

class Settings_Field_Collection {
	/**
	 * Array of fields
	 *
	 * @var array<\HWP\Previews\Admin\Settings\Fields\Settings_Field_Interface>
	 */
	protected $fields = [];

	/**
	 * Constructor to initialize the fields.
	 */
	public function __construct() {
		$this->initialize_fields();
	}

	/**
	 * @return array<\HWP\Previews\Admin\Settings\Fields\Settings_Field_Interface>
	 */
	public function get_fields(): array {
		return $this->fields;
	}

	/**
	 * Get a specific field by its key.
	 *
	 * @param string $key The key of the field to retrieve.
	 *
	 * @return \HWP\Previews\Admin\Settings\Fields\Settings_Field_Interface|null The field if found, null otherwise.
	 */
	public function get_field( string $key ): ?Settings_Field_Interface {
		return $this->fields[ $key ] ?? null;
	}

	/**
	 * Add a field to the collection.
	 *
	 * @param \HWP\Previews\Admin\Settings\Fields\Settings_Field_Interface $field The field to add.
	 */
	public function add_field( Settings_Field_Interface $field ): void {
		$key                  = $field->get_id();
		$this->fields[ $key ] = $field;
	}

	/**
	 * Remove a field from the collection by its key.
	 *
	 * @param string $key The key of the field to remove.
	 */
	public function remove_field( string $key ): void {
		unset( $this->fields[ $key ] );
	}

	/**
	 * Initialize the fields for the settings.
	 */
	protected function initialize_fields(): void {

		$this->add_field(
			new Checkbox_Field(
				'enabled',
				false,
				__( 'Enable Previews', 'hwp-previews' ),
				__( 'Enable previews for post type.', 'hwp-previews' )
			)
		);

		$this->add_field(
			new Checkbox_Field(
				'post_statuses_as_parent',
				true,
				__( 'Allow all post statuses in parents option', 'hwp-previews' ),
				__( 'By default WordPress only allows published posts to be parents. This option allows posts of all statuses to be used as parent within hierarchical post types.', 'hwp-previews' )
			)
		);

		$this->add_field(
			new Checkbox_Field(
				'in_iframe',
				false,
				__( 'Use iframe to render previews', 'hwp-previews' ),
				__( 'With this option enabled, headless previews will be displayed inside an iframe on the preview page, without leaving WordPress.', 'hwp-previews' )
			)
		);

		$this->add_field(
			new Text_Input_Field(
				'preview_url',
				false,
				__( 'Preview URL', 'hwp-previews' ),
				__( 'Construct your preview URL using the tags on the right. You can add any parameters needed to support headless previews.', 'hwp-previews' ),
				'https://localhost:3000/%s?preview=true&post_id={ID}&name={slug}',
				'code hwp-previews-url' // The class is being used as a query for the JS.
			)
		);

		// Allow other plugins to add their own fields.
		apply_filters( 'hwp_previews_settings_fields', $this );
	}
}
