<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Logger;

use WPGraphQL\Logging\Admin\Settings\Fields\Tab\BasicConfigurationTab;
use WPGraphQL\Logging\Logger\Rules\AdminUserRule;
use WPGraphQL\Logging\Logger\Rules\EnabledRule;
use WPGraphQL\Logging\Logger\Rules\ExcludeQueryRule;
use WPGraphQL\Logging\Logger\Rules\IpRestrictionsRule;
use WPGraphQL\Logging\Logger\Rules\LogResponseRule;
use WPGraphQL\Logging\Logger\Rules\QueryNullRule;
use WPGraphQL\Logging\Logger\Rules\RuleManager;
use WPGraphQL\Logging\Logger\Rules\SamplingRateRule;

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
	 * Determines if the response should be logged based on the configuration.
	 *
	 * @param array<string, mixed> $config The logging configuration.
	 *
	 * @return bool True if the response should be logged, false otherwise.
	 */
	public function should_log_response(array $config): bool {
		$rule = new LogResponseRule();
		return $rule->passes( $config );
	}

	/**
	 * Determine if the event should be logged based on the configuration and selected events.
	 *
	 * @param string      $event The event name.
	 * @param string|null $query The GraphQL query (optional).
	 *
	 * @return bool True if the event should be logged, false otherwise.
	 */
	public function should_log_event(string $event, ?string $query = null): bool {
		if ( ! $this->is_logging_enabled( $this->config, $query ) ) {
			return false;
		}
		return $this->is_selected_event( $event );
	}

	/**
	 * Check if the event is selected in the configuration.
	 *
	 * @param string $event The event name to check.
	 *
	 * @return bool True if the event is selected, false otherwise.
	 */
	public function is_selected_event(string $event): bool {
		$selected_events = $this->config[ BasicConfigurationTab::EVENT_LOG_SELECTION ] ?? [];
		if ( ! is_array( $selected_events ) || empty( $selected_events ) ) {
			return false;
		}
		return in_array( $event, $selected_events, true );
	}

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
		$this->rule_manager->add_rule( new ExcludeQueryRule() );
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

	/**
	 * Handles and logs application errors.
	 *
	 * @param string     $event
	 * @param \Throwable $exception
	 */
	protected function process_application_error( string $event, \Throwable $exception ): void {
        error_log( 'Error for WPGraphQL Logging - ' . $event . ': ' . $exception->getMessage() . ' in ' . $exception->getFile() . ' on line ' . $exception->getLine() ); //phpcs:ignore
	}
}
