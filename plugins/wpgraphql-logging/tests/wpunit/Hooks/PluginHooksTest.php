<?php

declare( strict_types=1 );

namespace WPGraphQL\Logging\Tests\Hooks;

use WPGraphQL\Logging\Hooks\PluginHooks;
use lucatume\WPBrowser\TestCase\WPTestCase;
use ReflectionClass;
use WPGraphQL\Logging\Logger\Database\DatabaseEntity;

/**
 * Class PluginHooksTest
 *
 * Tests for the PluginHooks class.
 *
 * @package WPGraphQL\Logging
 * @since 0.0.1
 */
class PluginHooksTest extends WPTestCase {

	public function setUp(): void {
		parent::setUp();
		$this->drop_table();
	}

	public function drop_table(): void {
		DatabaseEntity::drop_table();
	}

	public function test_instance_from_plugin_instance() {
		$instance = PluginHooks::init();
		$this->assertTrue( $instance instanceof PluginHooks );
	}

	public function test_singleton_returns_same_instance() {
		$first  = PluginHooks::init();
		$second = PluginHooks::init();
		$this->assertSame( $first, $second, 'PluginHooks::instance() should always return the same instance' );
	}

	public function test_instance_creates_and_sets_up_plugin_when_not_set() {
		$reflection       = new ReflectionClass( PluginHooks::class );
		$instanceProperty = $reflection->getProperty( 'instance' );
		$instanceProperty->setAccessible( true );
		$instanceProperty->setValue( null );

		$this->assertNull( $instanceProperty->getValue() );
		$instance = PluginHooks::init();

		$this->assertInstanceOf( PluginHooks::class, $instanceProperty->getValue() );
		$this->assertSame( $instance, $instanceProperty->getValue(), 'Plugin::instance() should set the static instance property' );
	}


	public function test_database_table_creation() {
		global $wpdb;

		// Setup plugin and run activation hook
		PluginHooks::init();
		wpgraphql_logging_activation_callback();

		// Check if the table now exists
		$table_exists = $wpdb->get_var( $wpdb->prepare(
			"SHOW TABLES LIKE %s",
			DatabaseEntity::get_table_name()
		) );

		$this->assertEquals( DatabaseEntity::get_table_name(), $table_exists, 'Database table should be created by setup method.' );
	}
}
