<?php

namespace HWP\Previews\Tests;


use lucatume\WPBrowser\TestCase\WPTestCase;

class ActivationTest extends WPTestCase {
	protected function setUp(): void {
		parent::setUp();
		if ( ! function_exists( 'hwp_previews_activation_callback' ) ) {
			require_once dirname( __DIR__ ) . '/activation.php';
		}
	}

	public function test_activation_callback_function_exists(): void {
		$this->assertTrue( function_exists( 'hwp_previews_activation_callback' ) );
	}


	public function test_custom_filter_on_hwp_previews_activate(): void {
		$called = false;

		add_action( 'hwp_previews_activate', function () use ( &$called ) {
			$called = true;
		} );

		$callback = hwp_previews_activation_callback();
		$callback();

		$this->assertTrue( $called, 'Custom filter on hwp_previews_activate was not called.' );
	}
}
