<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Logger\Api;

/**
 * Interface for logging rules.
 *
 * @package WPGraphQL\Logging
 *
 * @since 0.0.1
 */
interface LoggingRuleInterface {
	/**
	 * Check if the rule passes.
	 *
	 * @param array<string, mixed> $config The logging configuration.
	 * @param string|null          $query_string The GraphQL query string.
	 *
	 * @return bool True if the rule passes (logging should continue).
	 */
	public function passes(array $config, ?string $query_string = null): bool;

	/**
	 * Get the rule name for debugging.
	 */
	public function get_name(): string;
}
