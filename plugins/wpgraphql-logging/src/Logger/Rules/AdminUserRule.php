<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Logger\Rules;

use WPGraphQL\Logging\Admin\Settings\Fields\Tab\Basic_Configuration_Tab;

/**
 * Rule to check if logging should occur based on admin user setting.
 *
 * @package WPGraphQL\Logging
 *
 * @since 0.0.1
 */
class AdminUserRule implements LoggingRuleInterface {
	/**
	 * Check if the rule passes.
	 *
	 * @param array<string, mixed> $config The logging configuration.
	 * @param string|null          $query_string The GraphQL query string.
	 *
	 * @return bool True if the rule passes (logging should continue).
	 */
	public function passes(array $config, ?string $query_string = null): bool {

		$is_admin_user = (bool) ( $config[ Basic_Configuration_Tab::ADMIN_USER_LOGGING ] ?? false );
		if ( ! $is_admin_user ) {
			return true;
		}

		return current_user_can( 'manage_options' );
	}

	/**
	 * Get the rule name for debugging.
	 */
	public function get_name(): string {
		return 'admin_user_rule';
	}
}
