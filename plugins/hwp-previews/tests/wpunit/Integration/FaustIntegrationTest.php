<?php

declare(strict_types=1);

namespace HWP\Previews\Tests\Integration;

use HWP\Previews\Integration\Faust_Integration;
use lucatume\WPBrowser\TestCase\WPTestCase;
use ReflectionClass;

/**
 * Test class for Faust_Integration.
 */
class Faust_Integration_Test extends WPTestCase {

	public function setUp() : void {
		parent::setUp();

		// Reset singleton instance before each test
		$reflection       = new ReflectionClass( Faust_Integration::class );
		$instanceProperty = $reflection->getProperty( 'instance' );
		$instanceProperty->setAccessible( true );
		$instanceProperty->setValue( null, null );
	}


	public function test_instance_creates_and_sets_up_faust_integration_when_not_set() {
		$reflection       = new ReflectionClass( Faust_Integration::class );
		$instanceProperty = $reflection->getProperty( 'instance' );
		$instanceProperty->setAccessible( true );
		$instanceProperty->setValue( null );

		$this->assertNull( $instanceProperty->getValue() );
		$instance = Faust_Integration::init();

		$this->assertInstanceOf( Faust_Integration::class, $instanceProperty->getValue() );
		$this->assertSame( $instance, $instanceProperty->getValue(), 'Faust_Integration::init() should set the static instance property' );

		$this->assertFalse( $instance->get_faust_enabled() );
		$this->assertFalse( $instance->get_faust_configured() );

	}


	public function test_instance_configure_faust() {

		// Mock FaustWP exists
		tests_add_filter( 'pre_option_active_plugins', function ( $plugins ) {
			$plugins[] = 'faustwp/faustwp.php';

			return $plugins;
		} );

		$instance = Faust_Integration::init();
		$this->assertTrue( $instance->get_faust_enabled() );
		$this->assertTrue( $instance->get_faust_configured() );
	}


	public function test_dismiss_faust_notice_meta_value() {
		$instance = Faust_Integration::init();

		$admin_user = WPTestCase::factory()->user->create_and_get( [
			'role' => 'administrator',
			'meta_input' => [
				'first_name' => 'Test',
				'last_name'  => 'User',
			],
			'user_login' => 'testuser'
		] );

		// Set the current user to the admin user
		$original_user_id = get_current_user_id();
		wp_set_current_user( $admin_user->ID );

		// Set the user meta and check
		$instance::dismiss_faust_admin_notice();
		$this->assertEquals(
			1,
			get_user_meta( $admin_user->ID, Faust_Integration::FAUST_NOTICE_KEY, true )
		);

		$this->assertFalse(
			get_user_meta( $original_user_id, Faust_Integration::FAUST_NOTICE_KEY, true )
		);

		// Reset the current user
		wp_set_current_user( $original_user_id );
	}


	public function test_faust_frontend_url_default_url() {

		$instance = Faust_Integration::init();
		$this->assertEquals( $instance->get_faust_frontend_url(), 'http://localhost:3000' );

	}

	public function test_faust_frontend_url_with_faust_setting() {

		// Mock FaustWP exists
		tests_add_filter( 'pre_option_active_plugins', function ( $plugins ) {
			$plugins[] = 'faustwp/faustwp.php';

			return $plugins;
		} );

		$frontend_uri = 'https://mocked-frontend.com';
		$GLOBALS['_test_faustwp_frontend_uri'] = $frontend_uri;

		$instance = Faust_Integration::init();
		$this->assertTrue( function_exists( '\WPE\FaustWP\Settings\faustwp_get_setting' ) );
		$this->assertEquals( $frontend_uri, $instance->get_faust_frontend_url() );

		unset( $GLOBALS['_test_faustwp_frontend_uri'] );
	}
}

// Stub the FaustWP settings function for tests. The production code guards all
// calls with function_exists(), so this only runs when FaustWP is not installed.
namespace WPE\FaustWP\Settings;

if ( ! function_exists( 'WPE\FaustWP\Settings\faustwp_get_setting' ) ) {
	function faustwp_get_setting( string $key, string $default = '' ): string {
		return $GLOBALS[ '_test_faustwp_' . $key ] ?? $default;
	}
}
