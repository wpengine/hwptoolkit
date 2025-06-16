<?php

declare(strict_types=1);

namespace HWP\Previews\wpunit\Admin;

use HWP\Previews\Admin\Settings_Page;
use HWP\Previews\Preview\Parameter\Preview_Parameter_Registry;
use HWP\Previews\Preview\Post\Type\Contracts\Post_Types_Config_Interface;
use lucatume\WPBrowser\TestCase\WPTestCase;

class SettingsPageTest extends WPTestCase
{
	private $admin_user_id;
	private $mock_parameter_registry;
	private $mock_post_types_config;
	private $mock_post_types_config_registry;

	public function setUp(): void
	{
		parent::setUp();

		// Create an admin user
		$this->admin_user_id = $this->factory()->user->create([
			'role' => 'administrator'
		]);
		wp_set_current_user($this->admin_user_id);

		// Reset the singleton instance
		$reflection = new \ReflectionClass(Settings_Page::class);
		$instance_property = $reflection->getProperty('instance');
		$instance_property->setAccessible(true);
		$instance_property->setValue(null, null);

		// Mock dependencies
		$this->setupMocks();
	}

	public function tearDown(): void
	{
		// Clean up user
		wp_delete_user($this->admin_user_id);
		wp_set_current_user(0);

		// Reset singleton
		$reflection = new \ReflectionClass(Settings_Page::class);
		$instance_property = $reflection->getProperty('instance');
		$instance_property->setAccessible(true);
		$instance_property->setValue(null, null);

		// Clear $_GET
		$_GET = [];

		parent::tearDown();
	}

	private function setupMocks(): void
	{
		// Mock Preview_Parameter_Registry
		$this->mock_parameter_registry = $this->createMock(Preview_Parameter_Registry::class);
		$this->mock_parameter_registry->method('get_descriptions')
			->willReturn([
				'param1' => 'Description 1',
				'param2' => 'Description 2'
			]);

		// Mock Post_Types_Config_Interface
		$this->mock_post_types_config = $this->createMock(Post_Types_Config_Interface::class);
		$this->mock_post_types_config->method('get_public_post_types')
			->willReturn([
				'post' => 'Posts',
				'page' => 'Pages',
				'product' => 'Products'
			]);
	}

	public function test_singleton_init_creates_instance()
	{
		// Use reflection to mock static method calls
		$this->mockStaticDependencies();

		$instance1 = Settings_Page::init();
		$instance2 = Settings_Page::init();

		$this->assertInstanceOf(Settings_Page::class, $instance1);
		$this->assertSame($instance1, $instance2, 'Should return the same instance (singleton)');
	}

	public function test_singleton_init_fires_action()
	{
		$this->mockStaticDependencies();

		$action_fired = false;
		add_action('hwp_previews_init', function($instance) use (&$action_fired) {
			$action_fired = true;
			$this->assertInstanceOf(Settings_Page::class, $instance);
		});

		Settings_Page::init();

		$this->assertTrue($action_fired, 'hwp_previews_init action should be fired');
	}

	public function test_constructor_initializes_dependencies()
	{
		$this->mockStaticDependencies();

		$settings_page = new Settings_Page();

		// Use reflection to check protected properties
		$reflection = new \ReflectionClass($settings_page);

		$parameters_prop = $reflection->getProperty('parameters');
		$parameters_prop->setAccessible(true);
		$this->assertInstanceOf(Preview_Parameter_Registry::class, $parameters_prop->getValue($settings_page));

		$types_config_prop = $reflection->getProperty('types_config');
		$types_config_prop->setAccessible(true);
		$this->assertInstanceOf(Post_Types_Config_Interface::class, $types_config_prop->getValue($settings_page));
	}

	public function test_register_settings_pages_adds_admin_menu_action()
	{
		$this->mockStaticDependencies();

		$settings_page = new Settings_Page();

		// Check that admin_menu action was added
		$this->assertTrue(has_action('admin_menu') !== false, 'admin_menu action should be registered');
	}

	public function test_register_settings_fields_adds_admin_init_action()
	{
		$this->mockStaticDependencies();

		$settings_page = new Settings_Page();

		// Check that admin_init action was added
		$this->assertTrue(has_action('admin_init') !== false, 'admin_init action should be registered');
	}

	public function test_load_scripts_styles_adds_admin_enqueue_scripts_action()
	{
		$this->mockStaticDependencies();

		$settings_page = new Settings_Page();

		// Check that admin_enqueue_scripts action was added
		$this->assertTrue(has_action('admin_enqueue_scripts') !== false, 'admin_enqueue_scripts action should be registered');
	}

	public function test_get_current_tab_returns_first_tab_when_no_get_param()
	{
		$this->mockStaticDependencies();
		$settings_page = new Settings_Page();

		$post_types = [
			'post' => 'Posts',
			'page' => 'Pages',
			'product' => 'Products'
		];

		$current_tab = $settings_page->get_current_tab($post_types);

		$this->assertEquals('post', $current_tab, 'Should return first post type key when no GET parameter');
	}

	public function test_get_current_tab_returns_sanitized_get_param()
	{
		$this->mockStaticDependencies();
		$settings_page = new Settings_Page();

		$post_types = [
			'post' => 'Posts',
			'page' => 'Pages',
			'product' => 'Products'
		];

		$_GET['tab'] = 'page';
		$current_tab = $settings_page->get_current_tab($post_types);

		$this->assertEquals('page', $current_tab, 'Should return sanitized GET parameter value');
	}

	public function test_get_current_tab_with_custom_tab_param_name()
	{
		$this->mockStaticDependencies();
		$settings_page = new Settings_Page();

		$post_types = [
			'post' => 'Posts',
			'page' => 'Pages'
		];

		$_GET['custom_tab'] = 'page';
		$current_tab = $settings_page->get_current_tab($post_types, 'custom_tab');

		$this->assertEquals('page', $current_tab, 'Should use custom tab parameter name');
	}

	public function test_get_current_tab_sanitizes_malicious_input()
	{
		$this->mockStaticDependencies();
		$settings_page = new Settings_Page();

		$post_types = [
			'post' => 'Posts',
			'page' => 'Pages'
		];

		$_GET['tab'] = '<script>alert("xss")</script>';
		$current_tab = $settings_page->get_current_tab($post_types);

		$this->assertEquals('scriptalertxssscript', $current_tab, 'Should sanitize malicious input using sanitize_key');
	}

	public function test_get_current_tab_returns_empty_string_for_empty_post_types()
	{
		$this->mockStaticDependencies();
		$settings_page = new Settings_Page();

		$current_tab = $settings_page->get_current_tab([]);

		$this->assertEquals('', $current_tab, 'Should return empty string for empty post types array');
	}

	public function test_get_current_tab_handles_non_string_get_value()
	{
		$this->mockStaticDependencies();
		$settings_page = new Settings_Page();

		$post_types = [
			'post' => 'Posts',
			'page' => 'Pages'
		];

		$_GET['tab'] = ['array_value'];
		$current_tab = $settings_page->get_current_tab($post_types);

		$this->assertEquals('post', $current_tab, 'Should return first post type when GET value is not a string');
	}

	public function test_plugin_menu_slug_constant()
	{
		$this->assertEquals('hwp-previews', Settings_Page::PLUGIN_MENU_SLUG, 'Plugin menu slug should be correct');
	}

	private function mockStaticDependencies(): void
	{
		// Mock Preview_Parameter_Registry::get_instance()
		if (!function_exists('HWP\Previews\wpunit\Admin\mock_preview_parameter_registry_get_instance')) {
			function mock_preview_parameter_registry_get_instance() {
				$mock = $this->createMock(Preview_Parameter_Registry::class);
				$mock->method('get_descriptions')->willReturn([
					'param1' => 'Description 1',
					'param2' => 'Description 2'
				]);
				return $mock;
			}
		}

		// Mock Post_Types_Config_Registry::get_post_type_config()
		if (!function_exists('HWP\Previews\wpunit\Admin\mock_post_types_config_registry_get_post_type_config')) {
			function mock_post_types_config_registry_get_post_type_config() {
				$mock = $this->createMock(Post_Types_Config_Interface::class);
				$mock->method('get_public_post_types')->willReturn([
					'post' => 'Posts',
					'page' => 'Pages',
					'product' => 'Products'
				]);
				return $mock;
			}
		}

		// Override the static method calls in the constructor
		// This would require modifying the original class or using a mocking framework
		// For now, we'll assume the dependencies are properly injected
	}
}
