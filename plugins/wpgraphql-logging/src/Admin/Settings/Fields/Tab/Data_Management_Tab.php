<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Admin\Settings\Fields\Tab;

use WPGraphQL\Logging\Admin\Settings\Fields\Field\Checkbox_Field;
use WPGraphQL\Logging\Admin\Settings\Fields\Field\Text_Input_Field;
use WPGraphQL\Logging\Admin\Settings\Fields\Field\Text_Integer_Field;

/**
 * Data Management Tab class.
 *
 * @package WPGraphQL\Logging
 *
 * @since 0.0.1
 */
class Data_Management_Tab implements Settings_Tab_Interface {
	/**
	 * The field ID for the enabled checkbox.
	 *
	 * @var string
	 */
	public const DATA_DELETION_ENABLED = 'data_deletion_enabled';

	/**
	 * The field ID for the number of days to retain data.
	 *
	 * @var string
	 */
	public const DATA_RETENTION_DAYS = 'data_retention_days';

	/**
	 * The field ID for the data sanitization enabled checkbox.
	 *
	 * @var string
	 */
	public const DATA_SANITIZATION_ENABLED = 'data_sanitization_enabled';

	/**
	 * The field ID for the data sanitization fields.
	 *
	 * @var string
	 */
	public const DATA_SANITIZATION_FIELDS = 'data_sanitization_fields';

	/**
	 * Get the name/identifier of the tab.
	 */
	public function get_name(): string {
		return 'data_management';
	}

	/**
	 * Get the label of the tab.
	 *
	 * @return string The tab label.
	 */
	public function get_label(): string {
		return 'Data Management';
	}

	/**
	 * Get the fields for this tab.
	 *
	 * @return array<string, \WPGraphQL\Logging\Admin\Settings\Fields\Settings_Field_Interface> Array of fields keyed by field ID.
	 */
	public function get_fields(): array {
		$fields = [];

		$fields[ self::DATA_DELETION_ENABLED ] = new Checkbox_Field(
			self::DATA_DELETION_ENABLED,
			$this->get_name(),
			__( 'Data Deletion Enabled', 'wpgraphql-logging' ),
			'',
			__( 'Enable or disable data deletion for WPGraphQL logging.', 'wpgraphql-logging' ),
		);

		$fields[ self::DATA_RETENTION_DAYS ] = new Text_Integer_Field(
			self::DATA_RETENTION_DAYS,
			$this->get_name(),
			__( 'Number of Days to Retain Logs', 'wpgraphql-logging' ),
			'',
			__( 'Number of days to retain log data before deletion.', 'wpgraphql-logging' ),
			__( 'e.g., 30', 'wpgraphql-logging' ),
			'30'
		);

		$fields[ self::DATA_SANITIZATION_ENABLED ] = new Checkbox_Field(
			self::DATA_SANITIZATION_ENABLED,
			$this->get_name(),
			__( 'Data Sanitization Enabled', 'wpgraphql-logging' ),
			'',
			__( 'Enable or disable data sanitization for WPGraphQL logging.', 'wpgraphql-logging' ),
		);


		$fields[ self::DATA_SANITIZATION_FIELDS ] = new Text_Input_Field(
			self::DATA_SANITIZATION_FIELDS,
			$this->get_name(),
			__( 'Data Sanitization Fields', 'wpgraphql-logging' ),
			'',
			__( 'A comma-separated list of fields to sanitize for WPGraphQL logging.', 'wpgraphql-logging' ),
			__( 'e.g., user.email, user.name, user.firstName, user.lastName', 'wpgraphql-logging' ),
			'user_email, user_pass, user_login, user_status, display_name, nickname, first_name, last_name'
		);


		return apply_filters( 'wpgraphql_logging_data_management_fields', $fields );
	}
}
