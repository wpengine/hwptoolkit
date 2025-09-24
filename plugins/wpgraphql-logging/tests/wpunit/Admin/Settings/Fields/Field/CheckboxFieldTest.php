<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\wpunit\Admin\Settings\Fields\Field;

use WPGraphQL\Logging\Admin\Settings\Fields\Field\CheckboxField;
use WPGraphQL\Logging\Admin\Settings\Fields\SettingsFieldInterface;
use lucatume\WPBrowser\TestCase\WPTestCase;

/**
 * Test class for CheckboxField.
 *
 * @package WPGraphQL\Logging
 *
 * @since 0.0.1
 */
class CheckboxFieldTest extends WPTestCase {

	protected ?CheckboxField $field = null;

	protected function setUp(): void {
		parent::setUp();
		$this->field = new CheckboxField(
			'enable_logging',
			'basic_configuration',
			'Enable Logging',
			'custom-css-class',
			'Enable or disable query logging for WPGraphQL requests.'
		);
	}

	public function test_basic_field_properties(): void {
		$field = $this->field;
		$this->assertEquals( 'enable_logging', $field->get_id() );
		$this->assertTrue( $field->should_render_for_tab( 'basic_configuration' ) );
		$this->assertFalse( $field->should_render_for_tab( 'other_tab' ) );
	}

	public function test_sanitize_field(): void {
		$field = $this->field;
		$this->assertTrue( $field->sanitize_field( true ) );
		$this->assertTrue( $field->sanitize_field( 1 ) );
		$this->assertFalse( $field->sanitize_field( false ) );
		$this->assertFalse( $field->sanitize_field( 0 ) );
		$this->assertFalse( $field->sanitize_field( null ) );
	}

	public function test_render_field(): void {
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

		$expected_output = <<<HTML
<span class="wpgraphql-logging-tooltip">
								<span class="dashicons dashicons-editor-help"></span>
								<span id="wpgraphql_logging_settings[basic_configuration][enable_logging]-tooltip" class="tooltip-text description">Enable or disable query logging for WPGraphQL requests.</span>
						</span><input type="checkbox" name="wpgraphql_logging_settings[basic_configuration][enable_logging]" aria-labelledby="wpgraphql_logging_settings[basic_configuration][enable_logging]-tooltip" value="1"  class="custom-css-class" />
HTML;
		$this->assertEquals(
			preg_replace('/[\s\t\r\n]+/', '', $expected_output),
			preg_replace('/[\s\t\r\n]+/', '', $rendered_output)
		);

	}
}
