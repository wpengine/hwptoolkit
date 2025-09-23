<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Logger\Rules;

use WPGraphQL\Logging\Admin\Settings\Fields\Tab\BasicConfigurationTab;

/**
 * Rule to check if the query is excluded from logging.
 *
 * @package WPGraphQL\Logging
 *
 * @since 0.0.1
 */
class ExcludeQueryRule implements LoggingRuleInterface {
	/**
	 * Check if the rule passes.
	 *
	 * @param array<string, mixed> $config The logging configuration.
	 * @param string|null          $query_string The GraphQL query string.
	 *
	 * @return bool True if the rule passes (logging should continue).
	 */
	public function passes(array $config, ?string $query_string = null): bool {
		$queries = $config[ BasicConfigurationTab::EXCLUDE_QUERY ] ?? '';
		if ( null === $query_string ) {
			return true;
		}

		$excluded_queries = array_map( 'trim', explode( ',', $queries ) );
		foreach ( $excluded_queries as $excluded_query ) {
			if ( stripos( $query_string, $excluded_query ) !== false ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Get the rule name for debugging.
	 */
	public function get_name(): string {
		return 'exclude_query_rule';
	}
}
