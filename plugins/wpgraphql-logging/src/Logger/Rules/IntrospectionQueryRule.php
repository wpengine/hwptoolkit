<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Logger\Rules;

/**
 * Rule to check if the query is an introspection query.
 *
 * @package WPGraphQL\Logging
 *
 * @since 0.0.1
 */
class IntrospectionQueryRule implements LoggingRuleInterface {
	/**
	 * Check if the rule passes.
	 *
	 * @param array<string, mixed> $config The logging configuration.
	 * @param string|null          $query_string The GraphQL query string.
	 *
	 * @return bool True if the rule passes (logging should continue).
	 */
	public function passes(array $config, ?string $query_string = null): bool {
		return strpos( $query_string, '__schema' ) === false;
	}

	/**
	 * Get the rule name for debugging.
	 */
	public function get_name(): string {
		return 'introspection_query_rule';
	}
}
