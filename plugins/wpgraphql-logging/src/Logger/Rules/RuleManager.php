<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Logger\Rules;

/**
 * Manages a set of logging rules and checks if all pass.
 *
 * @package WPGraphQL\Logging
 *
 * @since 0.0.1
 */
class RuleManager {
	/** @var array<\WPGraphQL\Logging\Logger\Rules\LoggingRuleInterface> */
	private array $rules = [];

	/**
	 * Add a rule to the manager.
	 */
	public function add_rule(LoggingRuleInterface $rule): void {
		$this->rules[ $rule->get_name() ] = $rule;
	}

	/**
	 * Check if all rules pass.
	 *
	 * @param array<string, mixed> $config
	 * @param string|null          $query_string
	 */
	public function all_rules_pass(array $config, ?string $query_string = null): bool {
		foreach ( $this->rules as $rule ) {
			if ( ! $rule->passes( $config, $query_string ) ) {
				return false;
			}
		}
		return true;
	}
}
