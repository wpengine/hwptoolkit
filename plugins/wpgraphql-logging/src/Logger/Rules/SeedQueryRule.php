<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Logger\Rules;

use WPGraphQL\Logging\Admin\Settings\Fields\Tab\Basic_Configuration_Tab;

/**
 * Rule to check if the query is a Faust.js Seed Query.
 *
 * @package WPGraphQL\Logging
 *
 * @link https://github.com/wpengine/faustjs/blob/8e8797b1758d34d489236266e08f0657c015ff9f/packages/faustwp-core/src/queries/seedQuery.ts
 *
 * @since 0.0.1
 */
class SeedQueryRule implements LoggingRuleInterface {
	/**
	 * Check if the rule passes.
	 *
	 * @param array<string, mixed> $config The logging configuration.
	 * @param string|null          $query_string The GraphQL query string.
	 *
	 * @return bool True if the rule passes (logging should continue).
	 */
	public function passes(array $config, ?string $query_string = null): bool {

		if ( null === $query_string ) {
			return true;
		}

		$allow_query = (bool) $config[ Basic_Configuration_Tab::SEED_QUERY ];
		if ( ! $allow_query ) {
			return true;
		}
		return stripos( $query_string, 'SeedNode' ) === false;
	}

	/**
	 * Get the rule name for debugging.
	 */
	public function get_name(): string {
		return 'faustjs_seed_query_rule';
	}
}
