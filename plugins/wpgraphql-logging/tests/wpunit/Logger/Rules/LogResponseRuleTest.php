<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Tests\Logging\Rules;

use lucatume\WPBrowser\TestCase\WPTestCase;
use WPGraphQL\Logging\Admin\Settings\Fields\Tab\BasicConfigurationTab;
use WPGraphQL\Logging\Logger\Rules\LogResponseRule;

/**
 * Test cases for the LogResponseRule
 *
 * @package WPGraphQL\Logging
 *
 * @since 0.0.1
 */
class LogResponseRuleTest extends WPTestCase {

	private LogResponseRule $rule;

	public function setUp(): void {
		parent::setUp();
		$this->rule = new LogResponseRule();
	}

	public function testPassesReturnsTrueWhenLogResponseIsEnabled(): void {
		$config = [
			BasicConfigurationTab::LOG_RESPONSE => true,
		];

		$result = $this->rule->passes($config);

		$this->assertTrue($result);
	}

	public function testPassesReturnsFalseWhenLogResponseIsDisabled(): void {
		$config = [
			BasicConfigurationTab::LOG_RESPONSE => false,
		];

		$result = $this->rule->passes($config);

		$this->assertFalse($result);
	}

	public function testPassesReturnsFalseWhenLogResponseIsNotSet(): void {
		$config = [];

		$result = $this->rule->passes($config);

		$this->assertFalse($result);
	}

	public function testPassesCastsValueToBoolean(): void {
		$config = [
			BasicConfigurationTab::LOG_RESPONSE => 1,
		];

		$result = $this->rule->passes($config);

		$this->assertTrue($result);
	}

	public function testPassesIgnoresQueryStringParameter(): void {
		$config = [
			BasicConfigurationTab::LOG_RESPONSE => true,
		];

		$result = $this->rule->passes($config, 'query { posts { id } }');

		$this->assertTrue($result);
	}

	public function testGetNameReturnsCorrectString(): void {
		$result = $this->rule->get_name();

		$this->assertEquals('log_response_rule', $result);
	}
}
