<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Logger\Rules;

use WPGraphQL\Logging\Admin\Settings\Fields\Tab\BasicConfigurationTab;
use WPGraphQL\Logging\Logger\Api\LoggingRuleInterface;

/**
 * Rule to check if logging is enabled.
 *
 * @package WPGraphQL\Logging
 *
 * @since 0.0.1
 */
class SamplingRateRule implements LoggingRuleInterface {
	/**
	 * Check if the rule passes.
	 *
	 * @param array<string, mixed> $config The logging configuration.
	 * @param string|null          $query_string The GraphQL query string.
	 *
	 * @return bool True if the rule passes (logging should continue).
	 */
	public function passes(array $config, ?string $query_string = null): bool {
		$sampling_rate = (int) ( $config[ BasicConfigurationTab::DATA_SAMPLING ] ?? 100 );
		// If sampling rate is 0, never log.
		if ( 1 > $sampling_rate ) {
			return false;
		}

		$rand = wp_rand( 1, 100 );
		return $rand <= $sampling_rate;
	}

	/**
	 * Get the rule name for debugging.
	 */
	public function get_name(): string {
		return 'sampling_rate_rule';
	}
}
