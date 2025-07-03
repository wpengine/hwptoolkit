<?php

namespace WPGraphQL\Velocity\Analysis;


use WPGraphQL\Velocity\Data\DataSourceInterface;
use WPGraphQL\Velocity\Rules\Rule\ComplexityRule;

class RuleEngineService
{

	protected array $rules = [];
	public function __construct() {
		$this->setup();
	}

	public function setup() {

		// @TODO make more OOP
		$rules = $this->rules;
		$rules['complexity'] = new ComplexityRule();

		$this->rules = apply_filters('wpgraphql_velocity_rule_engine_rules', $rules);
	}


	/**
	 * Analyze the given data source and populate the analysis array.
	 *
	 * @param DataSourceInterface $data_source
	 * @param array $analysis
	 * @return array
	 */
	public function analyze(DataSourceInterface $data_source, array &$analysis): array
	{
		// @TODO add action to allow for custom rules to be added
		foreach ($this->rules as $rule) {
			$rule_result = $rule->analyze($data_source);
			$analysis[$rule->get_id()] = $rule_result->get_data();
		}

		return $analysis;
	}
}
