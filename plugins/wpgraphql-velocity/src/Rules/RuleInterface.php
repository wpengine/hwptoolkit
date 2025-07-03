<?php

namespace WPGraphQL\Velocity\Rules;

use WPGraphQL\Velocity\Data\DataSourceInterface;

interface RuleInterface
{
	/**
	 * Analyze the data source and return recommendations
	 *
	 * @param DataSource $dataSource The data source to analyze
	 * @return RuleResult The analysis result with recommendations
	 */
	public function analyze(DataSourceInterface $dataSource): RuleResult;

	/**
	 * Check if this rule should be applied to the given data source
	 *
	 * @param DataSource $dataSource
	 * @return bool
	 */
	public function is_applicable(DataSourceInterface $dataSource): bool;

	/**
	 * Get the unique identifier for this rule
	 *
	 * @return string
	 */
	public function get_id(): string;

	/**
	 * Get the priority for this rule (lower number = higher priority)
	 *
	 * @return int
	 */
	public function get_priority(): int;
}
