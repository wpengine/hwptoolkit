<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Admin\Settings\Fields\Tab;

use WPGraphQL\Logging\Admin\Settings\Fields\Field\CheckboxField;
use WPGraphQL\Logging\Admin\Settings\Fields\Field\SelectField;
use WPGraphQL\Logging\Admin\Settings\Fields\Field\TextInputField;
use WPGraphQL\Logging\Events\Events;

/**
 * Basic Configuration Tab class.
 *
 * @package WPGraphQL\Logging
 *
 * @since 0.0.1
 */
class BasicConfigurationTab implements SettingsTabInterface {
	/**
	 * The field ID for the enabled checkbox.
	 *
	 * @var string
	 */
	public const ENABLED = 'enabled';

	/**
	 * The field ID for the IP restrictions text input.
	 *
	 * @var string
	 */
	public const IP_RESTRICTIONS = 'ip_restrictions';

	/**
	 * The field ID for the data sampling select.
	 *
	 * @var string
	 */
	public const DATA_SAMPLING = 'data_sampling';

	/**
	 * The field ID for the user-based logging select.
	 *
	 * @var string
	 */
	public const ADMIN_USER_LOGGING = 'admin_user_logging';

	/**
	 * The field ID for the log point selection select.
	 *
	 * @var string
	 */
	public const EVENT_LOG_SELECTION = 'event_log_selection';

	/**
	 * The field ID for the exclude query text input.
	 *
	 * @var string
	 */
	public const EXCLUDE_QUERY = 'exclude_query';

	/**
	 * The field ID for whether to log the response from the WPGGraphQL query into the context object.
	 *
	 * @var string
	 */
	public const LOG_RESPONSE = 'log_response';

	/**
	 * Get the name/identifier of the tab.
	 */
	public function get_name(): string {
		return 'basic_configuration';
	}

	/**
	 * Get the label of the tab.
	 *
	 * @return string The tab label.
	 */
	public function get_label(): string {
		return 'Basic Configuration';
	}

	/**
	 * Get the fields for this tab.
	 *
	 * @return array<string, \WPGraphQL\Logging\Admin\Settings\Fields\SettingsFieldInterface> Array of fields keyed by field ID.
	 */
	public function get_fields(): array {
		$fields = [];

		$fields[ self::ENABLED ] = new CheckboxField(
			self::ENABLED,
			$this->get_name(),
			__( 'Enabled', 'wpgraphql-logging' ),
			'',
			__( 'Enable or disable WPGraphQL logging.', 'wpgraphql-logging' ),
		);

		$fields[ self::IP_RESTRICTIONS ] = new TextInputField(
			self::IP_RESTRICTIONS,
			$this->get_name(),
			__( 'IP Restrictions', 'wpgraphql-logging' ),
			'',
			__( 'Comma-separated list of IPv4/IPv6 addresses to restrict logging to. Leave empty to log from all IPs.', 'wpgraphql-logging' ),
			__( 'e.g., 192.168.1.1, 10.0.0.1', 'wpgraphql-logging' )
		);

		$fields[ self::EXCLUDE_QUERY ] = new TextInputField(
			self::EXCLUDE_QUERY,
			$this->get_name(),
			__( 'Exclude Queries', 'wpgraphql-logging' ),
			'',
			__( 'Comma-separated list of GraphQL query names to exclude from logging.', 'wpgraphql-logging' ),
			__( 'e.g., __schema,SeedNode,__typename', 'wpgraphql-logging' )
		);

		$fields[ self::ADMIN_USER_LOGGING ] = new CheckboxField(
			self::ADMIN_USER_LOGGING,
			$this->get_name(),
			__( 'Admin User Logging', 'wpgraphql-logging' ),
			'',
			__( 'Log only for admin users.', 'wpgraphql-logging' )
		);

		$fields[ self::DATA_SAMPLING ] = new SelectField(
			self::DATA_SAMPLING,
			$this->get_name(),
			__( 'Data Sampling Rate', 'wpgraphql-logging' ),
			[
				'10'  => __( '10% (Every 10th request)', 'wpgraphql-logging' ),
				'25'  => __( '25% (Every 4th request)', 'wpgraphql-logging' ),
				'50'  => __( '50% (Every other request)', 'wpgraphql-logging' ),
				'75'  => __( '75% (Every 3 out of 4 requests)', 'wpgraphql-logging' ),
				'100' => __( '100% (All requests)', 'wpgraphql-logging' ),
			],
			'',
			__( 'Percentage of requests to log for performance optimization.', 'wpgraphql-logging' ),
			false
		);

		$fields[ self::EVENT_LOG_SELECTION ] = new SelectField(
			self::EVENT_LOG_SELECTION,
			$this->get_name(),
			__( 'Log Points', 'wpgraphql-logging' ),
			[
				Events::PRE_REQUEST              => __( 'Pre Request', 'wpgraphql-logging' ),
				Events::BEFORE_GRAPHQL_EXECUTION => __( 'Before Query Execution', 'wpgraphql-logging' ),
				Events::BEFORE_RESPONSE_RETURNED => __( 'Before Response Returned', 'wpgraphql-logging' ),
				Events::REQUEST_DATA             => __( 'Request Data', 'wpgraphql-logging' ),
				Events::REQUEST_RESULTS          => __( 'Request Results', 'wpgraphql-logging' ),
				Events::RESPONSE_HEADERS_TO_SEND => __( 'Response Headers', 'wpgraphql-logging' ),
			],
			'',
			__( 'Select which points in the request lifecycle to log. By default, no events are logged.', 'wpgraphql-logging' ),
			true
		);

		$fields[ self::LOG_RESPONSE ] = new CheckboxField(
			self::LOG_RESPONSE,
			$this->get_name(),
			__( 'Log Response', 'wpgraphql-logging' ),
			'',
			__( 'Whether or not to log the response from the WPGraphQL query into the context object.', 'wpgraphql-logging' ),
		);

		return apply_filters( 'wpgraphql_logging_basic_configuration_fields', $fields );
	}
}
