<?php

declare(strict_types=1);

namespace HWP\Previews\wpunit\Admin\Settings\Fields\Field;

use HWP\Previews\Admin\Settings\Fields\Field\Text_Input_Field;
use HWP\Previews\Admin\Settings\Fields\Field\URL_Input_Field;
use HWP\Previews\Admin\Settings\Fields\Settings_Field_Interface;
use lucatume\WPBrowser\TestCase\WPTestCase;

class TextURLFieldTest extends WPTestCase {

	protected ?URL_Input_Field $field = null;

	protected function setUp(): void {
		parent::setUp();
		$this->field = new URL_Input_Field(
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
		$this->assertEquals( 'url', $field->get_input_type() );
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
		$protocol   = is_ssl() ? 'https://' : 'http://';
		$test_cases = array(
			'<b>Bold text</b>'                      => $protocol . 'Bold%20text',
			'<div>Content</div>'                    => $protocol . 'Content',
			'<p>Paragraph</p>'                      => $protocol . 'Paragraph',
			'<a href="http://example.com">Link</a>' => $protocol . 'Link',
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
		$expceted_input_label = 'hwp_previews_settings-' . $post_type . '-preview_url-tooltip';
		$excepted_input_value = 'https://example.com/' . $post_type . '?preview=true&amp;post_id={ID}&amp;name={slug}';
		$excepted_css_class   = 'custom-css-class';
		$expected_output      = '<input type="url" name="' . $excepted_input_name . '" aria-labelledby="' . $expceted_input_label . '" value="' . $excepted_input_value . '" placeholder="' . $excepted_input_value . '" class="' . $excepted_css_class . '" />';

		$this->assertEquals( $expected_output, $rendered_output );
	}

	public function test_render_field_with_no_option_value() {
		$field        = $this->field;
		$option_value = [];
		$setting_key  = HWP_PREVIEWS_SETTINGS_KEY;
		$post_type    = 'post';

		$rendered_output      = $field->render_field( $option_value, $setting_key, $post_type );
		$excepted_input_name  = 'hwp_previews_settings[' . $post_type . '][preview_url]';
		$expceted_input_label = 'hwp_previews_settings-' . $post_type . '-preview_url-tooltip';
		$excepted_input_value = 'https://example.com/' . $post_type . '?preview=true&amp;post_id={ID}&amp;name={slug}';
		$excepted_css_class   = 'custom-css-class';
		$expected_output      = '<input type="url" name="' . $excepted_input_name . '" aria-labelledby="' . $expceted_input_label . '" value="' . $excepted_input_value . '" placeholder="' . $excepted_input_value . '" class="' . $excepted_css_class . '" />';

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
		$expceted_input_label = 'hwp_previews_settings-' . $post_type . '-preview_url-tooltip';
		$default_value       = 'https://example.com/' . $post_type . '?preview=true&amp;post_id={ID}&amp;name={slug}';
		$excepted_css_class  = 'custom-css-class';
		$expected_output     = '<input type="url" name="' . $excepted_input_name . '" aria-labelledby="' . $expceted_input_label . '" value="' . $excepted_input_value . '" placeholder="' . $default_value . '" class="' . $excepted_css_class . '" />';

		$this->assertEquals( $expected_output, $rendered_output );
	}

	public function test_render_field_without_css_class() {
		$field = new URL_Input_Field(
			'preview_url',
			false,
			'Preview URL',
			'The preview URL',
		);

		$rendered_output = $field->render_field( [], HWP_PREVIEWS_SETTINGS_KEY, 'page' );

		$this->assertEquals(
			'<input type="url" name="hwp_previews_settings[page][preview_url]" aria-labelledby="hwp_previews_settings-page-preview_url-tooltip" value="" placeholder="" class="" />',
			$rendered_output
		);
	}

	public function test_sanitize_field_with_parameters() {
		$value = 'https://example.com/post?preview=true&post_id={ID}&name={slug}';
		$this->assertEquals(
			$value,
			$this->field->sanitize_field( $value )
		);
	}

		public function test_get_setting_value_returns_value_when_set() {
		$field = $this->field;
		$settings_key = HWP_PREVIEWS_SETTINGS_KEY;
		$post_type = 'post';
		$option_value = [
			$post_type => [
				'preview_url' => 'https://localhost:3000/post?preview=true&post_id=ID&name={slug}'
			]
		];

		update_option( $settings_key, $option_value );

		$result = $field->get_setting_value( $settings_key, $post_type );
		$this->assertEquals( [ 'preview_url' => 'https://localhost:3000/post?preview=true&post_id=ID&name={slug}' ], $result );

		delete_option( $settings_key );
		$this->assertEmpty( $field->get_setting_value( $settings_key, $post_type ) );
	}
}
