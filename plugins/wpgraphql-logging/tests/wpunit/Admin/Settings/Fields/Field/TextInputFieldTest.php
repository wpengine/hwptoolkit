<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\wpunit\Admin\Settings\Fields\Field;

use WPGraphQL\Logging\Admin\Settings\Fields\Field\TextInputField;
use WPGraphQL\Logging\Admin\Settings\Fields\SettingsFieldInterface;
use lucatume\WPBrowser\TestCase\WPTestCase;

class TextInputFieldTest extends WPTestCase {

	protected ?TextInputField $field = null;

	protected function setUp(): void {
		parent::setUp();
		$this->field = new TextInputField(
			'ip_restrictions',
			'basic_configuration',
			'IP Restrictions',
			'custom-css-class',
			'A comma separated list of IP addresses to restrict logging to. Leave empty to log from all IPs.',
			'e.g. 192.168.1.1, 10.0.0.1',
			''
		);
	}

	public function test_basic_field_properties(): void {
		$field = $this->field;
		$this->assertEquals( 'ip_restrictions', $field->get_id() );
		$this->assertTrue( $field->should_render_for_tab( 'basic_configuration' ) );
		$this->assertFalse( $field->should_render_for_tab( 'other_tab' ) );
		$this->assertTrue( $field->should_render_for_tab( 'basic_configuration' ) );
	}


	public function test_sanitize_field() {
		$field = $this->field;

		// Valid Input
		$input     = '192.168.1.1, 10.0.0.1';
		$sanitized = $field->sanitize_field( $input );
		$this->assertEquals( $input, $sanitized );

		// XSS
		$input     = '<script>alert("xss")</script>192.168.1.1, 10.0.0.1';
		$sanitized = $field->sanitize_field( $input );
		$this->assertStringNotContainsString( '<script>', $sanitized );
		$this->assertEquals( '192.168.1.1, 10.0.0.1', $sanitized );

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

	public function test_render_field() {
		$field = $this->field;
		$option_value = [];
		$setting_key = 'wpgraphql_logging_settings';
		$tab_key = 'basic_configuration';
		ob_start();
		$field->render_field_callback( [ 'tab_key' => $tab_key, 'settings_key' => $setting_key ] );
		$rendered_output = ob_get_clean();
		$this->assertStringContainsString( 'name="wpgraphql_logging_settings[basic_configuration][ip_restrictions]"', $rendered_output );
		$this->assertStringContainsString( 'value=""', $rendered_output );
		$this->assertStringContainsString( 'class="custom-css-class"', $rendered_output );
		$this->assertStringContainsString( 'placeholder="e.g. 192.168.1.1, 10.0.0.1"', $rendered_output );
		$this->assertStringContainsString( 'type="text"', $rendered_output );
	}

	public function test_sanitize_field_email() {

		$field = new TextInputField(
			'email_address',
			'basic_configuration',
			'Email Address',
			'custom-css-class',
			'The email address to send logs to.',
			'example@example.com',
			''
		);


		$this->assertEquals('test@example.com', $field->sanitize_field('test@example.com'));
	}

	public function test_sanitize_field_url() {

		$field = new TextInputField(
			'url',
			'basic_configuration',
			'URL',
			'custom-css-class',
			'The URL to send logs to.',
			'https://example.com',
			''
		);


		$this->assertEquals('https://example.com', $field->sanitize_field('https://example.com'));
	}

	public function test_add_settings_field_registers_field() {
        $field = $this->field;
		global $wp_settings_fields;
		$field->add_settings_field('section_id', 'page_id', ['foo' => 'bar']);
		$this->assertArrayHasKey('page_id', $wp_settings_fields);
		$this->assertArrayHasKey('section_id', $wp_settings_fields['page_id']);
    }

	public function test_render_field_callback() {
		$field = $this->field;
		$option_value = [];
		$setting_key = 'wpgraphql_logging_settings';
		$tab_key = 'basic_configuration';
		$args = [
			'tab_key' => $tab_key,
			'settings_key' => $setting_key,
		];

		ob_start();
		$field->render_field_callback( $args );
		$rendered_output = ob_get_contents();
		ob_end_clean();

		$this->assertStringContainsString( 'name="wpgraphql_logging_settings[basic_configuration][ip_restrictions]"', $rendered_output );
		$this->assertStringContainsString( 'value=""', $rendered_output );
		$this->assertStringContainsString( 'class="custom-css-class"', $rendered_output );
		$this->assertStringContainsString( 'placeholder="e.g. 192.168.1.1, 10.0.0.1"', $rendered_output );
		$this->assertStringContainsString( 'type="text"', $rendered_output );
		$this->assertStringContainsString( 'A comma separated list of IP addresses to restrict logging to. Leave empty to log from all IPs.', $rendered_output );
		$this->assertStringNotContainsString('multiple="multiple"', $rendered_output);
	}

	public function test_get_field_value() {
		// Use a field with a non-empty default value to verify fallbacks
		$field = new TextInputField(
			'test_field',
			'basic_configuration',
			'Test Field',
			'css',
			'desc',
			'ph',
			'DEFAULT'
		);

		$settings_key = 'wpgraphql_logging_settings';
		$tab_key = 'basic_configuration';

		$extract_value = static function( string $html ): string {
			$matches = [];
			preg_match('/value=\"([^\"]*)\"/', $html, $matches);
			return $matches[1] ?? '';
		};

		// 1) Tab exists but field ID missing -> default
		$html = $field->render_field( [ $tab_key => [] ], $settings_key, $tab_key );
		$this->assertSame( 'DEFAULT', $extract_value( $html ) );

		// 2) Field ID present with null -> default
		$html = $field->render_field( [ $tab_key => [ 'test_field' => null ] ], $settings_key, $tab_key );
		$this->assertSame( 'DEFAULT', $extract_value( $html ) );

		// 3) Field ID present with empty string -> empty string (not default)
		$html = $field->render_field( [ $tab_key => [ 'test_field' => '' ] ], $settings_key, $tab_key );
		$this->assertSame( '', $extract_value( $html ) );

		// 4) Field ID present with a value -> that value
		$html = $field->render_field( [ $tab_key => [ 'test_field' => 'custom' ] ], $settings_key, $tab_key );
		$this->assertSame( 'custom', $extract_value( $html ) );

		// 5) Empty field ID -> default
		// Create a subclass overriding get_id to simulate empty ID
		$emptyIdField = new class('ignored', $tab_key, 'Title', 'css', 'desc', 'ph', 'DEF') extends TextInputField {
			public function get_id(): string { return ''; }
		};
		$html = $emptyIdField->render_field( [ $tab_key => [ 'ignored' => 'value' ] ], $settings_key, $tab_key );
		$this->assertSame( 'DEF', $extract_value( $html ) );
	}

}
