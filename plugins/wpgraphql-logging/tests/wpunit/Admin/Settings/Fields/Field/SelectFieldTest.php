<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\wpunit\Admin\Settings\Fields\Field;

use WPGraphQL\Logging\Admin\Settings\Fields\Field\Select_Field;
use WPGraphQL\Logging\Admin\Settings\Fields\Settings_Field_Interface;
use lucatume\WPBrowser\TestCase\WPTestCase;

class SelectFieldTest extends WPTestCase {

	protected ?Select_Field $field = null;
	protected ?Select_Field $multipleField = null;

	protected function setUp(): void {
		parent::setUp();
		$this->field = new Select_Field(
			'log_level',
			'basic_configuration',
			'Log Level',
			[
				'debug' => 'Debug',
				'info' => 'Info',
				'warning' => 'Warning',
				'error' => 'Error',
			],
			'custom-css-class',
			'Select the minimum log level for WPGraphQL queries.',
			false
		);

		$this->multipleField = new Select_Field(
			'query_types',
			'basic_configuration',
			'Query Types',
			[
				'query' => 'Query',
				'mutation' => 'Mutation',
				'subscription' => 'Subscription',
			],
			'multiple-select-class',
			'Select which query types to log.',
			true
		);
	}

	public function test_basic_field_properties(): void {
		$field = $this->field;
		$this->assertEquals( 'log_level', $field->get_id() );
		$this->assertTrue( $field->should_render_for_tab( 'basic_configuration' ) );
		$this->assertFalse( $field->should_render_for_tab( 'other_tab' ) );
	}

	public function test_multiple_field_properties(): void {
		$field = $this->multipleField;
		$this->assertEquals( 'query_types', $field->get_id() );
	}

	public function test_sanitize_field_single_select(): void {
		$field = $this->field;

		$this->assertEquals( 'debug', $field->sanitize_field( 'debug' ) );
		$this->assertEquals( 'info', $field->sanitize_field( 'info' ) );
		$this->assertEquals( 'warning', $field->sanitize_field( 'warning' ) );
		$this->assertEquals( 'error', $field->sanitize_field( 'error' ) );
		$this->assertEquals( '', $field->sanitize_field( 'invalid' ) );
		$this->assertEquals( '', $field->sanitize_field( 'critical' ) );
		$this->assertEquals( '', $field->sanitize_field( '' ) );
		$this->assertEquals( '', $field->sanitize_field( '<script>alert("xss")</script>' ) );
		$this->assertEquals( 'debug', $field->sanitize_field( '<b>debug</b>' ) );
		$this->assertEquals( '', $field->sanitize_field( 123 ) );
		$this->assertEquals( '', $field->sanitize_field( true ) );
		$this->assertEquals( '', $field->sanitize_field( false ) );
	}

	public function test_sanitize_field_multiple_select(): void {
		$field = $this->multipleField;

		$this->assertEquals( ['query'], $field->sanitize_field( ['query'] ) );
		$this->assertEquals( ['query', 'mutation'], $field->sanitize_field( ['query', 'mutation'] ) );
		$this->assertEquals( ['query', 'mutation', 'subscription'], $field->sanitize_field( ['query', 'mutation', 'subscription'] ) );
		$this->assertEquals( [], $field->sanitize_field( ['invalid', 'another_invalid'] ) );
		$this->assertEquals( ['query'], $field->sanitize_field( 'query' ) );
		$this->assertEquals( [], $field->sanitize_field( 'invalid' ) );
	}

	public function test_render_field_callback(): void {
		$field = $this->field;
		$option_value = [];
		$setting_key = 'wpgraphql_logging_settings';
		$tab_key = 'basic_configuration';
		$args = [
			'tab_key' => $tab_key,
			'settings_key' => $setting_key,
		];

		// Capture the echoed output using output buffering
		ob_start();
		$field->render_field_callback( $args );
		$rendered_output = ob_get_contents();
		ob_end_clean();

		$this->assertStringContainsString( 'name="wpgraphql_logging_settings[basic_configuration][log_level]"', $rendered_output );
		$this->assertStringContainsString( 'id="log_level"', $rendered_output );
		$this->assertStringContainsString( 'class="custom-css-class"', $rendered_output );
		$this->assertStringContainsString( 'Select the minimum log level for WPGraphQL queries.', $rendered_output );
	}
}
