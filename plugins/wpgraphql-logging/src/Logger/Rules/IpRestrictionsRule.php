<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Logger\Rules;

use WPGraphQL\Logging\Admin\Settings\Fields\Tab\BasicConfigurationTab;

/**
 * Rule to check if logging should occur based on IP restrictions.
 *
 * @package WPGraphQL\Logging
 *
 * @since 0.0.1
 */
class IpRestrictionsRule implements LoggingRuleInterface {
	/**
	 * Check if the rule passes.
	 *
	 * @param array<string, mixed> $config The logging configuration.
	 * @param string|null          $query_string The GraphQL query string.
	 *
	 * @return bool True if the rule passes (logging should continue).
	 */
	public function passes(array $config, ?string $query_string = null): bool {

		$ip_restrictions = $config[ BasicConfigurationTab::IP_RESTRICTIONS ] ?? '';
		if ( empty( $ip_restrictions ) ) {
			return true;
		}
		$allowed_ips = array_map( 'trim', explode( ',', $ip_restrictions ) );
		if ( ! isset( $_SERVER['REMOTE_ADDR'] ) ) { // @phpcs:ignore WordPressVIPMinimum.Variables.ServerVariables.UserControlledHeaders
			return false;
		}

		$remote_addr = filter_var( $_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP ); // @phpcs:ignore WordPressVIPMinimum.Variables.ServerVariables.UserControlledHeaders, WordPressVIPMinimum.Variables.RestrictedVariables.cache_constraints___SERVER__REMOTE_ADDR__
		if ( false === $remote_addr ) {
			return false;
		}
		return in_array( $remote_addr, $allowed_ips, true );
	}

	/**
	 * Get the rule name for debugging.
	 */
	public function get_name(): string {
		return 'ip_restrictions';
	}
}
