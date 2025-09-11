<?php

namespace HWP\Previews\wpunit\Admin;

use HWP\Previews\Admin\Settings_Page;
use HWP\Previews\Preview\Post\Post_Preview_Service;
use lucatume\WPBrowser\TestCase\WPTestCase;
use ReflectionClass;

class SettingsPageTest extends WPTestCase {

	public function setup() : void {
		parent::setup();
		// Will set is_admin() to true
		$GLOBALS['current_screen'] = new class {
			public function in_admin( $context = null ) {
				if ( $context === null ) {
					return true;
				}

				return $context === 'user';
			}
		};

		// Reset the instance before each test
		$reflection       = new ReflectionClass( Settings_Page::class );
		$instanceProperty = $reflection->getProperty( 'instance' );
		$instanceProperty->setAccessible( true );
		$instanceProperty->setValue( null );
	}

	public function tearDown() : void {
		// Reset the current screen to avoid affecting other tests
		unset( $GLOBALS['current_screen'] );
		parent::tearDown();
	}

	public function test_init_returns_null_if_not_admin() {
		// Unset the admin screen mock
		unset( $GLOBALS['current_screen'] );

		$instance = Settings_Page::init();
		$this->assertNull( $instance, 'Settings_Page::init() should return null if not in admin.' );
	}

	public function test_init_returns_null_for_user_without_manage_options_cap() {
		$user_id = self::factory()->user->create( [ 'role' => 'subscriber' ] );
		wp_set_current_user( $user_id );

		$instance = Settings_Page::init();
		$this->assertNull( $instance, 'Settings_Page::init() should return null for users without manage_options capability.' );
	}

	public function test_init_returns_instance_for_user_with_manage_options_cap() {
		$user_id = self::factory()->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $user_id );

		$instance = Settings_Page::init();
		$this->assertInstanceOf( Settings_Page::class, $instance, 'Settings_Page::init() should return an instance for users with manage_options capability.' );
	}

	public function test_settings_page_instance() {
		$reflection       = new ReflectionClass( Settings_Page::class );
		$instanceProperty = $reflection->getProperty( 'instance' );
		$instanceProperty->setAccessible( true );

		$this->assertNull( $instanceProperty->getValue() );

		// To pass the capability check
		$user_id = self::factory()->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $user_id );
		$instance = Settings_Page::init();

		$this->assertInstanceOf( Settings_Page::class, $instanceProperty->getValue() );
		$this->assertSame( $instance, $instanceProperty->getValue(), 'Settings_Page::init() should set the static instance property' );
	}

	public function test_get_current_tab() {
		$_GET['attachment'] = 'attachment';

		// To pass the capability check
		$user_id = self::factory()->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $user_id );
		$settings_page = Settings_Page::init();

		$post_preview_service = new Post_Preview_Service();
		$post_types           = $post_preview_service->get_post_types();


		$tab = $settings_page->get_current_tab( [], 'attachment' );
		$this->assertSame( '', $tab );

		$tab = $settings_page->get_current_tab( $post_types, 'page' );
		$this->assertEquals( 'post', $tab );

		$tab = $settings_page->get_current_tab( $post_types, 'attachment' );
		$this->assertSame( 'attachment', $tab );
	}

	public function test_register_hooks() {
		// To pass the capability check
		$user_id = self::factory()->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $user_id );
		$settings_page = Settings_Page::init();
		$this->assertNull( $settings_page->register_settings_page() );
		$this->assertNull( $settings_page->register_settings_fields() );
		$this->assertNull( $settings_page->load_scripts_styles( 'settings_page_hwp-previews' ) );
		$this->assertNull( $settings_page->load_scripts_styles( 'invalid-previews' ) );
	}
}
