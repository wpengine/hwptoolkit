<?php

declare( strict_types=1 );

namespace HWP\Previews\wpunit\Admin\Settings\Menu;

use HWP\Previews\Admin\Settings\Menu\Menu_Page;
use lucatume\WPBrowser\TestCase\WPTestCase;

class MenuPageTest extends WPTestCase {

	protected $admin_user_id;

	protected $current_user_id;
	protected string $template_file = '';

	protected string $page_title = 'HWP Previews Settings';

	protected string  $menu_title = 'HWP Previews Settings';

	protected string  $menu_slug = 'hwp-previews';

	protected array $args = [
		'hwp_previews_main_page_config' => [
			'tabs'        => [
				'post' => 'Posts',
				'page' => 'Pages'
			],
			'current_tab' => 'post',
			'params'      => ''
		],
	];

	protected Menu_Page $menu_page;

	public function setUp(): void {
		parent::setUp();

		$this->current_user_id = get_current_user_id();

		// Create an administrator user for testing.
		$this->admin_user_id = $this->factory()->user->create( [
			'role' => 'administrator'
		] );
		wp_set_current_user( $this->admin_user_id );

		// Create a temporary template file for testing
		$this->template_file = sys_get_temp_dir() . '/test-template-' . uniqid() . '.php';
		file_put_contents( $this->template_file, '<?php echo "Test template content"; ?>' );

		$this->menu_page = new Menu_Page(
			$this->page_title,
			$this->menu_title,
			$this->menu_slug,
			$this->template_file,
			$this->args
		);
	}

	public function tearDown(): void {
		// Clean up the temporary template file
		if ( file_exists( $this->template_file ) ) {
			unlink( $this->template_file );
		}

		// Clean up the user
		wp_delete_user( $this->admin_user_id );
		wp_set_current_user( $this->current_user_id );

		parent::tearDown();
	}

	public function test_constructor_sets_properties_correctly() {

		$page = $this->menu_page;

		$reflection = new \ReflectionClass( $page );

		$page_title_prop = $reflection->getProperty( 'page_title' );
		$page_title_prop->setAccessible( true );
		$this->assertEquals( $this->page_title, $page_title_prop->getValue( $page ) );

		$menu_title_prop = $reflection->getProperty( 'menu_title' );
		$menu_title_prop->setAccessible( true );
		$this->assertEquals( $this->menu_title, $menu_title_prop->getValue( $page ) );

		$menu_slug_prop = $reflection->getProperty( 'menu_slug' );
		$menu_slug_prop->setAccessible( true );
		$this->assertEquals( $this->menu_slug, $menu_slug_prop->getValue( $page ) );

		$template_prop = $reflection->getProperty( 'template' );
		$template_prop->setAccessible( true );
		$this->assertEquals( $this->template_file, $template_prop->getValue( $page ) );

		$args_prop = $reflection->getProperty( 'args' );
		$args_prop->setAccessible( true );
		$this->assertEquals( $this->args, $args_prop->getValue( $page ) );
	}

	public function test_constructor_with_empty_args() {
		$page = new Menu_Page(
			$this->page_title,
			$this->menu_title,
			$this->menu_slug,
			$this->template_file,
		);

		$reflection = new \ReflectionClass( $page );
		$args_prop  = $reflection->getProperty( 'args' );
		$args_prop->setAccessible( true );
		$this->assertEquals( [], $args_prop->getValue( $page ) );
	}

	public function test_register_page_adds_submenu_correctly() {
		global $submenu, $_registered_pages, $_parent_pages;

		$page = $this->menu_page;

		// Verify the current user has the required capability
		$this->assertTrue( current_user_can( 'manage_options' ) );

		// Capture the state before registration
		$submenu_before = $submenu;

		$page->register_page();

		// Verify that the submenu was modified
		$this->assertNotEquals( $submenu_before, $submenu );

		// Check that the submenu item was added to options-general.php
		$this->assertArrayHasKey( 'options-general.php', $submenu );

		// Find the added submenu item
		$found_item = null;
		foreach ( $submenu['options-general.php'] as $item ) {
			if ( $item[2] === $this->menu_slug ) {
				$found_item = $item;
				break;
			}
		}

		$this->assertNotNull( $found_item, 'Should find the added submenu item' );
		$this->assertEquals( $this->menu_title, $found_item[0], 'Menu title should match' );
		$this->assertEquals( $this->menu_slug, $found_item[2], 'Menu slug should match' );
		$this->assertEquals( $this->page_title, $found_item[3], 'Page title should match' );
		$this->assertEquals( 'manage_options', $found_item[1], 'Capability should be manage_options' );

		// Verify page was registered and parent relationship was set
		$expected_hookname = get_plugin_page_hookname( $this->menu_slug, 'options-general.php' );
		$this->assertArrayHasKey( $expected_hookname, $_registered_pages );
		$this->assertEquals( 'options-general.php', $_parent_pages[$this->menu_slug] );
	}

	public function test_register_page_without_manage_options_capability() {
		global $submenu, $_wp_submenu_nopriv;

		// Create a subscriber user (no manage_options capability)
		$subscriber_id = $this->factory()->user->create( [
			'role' => 'subscriber'
		] );

		wp_set_current_user( $subscriber_id );

		$page = $this->menu_page;

		// Verify the current user doesn't have the required capability
		$this->assertFalse( current_user_can( 'manage_options' ) );

		// Capture the state before registration
		$submenu_before = $submenu;

		$page->register_page();

		// When user lacks capability, the submenu should not be modified
		// but the page should be marked as no-privilege
		$this->assertEquals( $submenu_before, $submenu, 'Submenu should not be modified when user lacks capability' );
		$this->assertTrue( $_wp_submenu_nopriv['options-general.php'][$this->menu_slug], 'Should mark page as no-privilege' );

		// Clean up
		wp_delete_user( $subscriber_id );
		wp_set_current_user( $this->admin_user_id );
	}

	public function test_registration_callback_with_nonexistent_template() {
		$page = new Menu_Page(
			$this->page_title,
			$this->menu_title,
			$this->menu_slug,
			'/path/to/nonexistent/template.php'
		);

		// Capture output
		ob_start();
		$page->registration_callback();
		$output = ob_get_clean();

		$this->assertStringContainsString( 'notice notice-error', $output );
		$this->assertStringContainsString( 'The HWP Previews Settings template does not exist.', $output );
	}

	public function test_registration_callback_sets_query_vars() {
		$args = [
			'test_var_1' => [ 'key1' => 'value1', 'key2' => 'value2' ],
			'test_var_2' => [ 'key3' => 'value3' ]
		];

		$page = new Menu_Page(
			$this->page_title,
			$this->menu_title,
			$this->menu_slug,
			$this->template_file,
			$args
		);

		// Clear any existing query vars
		global $wp_query;
		if ( isset( $wp_query->query_vars['test_var_1'] ) ) {
			unset( $wp_query->query_vars['test_var_1'] );
		}
		if ( isset( $wp_query->query_vars['test_var_2'] ) ) {
			unset( $wp_query->query_vars['test_var_2'] );
		}

		$page->registration_callback();

		// Verify query vars were set
		$this->assertEquals( [ 'key1' => 'value1', 'key2' => 'value2' ], get_query_var( 'test_var_1' ) );
		$this->assertEquals( [ 'key3' => 'value3' ], get_query_var( 'test_var_2' ) );
	}
}
