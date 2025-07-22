<?php

declare( strict_types=1 );

namespace WPGraphQL\Logging\Tests\Core;


use lucatume\WPBrowser\TestCase\WPTestCase;

/**
 * Test class for the activation callback.
 */
class ActivationTest extends WPTestCase {
	protected function setUp(): void {
		parent::setUp();
		if ( ! function_exists( 'wpgraphql_logging_activation_callback' ) ) {
			require_once dirname( __DIR__ ) . '/activation.php';
		}
	}

	public function test_activation_callback_function_exists(): void {
		$this->assertTrue( function_exists( 'wpgraphql_logging_activation_callback' ) );
	}


	public function test_custom_filter_on_wpgraphql_logging_activate(): void {
		$called = false;

		add_action( 'wpgraphql_logging_activate', function () use ( &$called ) {
			$called = true;
		} );

		wpgraphql_logging_activation_callback();
		$this->assertTrue( $called, 'Custom filter on wpgraphql_logging_activate was not called.' );
	}
}
