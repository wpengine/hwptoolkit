<?php

declare( strict_types=1 );

namespace WPGraphQL\Logging\Tests\Core;


use lucatume\WPBrowser\TestCase\WPTestCase;


/**
 * Test for the deactivation callback
 *
 * @package WPGraphQL\Logging
 * @since 0.0.1
 */
class DeactivationTest extends WPTestCase {
	protected function setUp(): void {
		parent::setUp();
		if ( ! function_exists( 'wpgraphql_logging_deactivation_callback' ) ) {
			require_once dirname( __DIR__ ) . '/deactivate.php';
		}
	}

	public function test_deactivation_callback_function_exists(): void {
		$this->assertTrue( function_exists( 'wpgraphql_logging_deactivation_callback' ) );
	}


	public function test_custom_filter_on_wpgraphql_logging_deactivate(): void {
		$called = false;

		add_action( 'wpgraphql_logging_deactivate', function () use ( &$called ) {
			$called = true;
		} );

		wpgraphql_logging_deactivation_callback();
		$this->assertTrue( $called, 'Custom filter on wpgraphql_logging_deactivate was not called.' );
	}
}
