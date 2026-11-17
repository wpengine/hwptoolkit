<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Tests\Admin\Settings\Fields\Tab;

use lucatume\WPBrowser\TestCase\WPTestCase;
use WPGraphQL\Logging\Admin\Settings\Fields\Field\CheckboxField;
use WPGraphQL\Logging\Admin\Settings\Fields\Field\TextInputField;
use WPGraphQL\Logging\Admin\Settings\Fields\Tab\BasicConfigurationTab;

/**
 * Test cases for the REST fields of the BasicConfigurationTab.
 *
 * @package WPGraphQL\Logging
 *
 * @since 1.1.0
 */
class BasicConfigurationTabTest extends WPTestCase {

	public function test_rest_fields_are_registered(): void {
		$fields = ( new BasicConfigurationTab() )->get_fields();

		$this->assertInstanceOf( CheckboxField::class, $fields[ BasicConfigurationTab::LOG_REST_REQUESTS ] );
		$this->assertInstanceOf( TextInputField::class, $fields[ BasicConfigurationTab::EXCLUDE_REST_ROUTES ] );
		$this->assertSame( BasicConfigurationTab::get_name(), $fields[ BasicConfigurationTab::LOG_REST_REQUESTS ]->tab );
	}

	public function test_log_rest_requests_is_off_unless_checked(): void {
		$field = ( new BasicConfigurationTab() )->get_fields()[ BasicConfigurationTab::LOG_REST_REQUESTS ];

		$this->assertFalse( $field->sanitize_field( null ) );
		$this->assertTrue( $field->sanitize_field( '1' ) );
	}

	public function test_exclude_rest_routes_is_sanitized_as_text(): void {
		$field = ( new BasicConfigurationTab() )->get_fields()[ BasicConfigurationTab::EXCLUDE_REST_ROUTES ];

		$this->assertSame( '/wp/v2/users, /wp/v2/types', $field->sanitize_field( '<b>/wp/v2/users</b>, /wp/v2/types' ) );
	}
}
