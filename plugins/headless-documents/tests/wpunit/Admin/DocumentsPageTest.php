<?php

declare( strict_types=1 );

namespace Headless\Documents\Tests\Admin;

use Headless\Documents\Admin\Documents_Page;
use Headless\Documents\Identifiers;
use Headless\Documents\Store\Document_Store;
use Headless\Documents\Store\Document_Table;
use lucatume\WPBrowser\TestCase\WPTestCase;

/**
 * Tests for the read-only Tools → Headless Documents screen.
 */
class DocumentsPageTest extends WPTestCase {


	public function setUp(): void {
		parent::setUp();

		delete_option( Identifiers::DB_VERSION_OPTION );
		Document_Table::maybe_install();
		wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );
		set_current_screen( 'dashboard' );
	}

	public function tearDown(): void {
		global $submenu, $_registered_pages;

		unset( $_GET['collection'], $_GET['document'], $_GET['paged'] );
		remove_all_filters( 'wp_die_handler' );
		Document_Table::uninstall();
		unset( $submenu['tools.php'] );
		$_registered_pages = [];

		parent::tearDown();
	}

	private function page_is_registered() {
		return (bool) get_plugin_page_hook( Identifiers::ADMIN_PAGE_SLUG, 'tools.php' );
	}

	private function render() {
		ob_start();
		try {
			Documents_Page::render();
		} finally {
			$output = ob_get_clean();
		}
		return $output;
	}

	public function test_page_is_registered_under_tools() {
		Documents_Page::register();

		$this->assertTrue( $this->page_is_registered() );
	}

	public function test_render_denies_users_without_the_read_capability() {
		wp_set_current_user( self::factory()->user->create( [ 'role' => 'editor' ] ) );
		add_filter(
			'wp_die_handler',
			function () {
				return function ( $message ) {
					throw new \WPDieException( is_string( $message ) ? $message : 'denied' );
				};
			}
		);

		$this->expectException( \WPDieException::class );
		$this->render();
	}

	public function test_list_shows_the_document_collection_id_and_version() {
		Document_Store::save( 'notes', 'team-notes', 0, [ 'title' => 'First' ] );
		Document_Store::save( 'notes', 'team-notes', 1, [ 'title' => 'Second' ] );

		$output = $this->render();

		$this->assertStringContainsString( '<code>notes</code>', $output );
		$this->assertStringContainsString( '<code>team-notes</code>', $output );
		$this->assertStringContainsString( '<td>2</td>', $output );
		$this->assertStringContainsString( wp_get_current_user()->display_name, $output );
	}

	public function test_list_explains_how_documents_are_created_when_empty() {
		$this->assertStringContainsString( 'No documents yet', $this->render() );
	}

	public function test_detail_view_escapes_document_data() {
		Document_Store::save( 'notes', 'xss', 0, [ 'note' => '<script>alert(1)</script>' ] );
		$_GET['collection'] = 'notes';
		$_GET['document']   = 'xss';

		$output = $this->render();

		$this->assertStringNotContainsString( '<script>', $output );
		$this->assertStringContainsString( '&lt;script&gt;alert(1)&lt;/script&gt;', $output );
	}

	public function test_detail_view_reports_a_missing_or_invalid_document() {
		$_GET['document'] = 'Not A Slug!';

		$this->assertStringContainsString( 'Document not found.', $this->render() );
	}
}
