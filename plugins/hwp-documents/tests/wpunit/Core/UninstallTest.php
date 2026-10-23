<?php

declare( strict_types=1 );

namespace HWP\Documents\Tests\Core;

use HWP\Documents\Identifiers;
use HWP\Documents\Store\Document_Store;
use HWP\Documents\Store\Document_Table;
use lucatume\WPBrowser\TestCase\WPTestCase;

/**
 * Tests for uninstall.php.
 */
class UninstallTest extends WPTestCase {

	/**
	 * Runs before the test below defines the constant, which can't be undone.
	 */
	public function test_uninstall_keeps_documents_without_the_constant() {
		if ( defined( 'HWP_DOCUMENTS_UNINSTALL_PLUGIN' ) ) {
			$this->markTestSkipped( 'HWP_DOCUMENTS_UNINSTALL_PLUGIN is already defined.' );
		}

		delete_option( Identifiers::DB_VERSION_OPTION );
		Document_Table::maybe_install();
		Document_Store::save( 'prefs', 'kept', 0, [ 'theme' => 'dark' ] );

		if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
			define( 'WP_UNINSTALL_PLUGIN', 'hwp-documents/hwp-documents.php' );
		}
		include dirname( __DIR__, 3 ) . '/uninstall.php';

		$this->assertEquals( (object) [ 'theme' => 'dark' ], Document_Store::get( 'prefs', 'kept' )['data'] );
		$this->assertNotFalse( get_option( Identifiers::DB_VERSION_OPTION ) );
	}

	public function test_uninstall_drops_the_table_and_its_version() {
		global $wpdb;

		if ( ! defined( 'HWP_DOCUMENTS_UNINSTALL_PLUGIN' ) ) {
			define( 'HWP_DOCUMENTS_UNINSTALL_PLUGIN', true );
		}

		delete_option( Identifiers::DB_VERSION_OPTION );
		Document_Table::maybe_install();
		Document_Store::save( 'prefs', 'settings', 0, [ 'theme' => 'dark' ] );
		$fired = false;
		add_action(
			'hwp_documents_after_uninstall',
			function () use ( &$fired ) {
				$fired = true;
			}
		);

		if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
			define( 'WP_UNINSTALL_PLUGIN', 'hwp-documents/hwp-documents.php' );
		}
		include dirname( __DIR__, 3 ) . '/uninstall.php';

		// The test suite creates temporary tables, which SHOW TABLES doesn't list.
		$suppress = $wpdb->suppress_errors( true );
		$exists   = false !== $wpdb->query( 'SELECT 1 FROM ' . Document_Table::name() . ' LIMIT 1' );
		$wpdb->suppress_errors( $suppress );

		$this->assertFalse( $exists );
		$this->assertFalse( get_option( Identifiers::DB_VERSION_OPTION ) );
		$this->assertTrue( $fired );
	}
}
