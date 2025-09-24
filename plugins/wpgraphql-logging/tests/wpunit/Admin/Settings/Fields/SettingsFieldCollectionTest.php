<?php

declare(strict_types=1);

namespace Tests\WPUnit\Admin\Settings\Fields;

use WPGraphQL\Logging\Admin\Settings\Fields\SettingsFieldCollection;
use WPGraphQL\Logging\Admin\Settings\Fields\SettingsFieldInterface;
use WPGraphQL\Logging\Admin\Settings\Fields\Tab\SettingsTabInterface;
use WPGraphQL\Logging\Admin\Settings\Fields\Tab\BasicConfigurationTab;
use WPGraphQL\Logging\Admin\Settings\Fields\Tab\DataManagementTab;
use Codeception\TestCase\WPTestCase;


/**
 * Test class for SettingsFieldCollection.
 *
 * @package WPGraphQL\Logging
 *
 * @since 0.0.1
 */
class SettingsFieldCollectionTest extends WPTestCase {

	private SettingsFieldCollection $collection;

	public function setUp(): void {
		parent::setUp();
		$this->collection = new SettingsFieldCollection();
	}

	public function test_constructor_initializes_tabs(): void {
		$tabs = $this->collection->get_tabs();

		$this->assertNotEmpty($tabs);
		$this->assertArrayHasKey(BasicConfigurationTab::get_name(), $tabs);
		$this->assertArrayHasKey(DataManagementTab::get_name(), $tabs);
	}



	public function test_add_field(): void {
		$field = $this->createMock(SettingsFieldInterface::class);
		$key = 'test_field';

		$this->collection->add_field($key, $field);

		$this->assertEquals($field, $this->collection->get_field($key));
		$this->assertInstanceOf(SettingsFieldInterface::class, $this->collection->get_field($key));

		// Remove field
		$this->collection->remove_field($key);
		$this->assertNull($this->collection->get_field($key));
	}

	public function test_get_tab(): void {
		$tab = $this->collection->get_tab(BasicConfigurationTab::get_name());

		$this->assertInstanceOf(SettingsTabInterface::class, $tab);
		$this->assertEquals(BasicConfigurationTab::get_name(), $tab->get_name());

		$this->assertNull($this->collection->get_tab('nonexistent_tab'));
	}
}
