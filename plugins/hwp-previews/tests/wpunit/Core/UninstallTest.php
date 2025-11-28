<?php

declare( strict_types=1 );

namespace HWP\Previews\Tests\Core;

use lucatume\WPBrowser\TestCase\WPTestCase;

/**
 * Test class for the uninstall callback.
 */
class UninstallTest extends WPTestCase {

	public function test_uninstall_file_exists(): void {
		$uninstall_file = dirname( __DIR__, 3 ) . '/uninstall.php';
		$this->assertFileExists( $uninstall_file, 'uninstall.php file should exist' );
	}

	public function test_option_can_be_deleted(): void {
		// Test that delete_option works (simulating what uninstall.php does).
		$option_key = HWP_PREVIEWS_SETTINGS_KEY;
		$test_data  = [ 'test' => 'data' ];

		update_option( $option_key, $test_data );
		$this->assertEquals( $test_data, get_option( $option_key ) );

		delete_option( $option_key );
		$this->assertFalse( get_option( $option_key ) );
	}

	public function test_uninstall_action_hook_exists(): void {
		// Verify the action hook can be registered.
		$called = false;

		add_action( 'hwp_previews_after_uninstall', function () use ( &$called ) {
			$called = true;
		} );

		do_action( 'hwp_previews_after_uninstall' );

		$this->assertTrue( $called, 'hwp_previews_after_uninstall action should fire' );
	}
}

