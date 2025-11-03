<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Tests\Admin\View;


use WPGraphQL\Logging\Admin\ViewLogsPage;
use Codeception\TestCase\WPTestCase;
use Brain\Monkey;
use WPGraphQL\Logging\Logger\Api\LogServiceInterface;

/**
 * Test for the ViewLogsPage
 *
 * @package WPGraphQL\Logging
 * @since 0.0.1
 */
class ViewLogsPageTest extends WPTestCase {


	protected function setUp(): void {
		parent::setUp();

		// Reset the singleton instance before each test
		$reflection = new \ReflectionClass(ViewLogsPage::class);
		$instance = $reflection->getProperty('instance');
		$instance->setAccessible(true);
		$instance->setValue(null);
	}

	protected function tearDown(): void {
		parent::tearDown();

		// Clean up singleton instance
		$reflection = new \ReflectionClass(ViewLogsPage::class);
		$instance = $reflection->getProperty('instance');
		$instance->setAccessible(true);
		$instance->setValue(null);
	}

	public function set_as_admin(): void {
		$admin_user = $this->factory->user->create(['role' => 'administrator']);
		wp_set_current_user($admin_user);
		set_current_screen('dashboard');
	}


	public function test_init_returns_null_when_user_cannot_manage_options(): void {
		// Mock user without permissions
		wp_set_current_user(0);

		$result = ViewLogsPage::init();

		$this->assertNull($result);
	}

	public function test_init_returns_same_instance_on_multiple_calls(): void {
		$this->set_as_admin();
		$instance1 = ViewLogsPage::init();
		$instance2 = ViewLogsPage::init();

		$this->assertSame($instance1, $instance2);
	}

	public function test_enqueue_admin_scripts_styles_only_on_correct_page(): void {
		$this->set_as_admin();
		$instance = ViewLogsPage::init();

		// Test with wrong hook suffix
		$instance->enqueue_admin_scripts_styles('different-page');
		$this->assertFalse(wp_script_is('jquery-ui-datepicker', 'enqueued'));

		// Test with correct hook suffix (simulate the page hook)
		$reflection = new \ReflectionClass($instance);
		$pageHookProperty = $reflection->getProperty('page_hook');
		$pageHookProperty->setAccessible(true);
		$pageHookProperty->setValue($instance, 'test-page-hook');

		$instance->enqueue_admin_scripts_styles('test-page-hook');
		$this->assertTrue(wp_script_is('jquery-ui-datepicker', 'enqueued'));
		$this->assertTrue(wp_script_is('jquery-ui-slider', 'enqueued'));
	}

	public function test_get_post_value_returns_null_for_missing_key(): void {
		$this->set_as_admin();
		$instance = ViewLogsPage::init();
		$reflection = new \ReflectionClass($instance);
		$method = $reflection->getMethod('get_post_value');
		$method->setAccessible(true);

		$result = $method->invoke($instance, 'nonexistent_key');

		$this->assertNull($result);
	}

	public function test_register_page() : void {
		$this->set_as_admin();
		$instance = ViewLogsPage::init();
		$instance->register_settings_page();

		global $menu;
		$found = false;
		foreach ($menu as $item) {
			if ($item[2] === ViewLogsPage::ADMIN_PAGE_SLUG) {
				$found = true;
				break;
			}
		}

		$this->assertTrue($found, 'Admin menu should contain the GraphQL Logs page');
	}

	public function test_register_admin_page() : void {
		$this->set_as_admin();
		$instance = ViewLogsPage::init();
		$instance->register_settings_page();

		// Default
		ob_start();
		$instance->render_admin_page();
		$output = ob_get_clean();

		$this->assertStringContainsString('<h1 class="wp-heading-inline">WPGraphQL Logs</h1>', $output);
	}


	public function test_process_page_actions_before_rendering_as_download_action() : void {
		$this->set_as_admin();
		$instance = ViewLogsPage::init();

		// Test download action
		$_GET['action'] = 'download';
		$_GET['log'] = 'nonexistent-log-id';

		ob_start();
		$this->expectException(\WPDieException::class);
		$this->expectExceptionMessage('The link you followed has expired.');
		$instance->process_page_actions_before_rendering();
		$output = ob_get_clean();

	}


	public function test_process_page_actions_before_rendering_as_filter_action() : void {
		$this->set_as_admin();
		$instance = ViewLogsPage::init();
		// Test process_filters_redirect
		$_GET['action'] = 'filter';
		$_GET['log'] = 'nonexistent-log-id';
		ob_start();
		$instance->process_page_actions_before_rendering();
		$output = ob_get_clean();
		$this->assertEquals('', $output);

		// Clean up
		unset($_GET['action'], $_GET['log']);
	}

	public function test_process_filters_redirect_with_invalid_nonce(): void {
		$this->set_as_admin();
		$instance = ViewLogsPage::init();

		// Simulate POST request with invalid nonce
		$_POST['wpgraphql_logging_nonce'] = 'invalid_nonce';

		ob_start();
		$instance->process_filters_redirect();
		$output = ob_get_clean();

		$this->assertEquals('', $output);

		// Clean up
		unset($_POST['wpgraphql_logging_nonce']);
	}

	public function test_get_redirect_url_constructs_correct_url(): void {
		$this->set_as_admin();
		$instance = ViewLogsPage::init();

		// Simulate POST data
		$_POST['start_date'] = '2025-01-01 00:00:00';
		$_POST['end_date'] = '2025-12-31 23:59:59';
		$_POST['level_filter'] = [1]; // Should not be an array
		$_POST['orderby'] = 'id';
		$_POST['order'] = 'ASC';

		$url = $instance->get_redirect_url();
		$this->assertStringNotContainsString('level_filter', $url);
		$this->assertEquals(
			menu_page_url(ViewLogsPage::ADMIN_PAGE_SLUG, false) .
			'&start_date=2025-01-01 00:00:00&end_date=2025-12-31 23:59:59&orderby=id&order=ASC',
			$url
		);
	}

	public function test_process_log_download_dies_without_nonce(): void {
		$this->set_as_admin();
		$instance = ViewLogsPage::init();
		$_GET['action'] = 'download';
		$_GET['log'] = 'nonexistent-log-id';
		ob_start();
		$this->expectException(\WPDieException::class);
		$this->expectExceptionMessage('The link you followed has expired.');

		// Use reflection to call the protected method
		$reflection = new \ReflectionClass($instance);
		$method = $reflection->getMethod('process_log_download');
		$method->setAccessible(true);
		$method->invoke($instance);
	}
}
