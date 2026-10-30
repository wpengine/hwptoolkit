<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Logger\Rules;

use WPGraphQL\Logging\Admin\Settings\Fields\Tab\BasicConfigurationTab;
use WPGraphQL\Logging\Logger\Api\LoggingRuleInterface;

/**
 * Rule to check if a WordPress REST API route is excluded from logging.
 *
 * Each configured entry matches that route and everything below it, so
 * `/wp/v2/users` also excludes `/wp/v2/users/1` and `/wp/v2/users/me`, but
 * not `/wp/v2/users-extra`. Matching is case-insensitive, like WordPress
 * REST route matching.
 *
 * @package WPGraphQL\Logging
 *
 * @since 1.1.0
 */
class ExcludeRestRouteRule implements LoggingRuleInterface {
	/**
	 * Check if the rule passes.
	 *
	 * @param array<string, mixed> $config The logging configuration.
	 * @param string|null          $query_string The REST route, e.g. `/wp/v2/posts`.
	 *
	 * @return bool True if the rule passes (logging should continue).
	 */
	public function passes(array $config, ?string $query_string = null): bool {
		$routes = $config[ BasicConfigurationTab::EXCLUDE_REST_ROUTES ] ?? '';
		if ( null === $query_string || ! is_string( $routes ) || '' === trim( $routes ) ) {
			return true;
		}

		$route           = $this->normalize_route( $query_string );
		$excluded_routes = array_filter( array_map( 'trim', explode( ',', $routes ) ), static fn( string $value ): bool => '' !== $value );
		foreach ( $excluded_routes as $excluded_route ) {
			if ( $this->matches( $route, $this->normalize_route( $excluded_route ) ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Get the rule name for debugging.
	 */
	public function get_name(): string {
		return 'exclude_rest_route_rule';
	}

	/**
	 * Whether a route is the excluded route or sits below it.
	 *
	 * @param string $route          The normalized request route.
	 * @param string $excluded_route The normalized excluded route.
	 */
	protected function matches( string $route, string $excluded_route ): bool {
		return $route === $excluded_route || str_starts_with( $route, rtrim( $excluded_route, '/' ) . '/' );
	}

	/**
	 * Normalize a route to a lowercase path with one leading slash and no trailing slash.
	 *
	 * @param string $route The route to normalize.
	 */
	protected function normalize_route( string $route ): string {
		return '/' . strtolower( trim( $route, '/' ) );
	}
}
