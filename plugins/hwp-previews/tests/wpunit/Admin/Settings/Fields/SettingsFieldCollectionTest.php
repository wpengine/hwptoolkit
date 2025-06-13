<?php

declare(strict_types=1);

namespace HWP\Previews\wpunit\Admin\Settings\Fields\Field;

use HWP\Previews\Admin\Settings\Fields\Settings_Field_Collection;
use HWP\Previews\Admin\Settings\Fields\Field\Checkbox_Field;
use HWP\Previews\Admin\Settings\Fields\Field\URL_Input_Field;
use lucatume\WPBrowser\TestCase\WPTestCase;

class SettingsFieldCollectionTest extends WPTestCase
{

	public function test_initialize_fields_adds_default_fields()
	{
		$collection = new Settings_Field_Collection();

		$fields = $collection->get_fields();

		// Assuming initialize_fields adds exactly 4 fields
		$this->assertCount(4, $fields);

		// Optionally, check the IDs of the initialized fields if known
		$expectedIds = [
			'enabled' => 'enabled',
			'post_statuses_as_parent' => 'post_statuses_as_parent',
			'in_iframe' => 'in_iframe',
			'preview_url' => 'preview_url',
		];

		$fieldIds = array_keys($fields);

		$this->assertEquals(array_values($expectedIds), $fieldIds);
	}

	public function test_default_fields_exist_and_are_correct_type()
	{
		$collection = new Settings_Field_Collection();
		$fields = $collection->get_fields();

		$this->assertArrayHasKey('enabled', $fields);
		$this->assertInstanceOf(Checkbox_Field::class, $fields['enabled']);

		$this->assertArrayHasKey('post_statuses_as_parent', $fields);
		$this->assertInstanceOf(Checkbox_Field::class, $fields['post_statuses_as_parent']);

		$this->assertArrayHasKey('in_iframe', $fields);
		$this->assertInstanceOf(Checkbox_Field::class, $fields['in_iframe']);

		$this->assertArrayHasKey('preview_url', $fields);
		$this->assertInstanceOf(URL_Input_Field::class, $fields['preview_url']);
	}

	public function test_default_field_labels_and_descriptions()
	{
		$collection = new Settings_Field_Collection();
		$fields = $collection->get_fields();

		$this->assertEquals('Enable Previews', $fields['enabled']->get_title());
		$this->assertEquals('Enable previews for post type.', $fields['enabled']->get_description());

		$this->assertEquals('Allow all post statuses in parents option', $fields['post_statuses_as_parent']->get_title());
		$this->assertEquals('By default WordPress only allows published posts to be parents. This option allows posts of all statuses to be used as parent within hierarchical post types.', $fields['post_statuses_as_parent']->get_description());

		$this->assertEquals('Use iframe to render previews', $fields['in_iframe']->get_title());
		$this->assertEquals('With this option enabled, headless previews will be displayed inside an iframe on the preview page, without leaving WordPress.', $fields['in_iframe']->get_description());

		$this->assertEquals('Preview URL', $fields['preview_url']->get_title());
		$this->assertEquals('Construct your preview URL using the tags on the right. You can add any parameters needed to support headless previews.', $fields['preview_url']->get_description());
	}

	public function test_get_field_returns_field_and_null_when_not_found()
	{
		$collection = new Settings_Field_Collection();
		$fields = $collection->get_fields();
		$this->assertSame($fields['enabled'], $collection->get_field('enabled'));
		$this->assertNull($collection->get_field('not_a_field'));
	}

	public function test_remove_field_removes_field()
	{
		$collection = new Settings_Field_Collection();
		$fields = $collection->get_fields();
		$this->assertArrayHasKey('enabled', $fields);
		$collection->remove_field('enabled');
		$this->assertNull($collection->get_field('enabled'));
	}
}
