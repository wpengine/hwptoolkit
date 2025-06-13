<?php

declare(strict_types=1);

namespace HWP\Previews\wpunit\Admin\Settings\Fields\Field;

use HWP\Previews\Admin\Settings\Fields\Field\Text_Input_Field;
use HWP\Previews\Admin\Settings\Fields\Settings_Field_Interface;
use lucatume\WPBrowser\TestCase\WPTestCase;

class TextInputFieldTest extends WPTestCase {

	protected ?Text_Input_Field $field = null;

	protected function setUp(): void {
		parent::setUp();
		$this->field = new Text_Input_Field(
			'preview_url',
			false,
			'Preview URL',
			'The preview URL',
			'https://example.com/%s?preview=true&post_id={ID}&name={slug}',
			'custom-css-class'
		);
	}

	public function test_basic_field_properties(): void {
		$field = $this->field;
		$this->assertEquals( 'preview_url', $field->get_id() );
		$this->assertFalse( $field->is_hierarchical() );
		$this->assertEquals( 'text', $field->get_input_type() );
	}

	public function test_should_render_field_non_hierarchal() {
		$field = $this->field;
		$this->assertInstanceOf( Settings_Field_Interface::class, $field );
		$this->assertTrue( $field->should_render_field( true ) );
		$this->assertTrue( $field->should_render_field( false ) );
	}

	public function test_should_render_field_hierarchical() {
		$field = new Text_Input_Field(
			'post_statuses_as_parent',
			true,
			'Hierarchical Preview URL',
			'By default WordPress only allows published posts to be parents. This option allows posts of all statuses to be used as parent within hierarchical post types.',
		);

		$this->assertFalse( $field->should_render_field( false ) );
		$this->assertTrue( $field->should_render_field( true ) );

	}

	public function test_sanitize_field() {
		$field = $this->field;

		// Valid Input
		$input     = 'https://example.com/?preview=true&post_id=123&name=test-post';
		$sanitized = $field->sanitize_field( $input );
		$this->assertEquals( $input, $sanitized );

		// XSS
		$input     = '<script>alert("xss")</script>https://example.com/?preview=true';
		$sanitized = $field->sanitize_field( $input );
		$this->assertStringNotContainsString( '<script>', $sanitized );
		$this->assertEquals( 'https://example.com/?preview=true', $sanitized );

		// HTML Tags
		$test_cases = array(
			'<b>Bold text</b>'                      => 'Bold text',
			'<div>Content</div>'                    => 'Content',
			'<p>Paragraph</p>'                      => 'Paragraph',
			'<a href="http://example.com">Link</a>' => 'Link',
			'<img src="image.jpg" alt="test">'      => '',
		);

		foreach ( $test_cases as $input => $expected ) {
			$this->assertEquals( $expected, $field->sanitize_field( $input ) );
		}
	}

	public function test_render_field_with_default_value() {
		$field        = $this->field;
		$option_value = array(
			'preview_url' => 'https://example.com/%s?preview=true&post_id={ID}&name={slug}',
		);
		$setting_key  = HWP_PREVIEWS_SETTINGS_KEY;
		$post_type    = 'post';

		$rendered_output      = $field->render_field( $option_value, $setting_key, $post_type );
		$excepted_input_name  = 'hwp_previews_settings[' . $post_type . '][preview_url]';
		$excepted_input_value = 'https://example.com/' . $post_type . '?preview=true&amp;post_id={ID}&amp;name={slug}';
		$excepted_css_class   = 'custom-css-class';

		$expected_output      = '<input type="text" name="' . $excepted_input_name . '" value="' . $excepted_input_value . '" placeholder="' . $excepted_input_value . '" class="' . $excepted_css_class . '" />';

		$this->assertEquals( $expected_output, $rendered_output );
	}

	public function test_render_field_with_no_option_value() {
		$field        = $this->field;
		$option_value = [];
		$setting_key  = HWP_PREVIEWS_SETTINGS_KEY;
		$post_type    = 'post';

		$rendered_output      = $field->render_field( $option_value, $setting_key, $post_type );
		$excepted_input_name  = 'hwp_previews_settings[' . $post_type . '][preview_url]';
		$excepted_input_value = 'https://example.com/' . $post_type . '?preview=true&amp;post_id={ID}&amp;name={slug}';
		$excepted_css_class   = 'custom-css-class';
		$expected_output      = '<input type="text" name="' . $excepted_input_name . '" value="' . $excepted_input_value . '" placeholder="' . $excepted_input_value . '" class="' . $excepted_css_class . '" />';

		$this->assertEquals( $expected_output, $rendered_output );
	}

	public function test_render_field_with_different_option_value_to_default_value() {
		$field                = $this->field;
		$excepted_input_value = 'https://example.com/?preview=true&amp;name={slug}';
		$option_value         = array(
			'preview_url' => $excepted_input_value
		);
		$setting_key          = HWP_PREVIEWS_SETTINGS_KEY;
		$post_type            = 'post';

		$rendered_output     = $field->render_field( $option_value, $setting_key, $post_type );
		$excepted_input_name = 'hwp_previews_settings[' . $post_type . '][preview_url]';
		$default_value       = 'https://example.com/' . $post_type . '?preview=true&amp;post_id={ID}&amp;name={slug}';
		$excepted_css_class  = 'custom-css-class';
		$expected_output     = '<input type="text" name="' . $excepted_input_name . '" value="' . $excepted_input_value . '" placeholder="' . $default_value . '" class="' . $excepted_css_class . '" />';

		$this->assertEquals( $expected_output, $rendered_output );
	}

	public function test_render_field_without_css_class() {
		$field = new Text_Input_Field(
			'preview_url',
			false,
			'Preview URL',
			'The preview URL',
		);

		$rendered_output = $field->render_field( [], HWP_PREVIEWS_SETTINGS_KEY, 'page' );

		$this->assertEquals(
			'<input type="text" name="hwp_previews_settings[page][preview_url]" value="" placeholder="" class="" />',
			$rendered_output
		);
	}

	public function test_get_title() {
        $field = $this->field;
        $this->assertEquals('Preview URL', $field->get_title());
    }

    public function test_get_description() {
        $field = $this->field;
        $this->assertEquals('The preview URL', $field->get_description());
    }

    public function test_add_settings_field_registers_field() {
        $field = $this->field;
		global $wp_settings_fields;
		$field->add_settings_field('section_id', 'page_id', ['foo' => 'bar']);
		$this->assertArrayHasKey('page_id', $wp_settings_fields);
		$this->assertArrayHasKey('section_id', $wp_settings_fields['page_id']);

    }

    public function test_settings_field_callback_outputs_html() {
        $field = $this->field;
        ob_start();
        $args = [
            'post_type' => 'post',
            'settings_key' => HWP_PREVIEWS_SETTINGS_KEY,
        ];
        $field->settings_field_callback($args);
        $output = ob_get_clean();
        $this->assertStringContainsString('hwp-previews-tooltip', $output);
        $this->assertStringContainsString('dashicons-editor-help', $output);
        $this->assertStringContainsString('input type="text"', $output);
    }
}
