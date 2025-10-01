<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Tests\Admin\Settings\Fields;

use WPGraphQL\Logging\Admin\Settings\Fields\Field\TextInputField;
use WPGraphQL\Logging\Admin\Settings\Fields\SettingsFieldInterface;
use WPGraphQL\Logging\Admin\Settings\Fields\Tab\BasicConfigurationTab;
use lucatume\WPBrowser\TestCase\WPTestCase;

/**
 * Test class for TextInputField.
 *
 * @package WPGraphQL\Logging
 *
 * @since 0.0.1
 */
class TextInputFieldTest extends WPTestCase {

	protected ?TextInputField $field = null;

	protected function setUp(): void {
		parent::setUp();
		$this->field = new TextInputField(
			'ip_restrictions',
			BasicConfigurationTab::get_name(),
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
		$this->assertTrue( $field->should_render_for_tab( BasicConfigurationTab::get_name() ) );
		$this->assertFalse( $field->should_render_for_tab( 'other_tab' ) );
		$this->assertTrue( $field->should_render_for_tab( BasicConfigurationTab::get_name() ) );
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
		$tab_key = BasicConfigurationTab::get_name();
		ob_start();
		$field->render_field_callback( [ 'tab_key' => $tab_key, 'settings_key' => $setting_key ] );
		$rendered_output = ob_get_clean();
		$this->assertStringContainsString( 'name="wpgraphql_logging_settings[basic_configuration][ip_restrictions]"', $rendered_output );
		$this->assertStringContainsString( 'value=""', $rendered_output );
		$this->assertStringContainsString( 'class="custom-css-class"', $rendered_output );
		$this->assertStringContainsString( 'placeholder="e.g. 192.168.1.1, 10.0.0.1"', $rendered_output );
		$this->assertStringContainsString( 'type="text"', $rendered_output );
	}
}
