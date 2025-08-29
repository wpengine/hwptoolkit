<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\wpunit\Admin;

use lucatume\WPBrowser\TestCase\WPTestCase;
use WPGraphQL\Logging\Admin\Settings_Page;
use ReflectionClass;

class Settings_Page_Test extends WPTestCase {

	public function setUp(): void {
		parent::setUp();
		// Set current user as admin with manage_options capabilities
		$user_id = $this->factory()->user->create([
			'role' => 'administrator',
			'capabilities' => ['manage_options'],
		]);
		wp_set_current_user($user_id);
	}

    protected function tearDown(): void {
        parent::tearDown();
        unset($_GET['tab']);
    }

	public function test_settings_page_instance() {
		$reflection       = new ReflectionClass( Settings_Page::class );
		$instanceProperty = $reflection->getProperty( 'instance' );
		$instanceProperty->setAccessible( true );
		$instanceProperty->setValue( null );

		$this->assertNull( $instanceProperty->getValue() );
		$instance = Settings_Page::init();

		$this->assertInstanceOf( Settings_Page::class, $instanceProperty->getValue() );
		$this->assertSame( $instance, $instanceProperty->getValue(), 'Settings_Page::init() should set the static instance property' );
	}

    public function test_setup_registers_hooks(): void {
        $page = new Settings_Page();
        $page->setup();

        $this->assertEquals(10, has_action('init', [$page, 'init_field_collection']));
        $this->assertEquals(10, has_action('admin_menu', [$page, 'register_settings_page']));
        $this->assertEquals(10, has_action('admin_init', [$page, 'register_settings_fields']));
        $this->assertEquals(10, has_action('admin_enqueue_scripts', [$page, 'load_scripts_styles']));
    }

    public function test_register_settings_page_no_field_collection_does_nothing(): void {
        global $submenu;
        $submenu = []; // reset

        $page = new Settings_Page();
        // Do not call init_field_collection() to trigger early return
        $page->register_settings_page();

        $this->assertArrayNotHasKey('options-general.php', $submenu);
    }

    public function test_get_current_tab_behaviour(): void {
        $page = new Settings_Page();

        // With no tabs provided -> default
        $this->assertSame('basic_configuration', $page->get_current_tab([]));

        // Provide custom tabs and no $_GET -> default
        $tabs = [
            'basic_configuration' => new class implements \WPGraphQL\Logging\Admin\Settings\Fields\Tab\Settings_Tab_Interface {
                public function get_name(): string { return 'basic_configuration'; }
                public function get_label(): string { return 'Basic Configuration'; }
                public function get_fields(): array { return []; }
            },
            'advanced' => new class implements \WPGraphQL\Logging\Admin\Settings\Fields\Tab\Settings_Tab_Interface {
                public function get_name(): string { return 'advanced'; }
                public function get_label(): string { return 'Advanced'; }
                public function get_fields(): array { return []; }
            },
        ];

        $this->assertSame('basic_configuration', $page->get_current_tab($tabs));

        // Invalid tab -> default
        $_GET['tab'] = 'does_not_exist';
        $this->assertSame('basic_configuration', $page->get_current_tab($tabs));

        // Valid tab -> returns it
        $_GET['tab'] = 'advanced';
        $this->assertSame('advanced', $page->get_current_tab($tabs));
    }

    public function test_load_scripts_styles_enqueues_assets_conditionally(): void {
        $page = new Settings_Page();

        // Wrong page hook -> nothing enqueued
        $page->load_scripts_styles('some_other_page');
        $this->assertFalse(wp_style_is('wpgraphql-logging-settings-css', 'enqueued'));
        $this->assertFalse(wp_script_is('wpgraphql-logging-settings-js', 'enqueued'));

        // Correct page hook -> stylesheet should enqueue if file exists; script only if file exists
        $page->load_scripts_styles('settings_page_' . Settings_Page::PLUGIN_MENU_SLUG);

        // CSS is present in this repository, so this should be enqueued
        $this->assertTrue(wp_style_is('wpgraphql-logging-settings-css', 'enqueued'));

        // JS may not exist; expectation: not enqueued if file missing
        $expectedJs = file_exists( trailingslashit( WPGRAPHQL_LOGGING_PLUGIN_DIR ) . 'assets/js/settings/wp-graphql-logging-settings.js' );
        $this->assertSame($expectedJs, wp_script_is('wpgraphql-logging-settings-js', 'enqueued'));
    }


}
