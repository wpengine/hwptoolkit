<?php

declare( strict_types=1 );

namespace Headless\Documents\Tests\Core;

use Headless\Documents\Identifiers;
use Headless\Documents\Store\Document_Store;
use Headless\Documents\Store\Document_Table;
use lucatume\WPBrowser\TestCase\WPTestCase;

/**
 * Tests for uninstall.php.
 */
class UninstallTest extends WPTestCase {

	public function test_uninstall_drops_the_table_and_its_version() {
		global $wpdb;

		delete_option( Identifiers::DB_VERSION_OPTION );
		Document_Table::maybe_install();
		Document_Store::save( 'prefs', 'settings', 0, [ 'theme' => 'dark' ] );
		$fired = false;
		add_action(
			'headless_documents_after_uninstall',
			function () use ( &$fired ) {
				$fired = true;
			}
		);

		if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
			define( 'WP_UNINSTALL_PLUGIN', 'headless-documents/headless-documents.php' );
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
