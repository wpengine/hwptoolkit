<?php

declare(strict_types=1);

namespace HWP\Previews\wpunit\Admin\Settings\Fields\Field;

use HWP\Previews\Admin\Settings\Fields\Field\Checkbox_Field;
use HWP\Previews\Admin\Settings\Fields\Settings_Field_Interface;
use lucatume\WPBrowser\TestCase\WPTestCase;

class CheckboxFieldTest extends WPTestCase {

	protected ?Checkbox_Field $field = null;

	protected function setUp(): void {
		parent::setUp();
		$this->field = new Checkbox_Field(
			'in_iframe',
			false,
			'Use iframe to render previews',
			'With this option enabled, headless previews will be displayed inside an iframe on the preview page, without leaving WordPress.',
			false,
			'custom-css-class'
		);
	}

	public function test_basic_field_properties(): void {
		$field = $this->field;
		$this->assertEquals('in_iframe', $field->get_id());
		$this->assertFalse($field->is_hierarchical());
	}

	public function test_should_render_field_non_hierarchal(): void {
		$field = $this->field;
		$this->assertInstanceOf(Settings_Field_Interface::class, $field);
		$this->assertTrue($field->should_render_field(true));
		$this->assertTrue($field->should_render_field(false));
	}

	public function test_should_render_field_hierarchical(): void {
		$field = new Checkbox_Field(
			'in_iframe',
			true,
			'Use iframe to render previews',
			'With this option enabled, headless previews will be displayed inside an iframe on the preview page, without leaving WordPress.'
		);
		$this->assertFalse($field->should_render_field(false));
		$this->assertTrue($field->should_render_field(true));
	}

	public function test_sanitize_field(): void {
		$field = $this->field;

		$this->assertEquals('1', $field->sanitize_field('1'));
		$this->assertEquals('0', $field->sanitize_field('0'));
		$this->assertEquals('1', $field->sanitize_field('on'));
		$this->assertEquals('1', $field->sanitize_field('off'));
		$this->assertEquals('1', $field->sanitize_field('random'));
	}

	public function test_render_field_checked(): void {
		$field = $this->field;
		$option_value = ['in_iframe' => '1'];
		$setting_key = HWP_PREVIEWS_SETTINGS_KEY;
		$post_type = 'post';

		$expected_input_name = 'hwp_previews_settings[' . $post_type . '][in_iframe]';
		$expceted_input_label = 'hwp_previews_settings-' . $post_type . '-in_iframe-tooltip';
		$expected_css_class = 'custom-css-class';
		$expected_output = '<input type="checkbox" name="' . $expected_input_name . '" aria-labelledby="' . $expceted_input_label . '" value="1"  checked=\'checked\' class="' . $expected_css_class . '" />';

		$rendered_output = $field->render_field($option_value, $setting_key, $post_type);

		$this->assertEquals($expected_output, $rendered_output);
	}

	public function test_render_field_unchecked(): void {
		$field = $this->field;
		$option_value = ['in_iframe' => '0'];
		$setting_key = HWP_PREVIEWS_SETTINGS_KEY;
		$post_type = 'post';

		$expected_input_name = 'hwp_previews_settings[' . $post_type . '][in_iframe]';
		$expceted_input_label = 'hwp_previews_settings-' . $post_type . '-in_iframe-tooltip';
		$expected_css_class = 'custom-css-class';
		$expected_output = '<input type="checkbox" name="' . $expected_input_name . '" aria-labelledby="' . $expceted_input_label . '" value="1"  class="' . $expected_css_class . '" />';

		$rendered_output = $field->render_field($option_value, $setting_key, $post_type);

		$this->assertEquals($expected_output, $rendered_output);
	}

	public function test_render_field_without_css_class(): void {
		$field = new Checkbox_Field(
			'in_iframe',
			false,
			'Use iframe to render previews',
			'With this option enabled, headless previews will be displayed inside an iframe on the preview page, without leaving WordPress.'
		);

		$rendered_output = $field->render_field([], HWP_PREVIEWS_SETTINGS_KEY, 'page');

		$this->assertEquals(
			'<input type="checkbox" name="hwp_previews_settings[page][in_iframe]" aria-labelledby="hwp_previews_settings-page-in_iframe-tooltip" value="1"  class="" />',
			$rendered_output
		);
	}

	public function test_get_title(): void {
		$field = $this->field;
		$this->assertEquals('Use iframe to render previews', $field->get_title());
	}

	public function test_get_description(): void {
		$field = $this->field;
		$this->assertEquals('With this option enabled, headless previews will be displayed inside an iframe on the preview page, without leaving WordPress.', $field->get_description());
	}

	public function test_add_settings_field_registers_field(): void {
		$field = $this->field;
		global $wp_settings_fields;
		$field->add_settings_field('section_id', 'page_id', ['foo' => 'bar']);
		$this->assertArrayHasKey('page_id', $wp_settings_fields);
		$this->assertArrayHasKey('section_id', $wp_settings_fields['page_id']);
	}

	public function test_settings_field_callback_outputs_html(): void {
		$field = $this->field;
		ob_start();
		$args = [
			'post_type'    => 'post',
			'settings_key' => HWP_PREVIEWS_SETTINGS_KEY,
		];
		$field->settings_field_callback($args);
		$output = ob_get_clean();
		$this->assertStringContainsString('hwp-previews-tooltip', $output);
		$this->assertStringContainsString('dashicons-editor-help', $output);
		$this->assertStringContainsString('input type="checkbox"', $output);
	}
}
