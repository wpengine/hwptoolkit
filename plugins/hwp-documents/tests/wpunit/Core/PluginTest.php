<?php

declare( strict_types=1 );

namespace HWP\Documents\Tests\Core;

use HWP\Documents\Admin\Documents_Page;
use HWP\Documents\Capabilities;
use HWP\Documents\Plugin;
use HWP\Documents\Rest\Documents_Controller;
use HWP\Documents\Store\Document_Table;
use lucatume\WPBrowser\TestCase\WPTestCase;

/**
 * Tests for plugin setup.
 */
class PluginTest extends WPTestCase {

	public function test_constants_are_defined() {
		$this->assertTrue( defined( 'HWP_DOCUMENTS_VERSION' ) );
		$this->assertTrue( defined( 'HWP_DOCUMENTS_PLUGIN_DIR' ) );
		$this->assertTrue( defined( 'HWP_DOCUMENTS_PLUGIN_URL' ) );
	}

	public function test_init_returns_the_same_instance_and_fires_an_action() {
		$fired = 0;
		add_action(
			'hwp_documents_init',
			function () use ( &$fired ) {
				$fired++;
			}
		);

		$this->assertSame( Plugin::init(), Plugin::init() );
		$this->assertSame( 2, $fired );
	}

	public function test_hooks_are_registered() {
		$this->assertSame( 10, has_action( 'init', [ Document_Table::class, 'maybe_install' ] ) );
		$this->assertSame( 10, has_filter( 'wpmu_drop_tables', [ Document_Table::class, 'drop_with_site' ] ) );
		$this->assertSame( 10, has_filter( 'user_has_cap', [ Capabilities::class, 'grant_to_admins' ] ) );
		$this->assertSame( 10, has_action( 'rest_api_init', [ Documents_Controller::class, 'register_routes' ] ) );
		$this->assertSame( 10, has_action( 'admin_menu', [ Documents_Page::class, 'register' ] ) );
	}

	public function test_routes_are_registered_under_the_headless_namespace() {
		$routes = rest_get_server()->get_routes( 'headless/v1' );

		$this->assertCount( 2, preg_grep( '#^/headless/v1/collections/#', array_keys( $routes ) ) );
	}

	public function test_deactivation_fires_an_action() {
		$fired = false;
		add_action(
			'hwp_documents_deactivate',
			function () use ( &$fired ) {
				$fired = true;
			}
		);

		hwp_documents_deactivation_callback();

		$this->assertTrue( $fired );
	}
}
