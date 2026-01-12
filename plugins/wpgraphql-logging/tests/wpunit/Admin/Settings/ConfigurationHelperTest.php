<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Tests\Admin\Settings;

use WPGraphQL\Logging\Admin\Settings\ConfigurationHelper;
use Codeception\TestCase\WPTestCase;

/**
 * Test class for ConfigurationHelper.
 *
 * @package WPGraphQL\Logging
 *
 * @since 0.0.1
 */
class ConfigurationHelperTest extends WPTestCase {

	protected $config = [
		'basic_configuration' => [
			'enabled' => true,
			'log_level' => 'info',
			'max_log_entries' => 1000,
			'enable_debug_mode' => false
		],
		'data_management' => [
			'enabled' => false,
			'auto_cleanup' => true,
			'cleanup_interval' => 'weekly',
			'retention_period' => 30,
			'export_format' => 'json'
		]
	];

	protected $default = [];


	public function setUp(): void {
		$configuration_helper = ConfigurationHelper::get_instance();
		$option_key = $configuration_helper->get_option_key();
		$this->default = get_option($option_key, []);
		update_option($option_key, $this->config);
		parent::setUp();
	}

	public function tearDown(): void {
		parent::tearDown();
		$configuration_helper = ConfigurationHelper::get_instance();
		$option_key = $configuration_helper->get_option_key();
		update_option($option_key, $this->default);
	}


	/**
	 * Test that instance is initially null and gets created.
	 */
	public function test_instance_initially_null_then_created(): void {
		// Use reflection to access the private static property
		$reflection = new \ReflectionClass(ConfigurationHelper::class);
		$instanceProperty = $reflection->getProperty('instance');
		$instanceProperty->setAccessible(true);

		// Reset the instance to null
		$instanceProperty->setValue(null, null);

		// Verify instance is null initially
		$this->assertNull($instanceProperty->getValue());

		// Get instance should create it
		$instance = ConfigurationHelper::get_instance();

		// Verify instance is no longer null and is correct type
		$this->assertNotNull($instanceProperty->getValue());
		$this->assertInstanceOf(ConfigurationHelper::class, $instance);
		$this->assertSame($instance, $instanceProperty->getValue());
	}

	/**
	 * Test get_config returns empty array when no config is set.
	 */
	public function test_get_config_returns_empty_array_when_no_config(): void {
		$instance = ConfigurationHelper::get_instance();
		$config = $instance->get_config();

		$this->assertIsArray($config);
	}

	public function test_get_setting(): void {
		$instance = ConfigurationHelper::get_instance();

		$default_value = ['test_key' => 'default_value'];
		$section = 'nonexistent_section';
		$setting_key = 'test_key';
		$result = $instance->get_setting($section, $setting_key, $default_value);
		$this->assertEquals($default_value, $result);
	}

	public function test_get_basic_config_returns_array(): void {
		$instance = ConfigurationHelper::get_instance();
		$basic_config = $instance->get_basic_config();

		$this->assertIsArray($basic_config);
		$this->assertSame($this->config['basic_configuration'], $basic_config);
	}

	public function test_get_data_management_returns_array(): void {
		$instance = ConfigurationHelper::get_instance();
		$data_management = $instance->get_data_management_config();

		$this->assertIsArray($data_management);
		$this->assertSame($this->config['data_management'], $data_management);
	}

	public function test_get_is_enabled(): void {
		$instance = ConfigurationHelper::get_instance();
		$this->assertTrue($instance->is_enabled('basic_configuration', 'enabled'));
		$this->assertFalse($instance->is_enabled('data_management', 'enabled'));
	}

	public function test_register_cache_hooks(): void {
		$instance = ConfigurationHelper::get_instance();


		// Set initial configuration
		$configuration = ['enabled' => true];
		update_option($instance->get_option_key(), $configuration);

		// Register cache hooks.
		ConfigurationHelper::register_cache_hooks();

		$this->assertEquals(10, has_action("update_option_{$instance->get_option_key()}", [$instance, 'clear_cache']));
		$this->assertEquals(10, has_action("add_option_{$instance->get_option_key()}", [$instance, 'clear_cache']));
		$this->assertEquals(10, has_action("delete_option_{$instance->get_option_key()}", [$instance, 'clear_cache']));

		// Test that the cache is cleared when the option is updated.
		update_option($instance->get_option_key(), ['enabled' => false]);
		$this->assertEquals(['enabled' => false], $instance->get_config());
	}
}
