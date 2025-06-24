<?php

namespace HWP\Previews\wpunit\Admin;

use HWP\Previews\Admin\Settings_Page;
use HWP\Previews\Preview\Post\Post_Preview_Service;
use lucatume\WPBrowser\TestCase\WPTestCase;
use ReflectionClass;

class SettingsPageTest extends WPTestCase {

	public function set_as_admin() {
		// Will set is_admin() to true
		$GLOBALS['current_screen'] = new class {
			public function in_admin( $context = null ) {
				if ( $context === null ) {
					return true;
				}

				return $context === 'user';
			}
		};
	}

	public function unset_as_admin() {
		unset( $GLOBALS['current_screen'] );
	}

	public function test_settings_page_instance() {

		$this->set_as_admin();

		$reflection       = new ReflectionClass( Settings_Page::class );
		$instanceProperty = $reflection->getProperty( 'instance' );
		$instanceProperty->setAccessible( true );
		$instanceProperty->setValue( null );

		$this->assertNull( $instanceProperty->getValue() );
		$instance = Settings_Page::init();

		$this->assertInstanceOf( Settings_Page::class, $instanceProperty->getValue() );
		$this->assertSame( $instance, $instanceProperty->getValue(), 'Settings_Page::init() should set the static instance property' );
		$this->unset_as_admin();
	}

	public function test_get_current_tab() {
		$this->set_as_admin();
		$_GET['attachment'] = 'attachment';
		$settings_page      = Settings_Page::init();

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
		$settings_page = new Settings_Page();
		$this->assertNull( $settings_page->register_settings_page() );
		$this->assertNull( $settings_page->register_settings_fields() );
		$this->assertNull( $settings_page->load_scripts_styles( 'settings_page_hwp-previews' ) );
		$this->assertNull( $settings_page->load_scripts_styles( 'invalid-previews' ) );
	}
}
