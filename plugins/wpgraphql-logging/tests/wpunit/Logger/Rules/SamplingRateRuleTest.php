<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Tests\Logging\Rules;


use lucatume\WPBrowser\TestCase\WPTestCase;
use WPGraphQL\Logging\Logger\Rules\SamplingRateRule;
use WPGraphQL\Logging\Admin\Settings\Fields\Tab\BasicConfigurationTab;

/**
 * Test cases for the SamplingRateRule
 *
 * @package WPGraphQL\Logging
 *
 * @since 0.0.1
 */
class SamplingRateRuleTest extends WPTestCase {

	private SamplingRateRule $rule;

	public function setUp(): void {
		parent::setUp();
		$this->rule = new SamplingRateRule();
	}

	public function test_get_name_returns_correct_name(): void {
		$this->assertEquals('sampling_rate_rule', $this->rule->get_name());
	}

	public function test_passes_with_100_percent_sampling_rate(): void {
		$config = [
			BasicConfigurationTab::DATA_SAMPLING => 100,
		];

		$result = $this->rule->passes($config);
		$this->assertTrue($result);
	}

	public function test_passes_with_0_percent_sampling_rate(): void {
		$config = [
			BasicConfigurationTab::DATA_SAMPLING => 0,
		];

		$result = $this->rule->passes($config);
		$this->assertFalse($result);
	}

	public function test_passes_with_default_sampling_rate_when_not_set(): void {
		$config = [];

		// Since default is 100, it should always pass
		$result = $this->rule->passes($config);
		$this->assertTrue($result);
	}

	public function test_passes_with_string_sampling_rate(): void {
		$config = [
			BasicConfigurationTab::DATA_SAMPLING => '50',
		];

		$result = $this->rule->passes($config);
		$this->assertIsBool($result);
	}

	public function test_passes_with_null_query_string(): void {
		$config = [
			BasicConfigurationTab::DATA_SAMPLING => 100,
		];

		$result = $this->rule->passes($config, null);
		$this->assertTrue($result);
	}

	public function test_passes_with_query_string(): void {
		$config = [
			BasicConfigurationTab::DATA_SAMPLING => 100,
		];

		$result = $this->rule->passes($config, 'query { posts { id } }');
		$this->assertTrue($result);
	}
}
