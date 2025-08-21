<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Service;

use WPGraphQL\Logging\Admin\Settings\Fields\Tab\Basic_Configuration_Tab;

/**
 * Service to manage the settings for the logging.
 */
class Config_Settings_Service {
	/**
	 * The config settings.
	 *
	 * @var array<string, array<string, string|int|bool|array<mixed>>>
	 */
	protected array $config = [];

	/**
	 * The default data sampling percentage.
	 *
	 * @var int
	 */
	public const DEFAULT_DATA_SAMPLING_PERCENTAGE = 25;

	/**
	 * The default performance metric threshold.
	 *
	 * @var float
	 */
	public const DEFAULT_PERFORMANCE_METRIC_THRESHOLD = 0.25;

	/**
	 * Singleton instance of the class.
	 *
	 * @var \WPGraphQL\Logging\Service\Config_Settings_Service|null
	 */
	protected static ?Config_Settings_Service $instance = null;

	/**
	 * Get or create the single instance of the class.
	 */
	public static function init(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
			self::$instance->setup();
		}

		return self::$instance;
	}

	/**
	 * Get the config settings.
	 *
	 * @return array<string, array<string, string|int|bool|array<mixed>>> The config settings.
	 */
	public function get_config_values(): array {
		return $this->config;
	}

	/**
	 * Get a config value.
	 *
	 * @param string $section The section of the config.
	 * @param string $key The key of the config.
	 * @param mixed  $default_value The default value if the key is not found.
	 *
	 * @return mixed The config value.
	 */
	public function get_config_value( string $section, string $key, mixed $default_value = null ): mixed {
		return $this->config[ $section ][ $key ] ?? $default_value;
	}

	/**
	 * Get a string config value.
	 *
	 * @param string $section The section of the config.
	 * @param string $key The key of the config.
	 * @param string $default_value The default value if the key is not found.
	 *
	 * @return string|null The config value.
	 */
	public function get_string_config_value( string $section, string $key, string $default_value = '' ): ?string {
		$value = $this->get_config_value( $section, $key, $default_value );
		return is_string( $value ) ? sanitize_text_field( $value ) : $default_value;
	}

	/**
	 * Get a boolean config value.
	 *
	 * @param string $section The section of the config.
	 * @param string $key The key of the config.
	 * @param bool   $default_value The default value if the key is not found.
	 *
	 * @return bool The config value.
	 */
	public function get_bool_config_value( string $section, string $key, bool $default_value = false ): bool {
		$value = $this->get_config_value( $section, $key, $default_value );
		return is_bool( $value ) ? $value : $default_value;
	}

	/**
	 * Get an integer config value.
	 *
	 * @param string $section The section of the config.
	 * @param string $key The key of the config.
	 * @param int    $default_value The default value if the key is not found.
	 *
	 * @return int The config value.
	 */
	public function get_integer_config_value( string $section, string $key, int $default_value = 0 ): int {
		$value = $this->get_config_value( $section, $key, $default_value );
		return is_int( $value ) ? $value : $default_value;
	}

	/**
	 * Check if the logging is enabled.
	 *
	 * @return bool True if the logging is enabled, false otherwise.
	 */
	public function is_enabled(): bool {
		return $this->get_bool_config_value(
			Basic_Configuration_Tab::get_name(),
			Basic_Configuration_Tab::ENABLED,
			false
		);
	}

	/**
	 * Check if the logging is restricted to admins.
	 *
	 * @return bool True if the logging is restricted to admins, false otherwise.
	 */
	public function is_admin_restricted(): bool {
		return $this->get_bool_config_value(
			Basic_Configuration_Tab::get_name(),
			Basic_Configuration_Tab::ADMIN_USER_LOGGING,
			false
		);
	}

	/**
	 * Check if the logging has IP restrictions.
	 *
	 * @return bool True if the logging has IP restrictions, false otherwise.
	 */
	public function is_ip_restricted(): bool {
		$value = $this->get_string_config_value(
			Basic_Configuration_Tab::get_name(),
			Basic_Configuration_Tab::IP_RESTRICTIONS,
			''
		);

		return ! empty( $value );
	}

	/**
	 * Get the IP restrictions.
	 *
	 * @return array<string> The IP restrictions.
	 */
	public function get_ip_restrictions(): array {
		$ip_restrictions = $this->get_string_config_value(
			Basic_Configuration_Tab::get_name(),
			Basic_Configuration_Tab::IP_RESTRICTIONS,
			''
		);

		if ( empty( $ip_restrictions ) ) {
			return [];
		}

		$ip_list = explode( ',', $ip_restrictions );
		$ip_list = array_map(
			static function ( $ip ): string {
				$ip = trim( $ip );
				$ip = '' !== $ip ? \sanitize_text_field( $ip ) : '';
				return $ip;
			},
			$ip_list
		);

		$ip_list = array_values(
			array_filter(
				$ip_list,
				static function ( string $ip ): bool {
					return '' !== $ip && false !== filter_var( $ip, FILTER_VALIDATE_IP );
				}
			)
		);

		return $ip_list;
	}

	/**
	 * Check if the logging has query restrictions.
	 *
	 * @return bool The query restrictions, null if not set.
	 */
	public function has_query_restrictions(): bool {
		$value = $this->get_string_config_value(
			Basic_Configuration_Tab::get_name(),
			Basic_Configuration_Tab::WPGRAPHQL_FILTERING,
			''
		);

		return ! empty( $value );
	}

	/**
	 * Get the query restrictions.
	 *
	 * @return array<string> The query restrictions.
	 */
	public function get_query_restrictions(): array {
		$value = $this->get_string_config_value(
			Basic_Configuration_Tab::get_name(),
			Basic_Configuration_Tab::WPGRAPHQL_FILTERING,
			''
		);

		if ( empty( $value ) ) {
			return [];
		}

		return array_map(
			'trim',
			explode( ',', $value )
		);
	}

	/**
	 * Check if the performance metric is enabled.
	 *
	 * @return bool The performance metric, null if not set.
	 */
	public function has_performance_metric(): bool {
		$value = $this->get_string_config_value(
			Basic_Configuration_Tab::get_name(),
			Basic_Configuration_Tab::PERFORMANCE_METRICS,
			''
		);

		return ! empty( $value );
	}

	/**
	 * Get the performance metric threshold.
	 *
	 * @return float The performance metric threshold.
	 */
	public function get_performance_metric_threshold(): float {
		$metric = $this->get_string_config_value(
			Basic_Configuration_Tab::get_name(),
			Basic_Configuration_Tab::PERFORMANCE_METRICS,
			''
		);

		if ( empty( $metric ) || ! is_numeric( $metric ) ) {
			return self::DEFAULT_PERFORMANCE_METRIC_THRESHOLD;
		}

		return (float) trim( $metric );
	}

	/**
	 * Check if the data sampling is enabled.
	 *
	 * @return bool True if the data sampling is enabled, false otherwise.
	 */
	public function is_data_sampling_enabled(): bool {
		$value = $this->get_integer_config_value(
			Basic_Configuration_Tab::get_name(),
			Basic_Configuration_Tab::DATA_SAMPLING,
			self::DEFAULT_DATA_SAMPLING_PERCENTAGE
		);

		return $value > 0;
	}

	/**
	 * Get the data sampling percentage.
	 *
	 * @return int `The data sampling percentage.
	 */
	public function get_data_sampling_percentage(): int {
		return $this->get_integer_config_value(
			Basic_Configuration_Tab::get_name(),
			Basic_Configuration_Tab::DATA_SAMPLING,
			self::DEFAULT_DATA_SAMPLING_PERCENTAGE
		);
	}

	/**
	 * Setup the config settings.
	 */
	protected function setup(): void {
		$this->config = get_option( WPGRAPHQL_LOGGING_SETTINGS_KEY, [] );
	}
}
