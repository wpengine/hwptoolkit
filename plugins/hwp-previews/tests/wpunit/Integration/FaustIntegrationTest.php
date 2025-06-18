<?php

declare( strict_types=1 );

namespace HWP\Previews\Tests\Integration;

use HWP\Previews\Integration\Faust_Integration;
use lucatume\WPBrowser\TestCase\WPTestCase;

/**
 * Note: A lot of the functionality is tested with e2e tests
 *
 * Test class for Faust_Integration
 */
class Faust_Integration_Test extends WPTestCase {

	protected function setUp(): void {
		parent::setUp();

		// Reset the singleton instance before each test
		$reflection        = new \ReflectionClass( Faust_Integration::class );
		$instance_property = $reflection->getProperty( 'instance' );
		$instance_property->setAccessible( true );
		$instance_property->setValue( null, null );
	}

	/**
	 * Test that init() returns a singleton instance
	 */
	public function test_init_returns_singleton_instance(): void {
		$instance1 = Faust_Integration::init();
		$instance2 = Faust_Integration::init();

		$this->assertInstanceOf( Faust_Integration::class, $instance1 );
		$this->assertSame( $instance1, $instance2, 'init() should return the same singleton instance' );
	}

//	public function test_is_faust_enabled_asserts_false() {
//
//	}

	public function test_is_faust_enabled_asserts_true() {

		// Mock FaustWP exists
		tests_add_filter( 'pre_option_active_plugins', function ( $plugins ) {
			$plugins[] = 'faustwp/faustwp.php';

			return $plugins;
		} );

		$instance = Faust_Integration::init();
		$this->assertTrue( $instance->is_faust_enabled() );
	}

	public function test_is_faust_enabled_asserts_false() {

		$instance = Faust_Integration::init();
		$this->assertFalse( $instance->is_faust_enabled() );
	}
}
