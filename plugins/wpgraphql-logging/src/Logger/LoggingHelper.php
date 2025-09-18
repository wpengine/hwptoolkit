<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Logger;

use WPGraphQL\Logging\Logger\Rules\AdminUserRule;
use WPGraphQL\Logging\Logger\Rules\EnabledRule;
use WPGraphQL\Logging\Logger\Rules\IntrospectionQueryRule;
use WPGraphQL\Logging\Logger\Rules\IpRestrictionsRule;
use WPGraphQL\Logging\Logger\Rules\QueryNullRule;
use WPGraphQL\Logging\Logger\Rules\RuleManager;
use WPGraphQL\Logging\Logger\Rules\SamplingRateRule;
use WPGraphQL\Logging\Logger\Rules\SeedQueryRule;

/**
 * Trait for shared logging helper methods.
 *
 * @package WPGraphQL\Logging
 *
 * @since 0.0.1
 */
trait LoggingHelper {
	/**
	 * The rule manager instance.
	 *
	 * @var \WPGraphQL\Logging\Logger\Rules\RuleManager|null
	 */
	protected ?RuleManager $rule_manager = null;

	/**
	 * Get the rule manager, initializing it if necessary.
	 */
	protected function get_rule_manager(): RuleManager {
		if ( null !== $this->rule_manager ) {
			return $this->rule_manager;
		}
		$this->rule_manager = new RuleManager();
		$this->rule_manager->add_rule( new QueryNullRule() );
		$this->rule_manager->add_rule( new SamplingRateRule() );
		$this->rule_manager->add_rule( new EnabledRule() );
		$this->rule_manager->add_rule( new AdminUserRule() );
		$this->rule_manager->add_rule( new IpRestrictionsRule() );
		$this->rule_manager->add_rule( new IntrospectionQueryRule() );
		$this->rule_manager->add_rule( new SeedQueryRule() );
		apply_filters( 'wpgraphql_logging_rule_manager', $this->rule_manager );
		return $this->rule_manager;
	}

	/**
	 * Checks if logging is enabled based on user settings.
	 *
	 * @param array<string, mixed> $config The logging configuration.
	 */
	protected function is_logging_enabled( array $config, ?string $query_string = null ): bool {

		$is_enabled = $this->get_rule_manager()->all_rules_pass( $config, $query_string );

		/**
		 * Filter the final decision on whether to log a request.
		 *
		 * @param bool                  $is_enabled True if logging is enabled, false otherwise.
		 * @param array<string, mixed>  $config     The current logging configuration.
		 */
		return apply_filters( 'wpgraphql_logging_is_enabled', $is_enabled, $config );
	}
}
