<?php

declare(strict_types=1);


namespace WPGraphQL\Logging\Tests\Logging\Rules;

use lucatume\WPBrowser\TestCase\WPTestCase;
use WPGraphQL\Logging\Admin\Settings\Fields\Tab\BasicConfigurationTab;
use WPGraphQL\Logging\Logger\Rules\AdminUserRule;


/**
 * Test class for AdminUserRule.
 *
 * @package WPGraphQL\Logging
 *
 * @since 0.0.1
 */
class AdminUserRuleTest extends WPTestCase {

	private AdminUserRule $rule;

	public function setUp(): void {
		parent::setUp();
		$this->rule = new AdminUserRule();
	}

	public function set_admin_user() {
		$user_id = $this->factory()->user->create(['role' => 'administrator']);
		wp_set_current_user($user_id);
	}

	public function test_get_name_returns_correct_name(): void {
		$this->assertEquals('admin_user_rule', $this->rule->get_name());
	}

	public function test_passes_when_admin_user_logging_disabled(): void {
		$config = [
			BasicConfigurationTab::ADMIN_USER_LOGGING => false,
		];

		$this->assertTrue($this->rule->passes($config));
	}

	public function test_passes_when_admin_user_logging_config_missing(): void {
		$config = [];

		$this->assertTrue($this->rule->passes($config));
	}

	public function test_passes_when_admin_user_logging_enabled_and_user_can_manage_options(): void {
		$this->set_admin_user();

		$config = [
			BasicConfigurationTab::ADMIN_USER_LOGGING => true,
		];

		$this->assertTrue($this->rule->passes($config));


		$config = [
			BasicConfigurationTab::ADMIN_USER_LOGGING => false,
		];

		$this->assertTrue($this->rule->passes($config));
	}

	public function test_fails_when_admin_user_logging_enabled_and_user_cannot_manage_options(): void {
		$user_id = $this->factory()->user->create(['role' => 'subscriber']);
		wp_set_current_user($user_id);

		$config = [
			BasicConfigurationTab::ADMIN_USER_LOGGING => true,
		];

		$this->assertFalse($this->rule->passes($config));
	}

	public function test_fails_when_admin_user_logging_enabled_and_no_user_logged_in(): void {
		wp_set_current_user(0);

		$config = [
			BasicConfigurationTab::ADMIN_USER_LOGGING => true,
		];

		$this->assertFalse($this->rule->passes($config));
	}

	public function test_passes_with_query_string_parameter(): void {
		$config = [
			BasicConfigurationTab::ADMIN_USER_LOGGING => false,
		];

		$this->assertTrue($this->rule->passes($config, 'query { posts { id } }'));
	}
}
