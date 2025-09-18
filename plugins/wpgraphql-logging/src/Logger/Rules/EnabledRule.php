<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Logger\Rules;

use WPGraphQL\Logging\Admin\Settings\Fields\Tab\Basic_Configuration_Tab;

/**
 * Rule to check if logging is enabled.
 *
 * @package WPGraphQL\Logging
 *
 * @since 0.0.1
 */
class EnabledRule implements LoggingRuleInterface {
	/**
	 * Check if the rule passes.
	 *
	 * @param array<string, mixed> $config The logging configuration.
	 * @param string|null          $query_string The GraphQL query string.
	 *
	 * @return bool True if the rule passes (logging should continue).
	 */
	public function passes(array $config, ?string $query_string = null): bool {
		return (bool) ( $config[ Basic_Configuration_Tab::ENABLED ] ?? false );
	}

	/**
	 * Get the rule name for debugging.
	 */
	public function get_name(): string {
		return 'enabled_rule';
	}
}
