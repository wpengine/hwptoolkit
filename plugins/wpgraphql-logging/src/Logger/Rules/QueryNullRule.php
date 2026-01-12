<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Logger\Rules;

use WPGraphQL\Logging\Logger\Api\LoggingRuleInterface;

/**
 * Rule to check if logging should occur based on query null setting.
 *
 * @package WPGraphQL\Logging
 *
 * @since 0.0.1
 */
class QueryNullRule implements LoggingRuleInterface {
	/**
	 * Check if the rule passes.
	 *
	 * @param array<string, mixed> $config The logging configuration.
	 * @param string|null          $query_string The GraphQL query string.
	 *
	 * @return bool True if the rule passes (logging should continue).
	 */
	public function passes(array $config, ?string $query_string = null): bool {
		return is_string( $query_string ) && '' !== trim( $query_string );
	}

	/**
	 * Get the rule name for debugging.
	 */
	public function get_name(): string {
		return 'query_null_rule';
	}
}
