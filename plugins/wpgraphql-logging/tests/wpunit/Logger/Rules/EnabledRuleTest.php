<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Tests\Logging\Rules;

use lucatume\WPBrowser\TestCase\WPTestCase;
use WPGraphQL\Logging\Logger\Rules\EnabledRule;
use WPGraphQL\Logging\Admin\Settings\Fields\Tab\BasicConfigurationTab;

/**
 * Test cases for the EnabledRule
 *
 * @package WPGraphQL\Logging
 *
 * @since 0.0.1
 */
class EnabledRuleTest extends WPTestCase {

	private EnabledRule $rule;

	public function setUp(): void {
		parent::setUp();
		$this->rule = new EnabledRule();
	}

	public function test_get_name_returns_correct_name(): void {
		$name = $this->rule->get_name();

		$this->assertSame('enabled_rule', $name);
	}

	public function test_passes_when_enabled_is_true(): void {
		$config = [
			BasicConfigurationTab::ENABLED => true,
		];

		$result = $this->rule->passes($config);

		$this->assertTrue($result);
	}

	public function test_passes_when_enabled_is_false(): void {
		$config = [
			BasicConfigurationTab::ENABLED => false,
		];

		$result = $this->rule->passes($config);

		$this->assertFalse($result);
	}

	public function test_passes_when_enabled_key_missing(): void {
		$config = [];

		$result = $this->rule->passes($config);

		$this->assertFalse($result);
	}

	public function test_passes_with_truthy_values(): void {
		$config = [
			BasicConfigurationTab::ENABLED => 1,
		];

		$result = $this->rule->passes($config);

		$this->assertTrue($result);
	}

	public function test_passes_with_falsy_values(): void {
		$config = [
			BasicConfigurationTab::ENABLED => 0,
		];

		$result = $this->rule->passes($config);

		$this->assertFalse($result);
	}

	public function test_passes_ignores_query_string(): void {
		$config = [
			BasicConfigurationTab::ENABLED => true,
		];

		$result = $this->rule->passes($config, 'query { posts { id } }');

		$this->assertTrue($result);
	}
}
