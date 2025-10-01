<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Tests\Logging\Rules;

use lucatume\WPBrowser\TestCase\WPTestCase;
use WPGraphQL\Logging\Logger\Rules\IpRestrictionsRule;
use WPGraphQL\Logging\Admin\Settings\Fields\Tab\BasicConfigurationTab;

/**
 * Test cases for the IpRestrictionsRule
 *
 * @package WPGraphQL\Logging
 *
 * @since 0.0.1
 */
class IpRestrictionsRuleTest extends WPTestCase {

	private IpRestrictionsRule $rule;

	public function setUp(): void {
		parent::setUp();
		$this->rule = new IpRestrictionsRule();
	}

	public function tearDown(): void {
		unset($_SERVER['REMOTE_ADDR']);
		parent::tearDown();
	}

	public function test_passes_when_no_ip_restrictions_configured(): void {
		$config = [];
		$this->assertTrue($this->rule->passes($config));
	}

	public function test_passes_when_empty_ip_restrictions_configured(): void {
		$config = [BasicConfigurationTab::IP_RESTRICTIONS => ''];
		$this->assertTrue($this->rule->passes($config));
	}

	public function test_fails_when_remote_addr_not_set(): void {
		$config = [BasicConfigurationTab::IP_RESTRICTIONS => '192.168.1.1'];
		unset($_SERVER['REMOTE_ADDR']);
		$this->assertFalse($this->rule->passes($config));
	}

	public function test_fails_when_invalid_ip_in_remote_addr(): void {
		$config = [BasicConfigurationTab::IP_RESTRICTIONS => '192.168.1.1'];
		$_SERVER['REMOTE_ADDR'] = 'invalid-ip';
		$this->assertFalse($this->rule->passes($config));
	}

	public function test_passes_when_ip_matches_single_allowed_ip(): void {
		$config = [BasicConfigurationTab::IP_RESTRICTIONS => '192.168.1.1'];
		$_SERVER['REMOTE_ADDR'] = '192.168.1.1';
		$this->assertTrue($this->rule->passes($config));
	}

	public function test_fails_when_ip_does_not_match_single_allowed_ip(): void {
		$config = [BasicConfigurationTab::IP_RESTRICTIONS => '192.168.1.1'];
		$_SERVER['REMOTE_ADDR'] = '192.168.1.2';
		$this->assertFalse($this->rule->passes($config));
	}

	public function test_passes_when_ip_matches_one_of_multiple_allowed_ips(): void {
		$config = [BasicConfigurationTab::IP_RESTRICTIONS => '192.168.1.1,10.0.0.1,172.16.0.1'];
		$_SERVER['REMOTE_ADDR'] = '10.0.0.1';
		$this->assertTrue($this->rule->passes($config));
	}

	public function test_fails_when_ip_does_not_match_any_allowed_ips(): void {
		$config = [BasicConfigurationTab::IP_RESTRICTIONS => '192.168.1.1,10.0.0.1,172.16.0.1'];
		$_SERVER['REMOTE_ADDR'] = '203.0.113.1';
		$this->assertFalse($this->rule->passes($config));
	}

	public function test_handles_whitespace_in_ip_list(): void {
		$config = [BasicConfigurationTab::IP_RESTRICTIONS => ' 192.168.1.1 , 10.0.0.1 , 172.16.0.1 '];
		$_SERVER['REMOTE_ADDR'] = '10.0.0.1';
		$this->assertTrue($this->rule->passes($config));
	}

	public function test_works_with_ipv6_addresses(): void {
		$config = [BasicConfigurationTab::IP_RESTRICTIONS => '::1,2001:db8::1'];
		$_SERVER['REMOTE_ADDR'] = '::1';
		$this->assertTrue($this->rule->passes($config));
	}

	public function test_get_name_returns_correct_identifier(): void {
		$this->assertEquals('ip_restrictions', $this->rule->get_name());
	}
}
