<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Tests\Admin\Settings\Fields;

use WPGraphQL\Logging\Admin\Settings\Fields\Field\TextIntegerField;
use WPGraphQL\Logging\Admin\Settings\Fields\SettingsFieldInterface;
use WPGraphQL\Logging\Admin\Settings\Fields\Tab\DataManagementTab;
use lucatume\WPBrowser\TestCase\WPTestCase;
use ReflectionClass;


/**
 * Test class for TextIntegerField.
 *
 * @package WPGraphQL\Logging
 *
 * @since 0.0.1
 */
class TextIntegerFieldTest extends WPTestCase {

	protected ?TextIntegerField $field = null;

	protected function setUp(): void {
		parent::setUp();
		$this->field = new TextIntegerField(
			DataManagementTab::DATA_RETENTION_DAYS,
			DataManagementTab::get_name(),
			__( 'Number of Days to Retain Logs', 'wpgraphql-logging' ),
			'',
			__( 'Number of days to retain log data before deletion.', 'wpgraphql-logging' ),
			__( 'e.g., 30', 'wpgraphql-logging' ),
			'30'
		);
	}

	public function test_input_type(): void {
		$field = $this->field;
		$reflection = new ReflectionClass($field);
		$method = $reflection->getMethod('get_input_type');
		$method->setAccessible(true);
		$this->assertEquals( 'number', $method->invoke($field) );
	}

	public function test_sanitize_field() {
		$field = $this->field;

		// Valid integer inputs
		$this->assertEquals( '123', $field->sanitize_field( '123' ) );
		$this->assertEquals( '0', $field->sanitize_field( '0' ) );
		$this->assertEquals( '999999', $field->sanitize_field( '999999' ) );

		// Negative numbers should be converted to positive
		$this->assertEquals( '-123', $field->sanitize_field( '-123' ) );

		// Decimal numbers should be converted to integers
		$this->assertEquals( '123', $field->sanitize_field( '123.45' ) );
		$this->assertEquals( '123', $field->sanitize_field( '123.99' ) );

		// Non-numeric strings should return empty or default
		$this->assertEquals( '0', $field->sanitize_field( 'abc' ) );
		$this->assertEquals( '0', $field->sanitize_field( 'not a number' ) );

		// XSS attempts
		$this->assertEquals( '0', $field->sanitize_field( '<script>alert("xss")</script>' ) );
		$this->assertEquals( '0', $field->sanitize_field( '<script>123</script>' ) );
	}
}
