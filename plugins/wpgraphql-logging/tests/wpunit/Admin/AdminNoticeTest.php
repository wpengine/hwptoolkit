<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Tests\Admin;

use lucatume\WPBrowser\TestCase\WPTestCase;
use WPGraphQL\Logging\Admin\AdminNotice;
use ReflectionClass;
use Mockery;


/**
 * Test class for AdminNotice.
 *
 * @package WPGraphQL\Logging
 *
 * @since 0.0.1
 */
class AdminNoticeTest extends WPTestCase {

	public function set_as_admin(): void {
		$admin_user = $this->factory->user->create(['role' => 'administrator']);
		wp_set_current_user($admin_user);
		set_current_screen('dashboard');
	}

	public function set_dismissed_notice_meta_data(): void {
		update_user_meta( get_current_user_id(), AdminNotice::ADMIN_NOTICE_KEY, 1 );
	}

	public function unset_dismissed_notice_meta_data(): void {
		delete_user_meta( get_current_user_id(), AdminNotice::ADMIN_NOTICE_KEY );
	}

	public function test_admin_notice_instance() {
		$this->set_as_admin();

		$reflection       = new ReflectionClass( AdminNotice::class );
		$instanceProperty = $reflection->getProperty( 'instance' );
		$instanceProperty->setAccessible( true );
		$instanceProperty->setValue( null );

		$this->assertNull( $instanceProperty->getValue() );
		$instance = AdminNotice::init();

		$this->assertInstanceOf( AdminNotice::class, $instanceProperty->getValue() );
		$this->assertSame( $instance, $instanceProperty->getValue(), 'AdminNotice::init() should set the static instance property' );
	}

	public function test_initialization_sets_instance_with_no_hooks_registered_as_not_admin(): void {
		wp_set_current_user(0);
		$instance = AdminNotice::init();
		$this->assertFalse(has_action('admin_notices', [$instance, 'register_admin_notice']));
		$this->assertFalse(has_action('wp_ajax_' . AdminNotice::ADMIN_NOTICE_KEY, [$instance, 'process_ajax_request']));
	}

	public function test_initialization_sets_instance_with_no_hooks_registered_as_dismissed(): void {
		$this->set_as_admin();
		$this->set_dismissed_notice_meta_data();

		$instance = AdminNotice::init();
		$this->assertFalse(has_action('admin_notices', [$instance, 'register_admin_notice']));
		$this->assertFalse(has_action('wp_ajax_' . AdminNotice::ADMIN_NOTICE_KEY, [$instance, 'process_ajax_request']));
	}

	public function test_check_notice_is_displayed(): void {
		$this->set_as_admin();

		$reflection       = new ReflectionClass( AdminNotice::class );
		$instanceProperty = $reflection->getProperty( 'instance' );
		$instanceProperty->setAccessible( true );
		$instanceProperty->setValue( null );

		$notice = AdminNotice::init(); // Notice should be now registered

		$hook = has_action('admin_notices', [$notice, 'register_admin_notice']);
		$this->assertNotFalse($hook);
		$this->assertEquals(10, $hook);

		$ajax_hook = has_action('wp_ajax_' . AdminNotice::ADMIN_NOTICE_KEY, [$notice, 'process_ajax_request']);
		$this->assertNotFalse($ajax_hook);
		$this->assertEquals(10, $ajax_hook);
	}

	public function test_register_admin_notice_outputs_template(): void {
		$this->set_as_admin();

		$notice = AdminNotice::init();

		ob_start();
		$notice->register_admin_notice();
		$output = ob_get_clean();

		$this->assertStringContainsString('wpgraphql-logging-admin-notice', $output);
		$this->assertStringContainsString('notice-warning', $output);
		$this->assertStringContainsString('is-dismissible', $output);
		$this->assertStringContainsString('Heads up! While very useful for debugging', $output);
		$this->assertStringContainsString('<script>', $output);
	}

	public function test_dismiss_admin_notice_updates_user_meta(): void {
		$this->set_as_admin();

		$notice = AdminNotice::init();
		$this->assertFalse($notice->is_notice_dismissed());

		// Dismiss the notice.
		AdminNotice::dismiss_admin_notice();
		$this->assertTrue($notice->is_notice_dismissed());
	}

	public function test_process_ajax_request_dismisses_notice(): void {
		$this->set_as_admin();
		$this->unset_dismissed_notice_meta_data();

		$notice = AdminNotice::init();
		$this->assertFalse($notice->is_notice_dismissed());

		$notice::dismiss_admin_notice();
		$this->assertTrue($notice->is_notice_dismissed());
	}

}
