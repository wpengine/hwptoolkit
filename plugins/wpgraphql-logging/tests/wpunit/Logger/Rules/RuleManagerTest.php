<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Tests\Logging\Rules;

use WPGraphQL\Logging\Logger\Rules\RuleManager;
use WPGraphQL\Logging\Logger\Api\LoggingRuleInterface;
use lucatume\WPBrowser\TestCase\WPTestCase;

/**
 * Test the RuleManager class.
 *
 * @package WPGraphQL\Logging
 *
 * @since 0.0.1
 */
class RuleManagerTest extends WPTestCase {

	private RuleManager $rule_manager;

	public function setUp(): void {
		parent::setUp();
		$this->rule_manager = new RuleManager();
	}

	public function test_add_rule(): void {
		$rule = $this->createMock(LoggingRuleInterface::class);
		$rule->method('get_name')->willReturn('test_rule');

		$this->rule_manager->add_rule($rule);

		// Test that all_rules_pass works with the added rule
		$rule->method('passes')->willReturn(true);
		$this->assertTrue($this->rule_manager->all_rules_pass([]));
	}

	public function test_all_rules_pass_with_no_rules(): void {
		$this->assertTrue($this->rule_manager->all_rules_pass([]));
	}

	public function test_all_rules_pass_with_single_passing_rule(): void {
		$rule = $this->createMock(LoggingRuleInterface::class);
		$rule->method('get_name')->willReturn('passing_rule');
		$rule->method('passes')->willReturn(true);

		$this->rule_manager->add_rule($rule);

		$this->assertTrue($this->rule_manager->all_rules_pass(['key' => 'value']));
	}

	public function test_all_rules_pass_with_single_failing_rule(): void {
		$rule = $this->createMock(LoggingRuleInterface::class);
		$rule->method('get_name')->willReturn('failing_rule');
		$rule->method('passes')->willReturn(false);

		$this->rule_manager->add_rule($rule);

		$this->assertFalse($this->rule_manager->all_rules_pass(['key' => 'value']));
	}

	public function test_all_rules_pass_with_multiple_passing_rules(): void {
		$rule1 = $this->createMock(LoggingRuleInterface::class);
		$rule1->method('get_name')->willReturn('rule_1');
		$rule1->method('passes')->willReturn(true);

		$rule2 = $this->createMock(LoggingRuleInterface::class);
		$rule2->method('get_name')->willReturn('rule_2');
		$rule2->method('passes')->willReturn(true);

		$this->rule_manager->add_rule($rule1);
		$this->rule_manager->add_rule($rule2);

		$this->assertTrue($this->rule_manager->all_rules_pass(['key' => 'value']));
	}

	public function test_all_rules_pass_with_mixed_rules(): void {
		$passing_rule = $this->createMock(LoggingRuleInterface::class);
		$passing_rule->method('get_name')->willReturn('passing_rule');
		$passing_rule->method('passes')->willReturn(true);

		$failing_rule = $this->createMock(LoggingRuleInterface::class);
		$failing_rule->method('get_name')->willReturn('failing_rule');
		$failing_rule->method('passes')->willReturn(false);

		$this->rule_manager->add_rule($passing_rule);
		$this->rule_manager->add_rule($failing_rule);

		$this->assertFalse($this->rule_manager->all_rules_pass(['key' => 'value']));
	}

	public function test_all_rules_pass_with_query_string(): void {
		$rule = $this->createMock(LoggingRuleInterface::class);
		$rule->method('get_name')->willReturn('query_rule');
		$rule->expects($this->once())
			->method('passes')
			->with(['key' => 'value'], 'query { user { name } }')
			->willReturn(true);

		$this->rule_manager->add_rule($rule);

		$this->assertTrue($this->rule_manager->all_rules_pass(['key' => 'value'], 'query { user { name } }'));
	}

}
