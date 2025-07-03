<?php

namespace WPGraphQL\Velocity\Rules\Rule;

use WPGraphQL\Velocity\Data\DataSourceInterface;
use WPGraphQL\Velocity\Rules\RuleInterface;
use WPGraphQL\Velocity\Rules\RuleResult;

class ComplexityRule implements RuleInterface
{

	public function analyze( DataSourceInterface $dataSource ): RuleResult {
		// @TODO
		// Note the logic here is not impletemented yet, this is just a stub for the rule engine
		return new RuleResult(
			$this->get_id(),
			'error', // Level can be 'info', 'warning', 'error'
			'This query is complex and may take a long time to execute.',
			[
				// Metadata can include additional information about the rule
				'complexity_threshold' => 1000, // Example threshold for complexity
			]
		);

	}

	public function is_applicable( DataSourceInterface $dataSource ): bool {
		// TODO abstract method
		return $dataSource->get_type() === 'sql_analysis';
	}

	public function get_id(): string {
		return 'query_complexity_rule';
	}

	public function get_priority(): int {
		// TODO: Implement get_priority() method.
		return 1;
	}
}
