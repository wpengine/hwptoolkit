<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Tests\Admin\Settings;

use Codeception\TestCase\WPTestCase;
use WPGraphQL\Logging\Admin\Settings\ConfigurationHelper;
use WPGraphQL\Logging\Admin\Settings\Fields\SettingsFieldCollection;
use WPGraphQL\Logging\Admin\Settings\Fields\SettingsFieldInterface;
use WPGraphQL\Logging\Admin\Settings\SettingsFormManager;
use WPGraphQL\Logging\Admin\Settings\Fields\Tab\SettingsTabInterface;

/**
 * Test class for Settings.
 *
 * @package WPGraphQL\Logging
 *
 * @since 0.0.1
 */
class SettingsFormManagerTest extends WPTestCase {

	private SettingsFormManager $manager;
	private SettingsFieldCollection $field_collection;
	private ConfigurationHelper $configuration_helper;

	public function setUp(): void {
		parent::setUp();

		$this->field_collection = $this->createMock(SettingsFieldCollection::class);
		$this->configuration_helper = $this->createMock(ConfigurationHelper::class);

		$this->manager = new SettingsFormManager(
			$this->field_collection,
			$this->configuration_helper
		);
	}

	public function test_get_option_key_returns_configuration_helper_value(): void {
		$expected = 'test_option_key';
		$this->configuration_helper
			->expects($this->once())
			->method('get_option_key')
			->willReturn($expected);

		$result = $this->manager->get_option_key();

		$this->assertEquals($expected, $result);
	}

	public function test_get_settings_group_returns_configuration_helper_value(): void {
		$expected = 'test_settings_group';
		$this->configuration_helper
			->expects($this->once())
			->method('get_settings_group')
			->willReturn($expected);

		$result = $this->manager->get_settings_group();

		$this->assertEquals($expected, $result);
	}

	public function test_sanitize_settings_returns_empty_array_when_input_is_null(): void {
		$result = $this->manager->sanitize_settings(null);

		$this->assertEquals([], $result);
	}

	public function test_sanitize_settings_returns_old_input_when_no_tabs(): void {
		$old_option = ['existing' => 'data'];
		update_option('test_option', $old_option);

		$this->configuration_helper
			->method('get_option_key')
			->willReturn('test_option');

		$this->field_collection
			->method('get_tabs')
			->willReturn([]);

		$result = $this->manager->sanitize_settings(['new' => 'data']);

		$this->assertEquals($old_option, $result);
	}

	public function test_sanitize_settings_sanitizes_known_fields(): void {
		$old_option = [];
		update_option('test_option', $old_option);

		$this->configuration_helper
			->method('get_option_key')
			->willReturn('test_option');

		$tab = $this->createMock(SettingsTabInterface::class);
		$this->field_collection
			->method('get_tabs')
			->willReturn(['test_tab' => $tab]);

		$field = $this->createMock(SettingsFieldInterface::class);
		$field->expects($this->once())
			->method('sanitize_field')
			->with('raw_value')
			->willReturn('sanitized_value');

		$this->field_collection
			->method('get_field')
			->with('test_field')
			->willReturn($field);

		$new_input = ['test_tab' => ['test_field' => 'raw_value']];
		$result = $this->manager->sanitize_settings($new_input);

		$expected = ['test_tab' => ['test_field' => 'sanitized_value']];
		$this->assertEquals($expected, $result);
	}

	public function test_sanitize_settings_skips_unknown_fields(): void {
		$old_option = [];
		update_option('test_option', $old_option);

		$this->configuration_helper
			->method('get_option_key')
			->willReturn('test_option');

		$tab = $this->createMock(SettingsTabInterface::class);
		$this->field_collection
			->method('get_tabs')
			->willReturn(['test_tab' => $tab]);

		$this->field_collection
			->method('get_field')
			->with('unknown_field')
			->willReturn(null);

		$new_input = ['test_tab' => ['unknown_field' => 'value']];
		$result = $this->manager->sanitize_settings($new_input);

		$expected = ['test_tab' => []];
		$this->assertEquals($expected, $result);
	}

	public function test_sanitize_settings_prunes_redundant_tabs(): void {
		$old_option = ['old_tab' => ['data' => 'value'], 'valid_tab' => ['field' => 'value']];
		update_option('test_option', $old_option);

		$this->configuration_helper
			->method('get_option_key')
			->willReturn('test_option');

		$tab = $this->createMock(SettingsTabInterface::class);
		$this->field_collection
			->method('get_tabs')
			->willReturn(['valid_tab' => $tab]);

		$field = $this->createMock(SettingsFieldInterface::class);
		$field->method('sanitize_field')->willReturn('new_value');

		$this->field_collection
			->method('get_field')
			->willReturn($field);

		$new_input = ['valid_tab' => ['field' => 'new_value']];
		$result = $this->manager->sanitize_settings($new_input);

		$this->assertArrayNotHasKey('old_tab', $result);
		$this->assertArrayHasKey('valid_tab', $result);
	}
}
