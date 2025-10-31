<?php

declare( strict_types=1 );

namespace WPGraphQL\Logging\Tests\Core;


use lucatume\WPBrowser\TestCase\WPTestCase;
use WPGraphQL\Logging\Plugin;

/**
 * Test for the activation callback
 *
 * @package WPGraphQL\Logging
 * @since 0.0.1
 */
class ActivationTest extends WPTestCase {

	protected function setUp(): void {
		// Sets the uninstall constant to true to ensure the log service is deactivated.
		if ( ! defined( 'WP_GRAPHQL_LOGGING_UNINSTALL_PLUGIN' ) ) {
			define( 'WP_GRAPHQL_LOGGING_UNINSTALL_PLUGIN', true );
		}
		parent::setUp();
		if ( ! function_exists( 'wpgraphql_logging_activation_callback' ) ) {
			require_once dirname( __DIR__ ) . '/activation.php';
		}
		$this->deactivate();
	}

	public function deactivate(): void {
		$log_service = Plugin::get_log_service();
		$log_service->deactivate();
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
