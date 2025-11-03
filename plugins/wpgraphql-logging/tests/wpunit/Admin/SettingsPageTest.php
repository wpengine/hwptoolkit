<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Tests\Admin;

use lucatume\WPBrowser\TestCase\WPTestCase;
use WPGraphQL\Logging\Admin\SettingsPage;
use ReflectionClass;
use WPGraphQL\Logging\Admin\Settings\Fields\SettingsFieldCollection;


/**
 * Test class for SettingsPage.
 *
 * @package WPGraphQL\Logging
 *
 * @since 0.0.1
 */
class SettingsPageTest extends WPTestCase {

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
		$reflection       = new ReflectionClass( SettingsPage::class );
		$instanceProperty = $reflection->getProperty( 'instance' );
		$instanceProperty->setAccessible( true );
		$instanceProperty->setValue( null );

		$this->assertNull( $instanceProperty->getValue() );
		$instance = SettingsPage::init();
		$instance->setup();

		$this->assertInstanceOf( SettingsPage::class, $instanceProperty->getValue() );
		$this->assertSame( $instance, $instanceProperty->getValue(), 'SettingsPage::init() should set the static instance property' );
	}

	public function test_setup_registers_hooks(): void {
		$page = new SettingsPage();
        $page->setup();

        $this->assertEquals(10, has_action('init', [$page, 'init_field_collection']));
        $this->assertEquals(10, has_action('admin_menu', [$page, 'register_settings_page']));
        $this->assertEquals(10, has_action('admin_init', [$page, 'register_settings_fields']));
        $this->assertEquals(10, has_action('admin_enqueue_scripts', [$page, 'load_scripts_styles']));

		// Init Field Collection
		$page->init_field_collection();
		$page->register_settings_fields();
		$page->register_settings_page();
		$page->load_scripts_styles('settings_page_' . SettingsPage::PLUGIN_MENU_SLUG);

		$this->assertNotNull($page->get_field_collection(), 'Field collection should be initialized in setup');
		$this->assertInstanceOf( SettingsFieldCollection::class, $page->get_field_collection(), 'Field collection should be initialized in setup' );
	}

	public function test_init_field_collection_initializes_field_collection(): void {

		$page = SettingsPage::init();
		$page->setup();
		$page->init_field_collection();

		$this->assertNotNull($page->get_field_collection(), 'Field collection should be initialized in setup');
		$this->assertInstanceOf( SettingsFieldCollection::class, $page->get_field_collection(), 'Field collection should be initialized in setup' );
    }

    public function test_register_settings_page_no_field_collection_does_nothing(): void {
        global $submenu;
        $submenu = []; // reset

        $page = new SettingsPage();
        // Do not call init_field_collection() to trigger early return
        $page->register_settings_page();

        $this->assertArrayNotHasKey('options-general.php', $submenu);
    }

    public function test_get_current_tab_behaviour(): void {
        $page = new SettingsPage();

        // With no tabs provided -> default
        $this->assertSame('basic_configuration', $page->get_current_tab([]));

        // Provide custom tabs and no $_GET -> default
        $tabs = [
            'basic_configuration' => new class implements \WPGraphQL\Logging\Admin\Settings\Fields\Tab\SettingsTabInterface {
                public static function get_name(): string { return 'basic_configuration'; }
                public static function get_label(): string { return 'Basic Configuration'; }
                public function get_fields(): array { return []; }
            },
            'advanced' => new class implements \WPGraphQL\Logging\Admin\Settings\Fields\Tab\SettingsTabInterface {
                public static function get_name(): string { return 'advanced'; }
                public static function get_label(): string { return 'Advanced'; }
                public function get_fields(): array { return []; }
            },
        ];

        $this->assertSame('basic_configuration', $page->get_current_tab($tabs));

        // Invalid tab -> default
        $_GET['tab'] = 'does_not_exist';
        $this->assertSame('basic_configuration', $page->get_current_tab($tabs));

        // Valid tab -> returns it
        $_GET['tab'] = 'advanced';
		$_GET['wpgraphql_logging_settings_tab_nonce'] = wp_create_nonce( 'wpgraphql-logging-settings-tab-action' );
        $this->assertSame('advanced', $page->get_current_tab($tabs));
    }
}
