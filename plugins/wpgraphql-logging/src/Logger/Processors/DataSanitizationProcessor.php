<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Logger\Processors;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;
use WPGraphQL\Logging\Admin\Settings\ConfigurationHelper;
use WPGraphQL\Logging\Admin\Settings\Fields\Tab\DataManagementTab;

/**
 * This class is responsible for sanitizing data in log records
 *
 * @package WPGraphQL\Logging
 *
 * @since 0.0.1
 */
class DataSanitizationProcessor implements ProcessorInterface {
	/**
	 * The data management settings.
	 *
	 * @var array<string, string|int|bool|array<string>>
	 */
	protected array $config;

	/**
	 * DataSanitizationProcessor constructor.
	 */
	public function __construct() {
		$config_helper = ConfigurationHelper::get_instance();
		$this->config  = $config_helper->get_data_management_config();
	}

	/**
	 * Check if data sanitization is enabled.
	 */
	public function is_enabled(): bool {
		$is_enabled = (bool) ( $this->config[ DataManagementTab::DATA_SANITIZATION_ENABLED ] ?? false );
		return apply_filters( 'wpgraphql_logging_data_sanitization_enabled', $is_enabled );
	}

	/**
	 * Get the sanitization rules based on the settings.
	 *
	 * @return array<string, mixed> The sanitization rules.
	 */
	protected function get_rules(): array {
		$method = $this->config[ DataManagementTab::DATA_SANITIZATION_METHOD ] ?? 'none';


		if ( 'recommended' === $method ) {
			return apply_filters( 'wpgraphql_logging_data_sanitization_rules', $this->get_recommended_rules() );
		}

		return apply_filters( 'wpgraphql_logging_data_sanitization_rules', $this->get_custom_rules() );
	}

	/**
	 * Get the recommended sanitization rules.
	 *
	 * @return array<string, mixed> The recommended sanitization rules.
	 */
	protected function get_recommended_rules(): array {
		$rules = [
			'request.app_context.viewer.data'    => 'remove',
			'request.app_context.viewer.allcaps' => 'remove',
			'request.app_context.viewer.cap_key' => 'remove',
			'request.app_context.viewer.caps'    => 'remove',
			'variables.username'                 => 'anonymize',
			'variables.password'                 => 'anonymize',
			'variables.email'                    => 'anonymize',
			'params.username'                    => 'anonymize',
			'params.password'                    => 'anonymize',
			'params.email'                       => 'anonymize',
			'params.requests.*.body.username'    => 'anonymize', // Sub-requests of /batch/v1.
			'params.requests.*.body.password'    => 'anonymize',
			'params.requests.*.body.email'       => 'anonymize',
			'response.password'                  => 'anonymize', // e.g. a new REST application password.
			'response.responses.*.body.password' => 'anonymize', // Responses from /batch/v1.
			'response.body.password'             => 'anonymize', // Responses wrapped by ?_envelope.
		];

		return apply_filters( 'wpgraphql_logging_data_sanitization_recommended_rules', $rules );
	}

	/**
	 * Get the custom sanitization rules based on the settings.
	 *
	 * @return array<string, mixed> The custom sanitization rules.
	 */
	protected function get_custom_rules(): array {

		$rules  = [];
		$fields = [
			'anonymize' => $this->config[ DataManagementTab::DATA_SANITIZATION_CUSTOM_FIELD_ANONYMIZE ] ?? [],
			'remove'    => $this->config[ DataManagementTab::DATA_SANITIZATION_CUSTOM_FIELD_REMOVE ] ?? [],
			'truncate'  => $this->config[ DataManagementTab::DATA_SANITIZATION_CUSTOM_FIELD_TRUNCATE ] ?? [],
		];

		foreach ( $fields as $action => $field_string ) {
			if ( ! is_string( $field_string ) || '' === trim( $field_string ) ) {
				continue;
			}

			$field_string = trim( $field_string );
			$field_list   = array_filter(
				array_map( 'trim', explode( ',', $field_string ) ),
				static function ($value) {
					return '' !== $value;
				}
			);

			foreach ( $field_list as $field ) {
				$rules[ $field ] = $action;
			}
		}

		return $rules;
	}

	/**
	 * Apply a sanitization rule to a specific key in the data array.
	 *
	 * @param array<string, mixed> $data The data array to sanitize.
	 * @param string               $key The key to apply the rule to (dot notation for nested keys).
	 * @param string               $rule The sanitization rule ('anonymize', 'remove', 'truncate').
	 */
	protected function apply_rule(array &$data, string $key, string $rule): void {
		if ( empty( $data ) ) {
			return;
		}

		$this->apply_rule_to_path( $data, explode( '.', $key ), $rule );
	}

	/**
	 * Apply the sanitization rule to the target value.
	 *
	 * @param array<array-key, mixed> $current The array containing the target key (a list, when reached through a '*' path segment).
	 * @param string                  $key     The key to sanitize.
	 * @param string                  $rule    The sanitization rule to apply.
	 *
	 * @phpcs:disable Generic.Metrics.NestingLevel.TooHigh
	 */
	protected function apply_sanitization_rule(array &$current, string $key, string $rule): void {
		switch ( $rule ) {
			case 'anonymize':
				$current[ $key ] = '***';
				break;
			case 'remove':
				unset( $current[ $key ] );
				break;
			case 'truncate':
				if ( is_string( $current[ $key ] ) ) {
					// Truncate to 50 characters.
					$current[ $key ] = substr( $current[ $key ], 0, 47 ) . '...';
				}
				break;
		}
	}

	/**
	 * Apply a sanitization rule to a path of keys. A '*' segment matches every
	 * child array at that level, e.g. 'params.requests.*.body.password'.
	 *
	 * @param array<mixed>  $data The data array to sanitize.
	 * @param array<string> $keys The remaining path keys.
	 * @param string        $rule The sanitization rule ('anonymize', 'remove', 'truncate').
	 */
	private function apply_rule_to_path(array &$data, array $keys, string $rule): void {
		$segment = (string) array_shift( $keys );

		if ( [] === $keys ) {
			if ( array_key_exists( $segment, $data ) ) {
				$this->apply_sanitization_rule( $data, $segment, $rule );
			}
			return;
		}

		if ( '*' === $segment ) {
			$this->apply_wildcard_rule( $data, $keys, $rule );
			return;
		}

		if ( isset( $data[ $segment ] ) && is_array( $data[ $segment ] ) ) {
			$this->apply_rule_to_path( $data[ $segment ], $keys, $rule );
		}
	}

	/**
	 * Apply a sanitization rule to every child array of the given data.
	 *
	 * @param array<mixed>  $data The array whose children are matched by a '*' segment.
	 * @param array<string> $keys The path keys after the '*' segment.
	 * @param string        $rule The sanitization rule ('anonymize', 'remove', 'truncate').
	 */
	private function apply_wildcard_rule(array &$data, array $keys, string $rule): void {
		foreach ( array_keys( $data ) as $child_key ) {
			if ( is_array( $data[ $child_key ] ) ) {
				$this->apply_rule_to_path( $data[ $child_key ], $keys, $rule );
			}
		}
	}

	/**
	 * This method is called for each log record. It adds the captured
	 * request headers to the record's 'extra' array.
	 *
	 * @param \Monolog\LogRecord $record The log record to process.
	 *
	 * @return \Monolog\LogRecord The processed log record.
	 */
	public function __invoke( LogRecord $record ): LogRecord {

		if ( ! $this->is_enabled() ) {
			return $record;
		}

		$rules = $this->get_rules();

		if ( empty( $rules ) ) {
			return $record;
		}

		$context = $record->context;
		$extra   = $record->extra;
		foreach ( $rules as $key => $rule ) {
			$this->apply_rule( $context, $key, $rule );
			$this->apply_rule( $extra, $key, $rule );
		}

		$record = $record->with( context: $context, extra: $extra );
		return apply_filters( 'wpgraphql_logging_data_sanitization_record', $record );
	}
}
