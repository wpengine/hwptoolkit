<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Logger\Rules;

use WPGraphQL\Logging\Admin\Settings\Fields\Tab\BasicConfigurationTab;
use WPGraphQL\Logging\Logger\Api\LoggingRuleInterface;

/**
 * Rule to check if WordPress REST API request logging is enabled.
 *
 * @package WPGraphQL\Logging
 *
 * @since 1.1.0
 */
class LogRestRequestsRule implements LoggingRuleInterface {
	/**
	 * Check if the rule passes.
	 *
	 * @param array<string, mixed> $config The logging configuration.
	 * @param string|null          $query_string The REST route (unused).
	 *
	 * @return bool True if the rule passes (logging should continue).
	 */
	public function passes(array $config, ?string $query_string = null): bool {
		return (bool) ( $config[ BasicConfigurationTab::LOG_REST_REQUESTS ] ?? false );
	}

	/**
	 * Get the rule name for debugging.
	 */
	public function get_name(): string {
		return 'log_rest_requests_rule';
	}
}
