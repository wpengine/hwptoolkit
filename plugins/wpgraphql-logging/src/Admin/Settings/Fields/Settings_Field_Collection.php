<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Admin\Settings\Fields;

use WPGraphQL\Logging\Admin\Settings\Fields\Tab\Basic_Configuration_Tab;
use WPGraphQL\Logging\Admin\Settings\Fields\Tab\Data_Management_Tab;
use WPGraphQL\Logging\Admin\Settings\Fields\Tab\Settings_Tab_Interface;

/**
 * Class Settings_Field_Collection
 *
 * @package WPGraphQL\Logging
 *
 * @since 0.0.1
 */
class Settings_Field_Collection {
	/**
	 * Array of fields
	 *
	 * @var array<\WPGraphQL\Logging\Admin\Settings\Fields\Settings_Field_Interface>
	 */
	protected array $fields = [];

	/**
	 * Array of tabs
	 *
	 * @var array<\WPGraphQL\Logging\Admin\Settings\Fields\Tab\Settings_Tab_Interface>
	 */
	protected array $tabs = [];

	/**
	 * Constructor to initialize the fields.
	 */
	public function __construct() {
		$this->add_tab( new Basic_Configuration_Tab() );
		$this->add_tab( new Data_Management_Tab() );
		do_action( 'wpgraphql_logging_settings_field_collection_init', $this );
	}

	/**
	 * @return array<\WPGraphQL\Logging\Admin\Settings\Fields\Settings_Field_Interface>
	 */
	public function get_fields(): array {
		return $this->fields;
	}

	/**
	 * Get a specific field by its key.
	 *
	 * @param string $key The key of the field to retrieve.
	 *
	 * @return \WPGraphQL\Logging\Admin\Settings\Fields\Settings_Field_Interface|null The field if found, null otherwise.
	 */
	public function get_field( string $key ): ?Settings_Field_Interface {
		return $this->fields[ $key ] ?? null;
	}

	/**
	 * Add a field to the collection.
	 *
	 * @param string                                                            $key   The key for the field.
	 * @param \WPGraphQL\Logging\Admin\Settings\Fields\Settings_Field_Interface $field The field to add.
	 */
	public function add_field( string $key, Settings_Field_Interface $field ): void {
		$this->fields[ $key ] = $field;
	}

	/**
	 * Remove a field from the collection.
	 *
	 * @param string $key The key of the field to remove.
	 */
	public function remove_field( string $key ): void {
		unset( $this->fields[ $key ] );
	}

	/**
	 * Add a tab to the collection.
	 *
	 * @param \WPGraphQL\Logging\Admin\Settings\Fields\Tab\Settings_Tab_Interface $tab The tab to add.
	 */
	public function add_tab( Settings_Tab_Interface $tab ): void {
		$this->tabs[ $tab->get_name() ] = $tab;

		foreach ( $tab->get_fields() as $field_key => $field ) {
			$this->add_field( $field_key, $field );
		}
	}

	/**
	 * Get all tabs.
	 *
	 * @return array<\WPGraphQL\Logging\Admin\Settings\Fields\Tab\Settings_Tab_Interface>
	 */
	public function get_tabs(): array {
		return $this->tabs;
	}

	/**
	 * Get a specific tab by its name.
	 *
	 * @param string $tab_name The name of the tab to retrieve.
	 *
	 * @return \WPGraphQL\Logging\Admin\Settings\Fields\Tab\Settings_Tab_Interface|null The tab if found, null otherwise.
	 */
	public function get_tab( string $tab_name ): ?Settings_Tab_Interface {
		return $this->tabs[ $tab_name ] ?? null;
	}
}
