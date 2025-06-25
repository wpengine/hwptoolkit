<?php

declare( strict_types=1 );

namespace HWP\Previews\Tests\Core;


use lucatume\WPBrowser\TestCase\WPTestCase;

/**
 * Test class for the de-activation callback.
 *
 */
class DeactivationTest extends WPTestCase {
	protected function setUp(): void {
		parent::setUp();
		if ( ! function_exists( 'hwp_previews_deactivation_callback' ) ) {
			require_once dirname( __DIR__ ) . '/deactivate.php';
		}
	}

	public function test_deactivation_callback_function_exists(): void {
		$this->assertTrue( function_exists( 'hwp_previews_deactivation_callback' ) );
	}


	public function test_custom_filter_on_hwp_previews_deactivate(): void {
		$called = false;

		add_action( 'hwp_previews_deactivate', function () use ( &$called ) {
			$called = true;
		} );

		$callback = hwp_previews_deactivation_callback();
		$callback();

		$this->assertTrue( $called, 'Custom filter on hwp_previews_deactivate was not called.' );
	}
}
