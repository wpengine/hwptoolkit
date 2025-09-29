<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Admin\Settings\Fields;

use WPGraphQL\Logging\Admin\Settings\Fields\Tab\BasicConfigurationTab;
use WPGraphQL\Logging\Admin\Settings\Fields\Tab\DataManagementTab;
use WPGraphQL\Logging\Admin\Settings\Fields\Tab\SettingsTabInterface;

/**
 * Class SettingsFieldCollection
 *
 * @package WPGraphQL\Logging
 *
 * @since 0.0.1
 */
class SettingsFieldCollection {
	/**
	 * Array of fields
	 *
	 * @var array<\WPGraphQL\Logging\Admin\Settings\Fields\SettingsFieldInterface>
	 */
	protected array $fields = [];

	/**
	 * Array of tabs
	 *
	 * @var array<\WPGraphQL\Logging\Admin\Settings\Fields\Tab\SettingsTabInterface>
	 */
	protected array $tabs = [];

	/**
	 * Constructor to initialize the fields.
	 */
	public function __construct() {
		$this->add_tab( new BasicConfigurationTab() );
		$this->add_tab( new DataManagementTab() );
		do_action( 'wpgraphql_logging_settings_field_collection_init', $this );
	}

	/**
	 * @return array<\WPGraphQL\Logging\Admin\Settings\Fields\SettingsFieldInterface>
	 */
	public function get_fields(): array {
		return $this->fields;
	}

	/**
	 * Get a specific field by its key.
	 *
	 * @param string $key The key of the field to retrieve.
	 *
	 * @return \WPGraphQL\Logging\Admin\Settings\Fields\SettingsFieldInterface|null The field if found, null otherwise.
	 */
	public function get_field( string $key ): ?SettingsFieldInterface {
		return $this->fields[ $key ] ?? null;
	}

	/**
	 * Add a field to the collection.
	 *
	 * @param string                                                          $key   The key for the field.
	 * @param \WPGraphQL\Logging\Admin\Settings\Fields\SettingsFieldInterface $field The field to add.
	 */
	public function add_field( string $key, SettingsFieldInterface $field ): void {
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
	 * @param \WPGraphQL\Logging\Admin\Settings\Fields\Tab\SettingsTabInterface $tab The tab to add.
	 */
	public function add_tab( SettingsTabInterface $tab ): void {
		$this->tabs[ $tab->get_name() ] = $tab;

		foreach ( $tab->get_fields() as $field_key => $field ) {
			$this->add_field( $field_key, $field );
		}
	}

	/**
	 * Get all tabs.
	 *
	 * @return array<\WPGraphQL\Logging\Admin\Settings\Fields\Tab\SettingsTabInterface>
	 */
	public function get_tabs(): array {
		return $this->tabs;
	}

	/**
	 * Get a specific tab by its name.
	 *
	 * @param string $tab_name The name of the tab to retrieve.
	 *
	 * @return \WPGraphQL\Logging\Admin\Settings\Fields\Tab\SettingsTabInterface|null The tab if found, null otherwise.
	 */
	public function get_tab( string $tab_name ): ?SettingsTabInterface {
		return $this->tabs[ $tab_name ] ?? null;
	}
}
