<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Admin\Settings\Fields\Tab;

use WPGraphQL\Logging\Admin\Settings\Fields\Field\CheckboxField;
use WPGraphQL\Logging\Admin\Settings\Fields\Field\SelectField;
use WPGraphQL\Logging\Admin\Settings\Fields\Field\TextInputField;
use WPGraphQL\Logging\Admin\Settings\Fields\Field\TextIntegerField;
use WPGraphQL\Logging\Admin\Settings\Fields\Tab\SettingsTabInterface;

/**
 * Data Management Tab class.
 *
 * @package WPGraphQL\Logging
 *
 * @since 0.0.1
 */
class DataManagementTab implements SettingsTabInterface {
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
	 * The field ID for the data sanitization method.
	 *
	 * @var string
	 */
	public const DATA_SANITIZATION_METHOD = 'data_sanitization_method';

	/**
	 * The field ID for the custom fields to sanitize.
	 *
	 * @var string
	 */
	public const DATA_SANITIZATION_CUSTOM_FIELD_ANONYMIZE = 'data_sanitization_custom_field_anonymize';

	/**
	 * The field ID for the custom fields to remove.
	 *
	 * @var string
	 */
	public const DATA_SANITIZATION_CUSTOM_FIELD_REMOVE = 'data_sanitization_custom_field_remove';

	/**
	 * The field ID for the custom fields to truncate.
	 *
	 * @var string
	 */
	public const DATA_SANITIZATION_CUSTOM_FIELD_TRUNCATE = 'data_sanitization_custom_field_truncate';

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
	 * @return array<string, \WPGraphQL\Logging\Admin\Settings\Fields\SettingsFieldInterface> Array of fields keyed by field ID.
	 */
	public function get_fields(): array {
		$fields = [];

		$fields[ self::DATA_DELETION_ENABLED ] = new CheckboxField(
			self::DATA_DELETION_ENABLED,
			$this->get_name(),
			__( 'Data Deletion Enabled', 'wpgraphql-logging' ),
			'',
			__( 'Enable or disable data deletion for WPGraphQL logging.', 'wpgraphql-logging' ),
		);

		$fields[ self::DATA_RETENTION_DAYS ] = new TextIntegerField(
			self::DATA_RETENTION_DAYS,
			$this->get_name(),
			__( 'Number of Days to Retain Logs', 'wpgraphql-logging' ),
			'',
			__( 'Number of days to retain log data before deletion.', 'wpgraphql-logging' ),
			__( 'e.g., 30', 'wpgraphql-logging' ),
			'30'
		);

		$fields[ self::DATA_SANITIZATION_ENABLED ] = new CheckboxField(
			self::DATA_SANITIZATION_ENABLED,
			$this->get_name(),
			__( 'Data Sanitization Enabled', 'wpgraphql-logging' ),
			'',
			__( 'Enable or disable data sanitization for WPGraphQL logging.', 'wpgraphql-logging' ),
		);


		$fields[ self::DATA_SANITIZATION_METHOD ] = new SelectField(
			self::DATA_SANITIZATION_METHOD,
			$this->get_name(),
			__( 'Data Sanitization Method', 'wpgraphql-logging' ),
			[
				'recommended' => __( 'Recommended', 'wpgraphql-logging' ),
				'custom'      => __( 'Custom', 'wpgraphql-logging' ),
			],
			'',
			__( 'Select the method to use for data sanitization.', 'wpgraphql-logging' ),
			false
		);


		$fields[ self::DATA_SANITIZATION_CUSTOM_FIELD_ANONYMIZE ] = new TextInputField(
			self::DATA_SANITIZATION_CUSTOM_FIELD_ANONYMIZE,
			$this->get_name(),
			__( 'Custom Fields to Anonymize', 'wpgraphql-logging' ),
			'wpgraphql-logging-custom',
			__( 'Comma-separated list of custom fields to anonymize.', 'wpgraphql-logging' ),
			'e.g., user_email, user_ip'
		);

		$fields[ self::DATA_SANITIZATION_CUSTOM_FIELD_REMOVE ] = new TextInputField(
			self::DATA_SANITIZATION_CUSTOM_FIELD_REMOVE,
			$this->get_name(),
			__( 'Custom Fields to Remove', 'wpgraphql-logging' ),
			'wpgraphql-logging-custom',
			__( 'Comma-separated list of custom fields to remove.', 'wpgraphql-logging' ),
		);

		$fields[ self::DATA_SANITIZATION_CUSTOM_FIELD_TRUNCATE ] = new TextInputField(
			self::DATA_SANITIZATION_CUSTOM_FIELD_TRUNCATE,
			$this->get_name(),
			__( 'Custom Fields to Truncate', 'wpgraphql-logging' ),
			'wpgraphql-logging-custom',
			__( 'Comma-separated list of custom fields to truncate.', 'wpgraphql-logging' ),
		);

		return apply_filters( 'wpgraphql_logging_data_management_fields', $fields );
	}
}
