<?php

declare( strict_types=1 );

namespace HWP\Previews\wpunit;

use HWP\Previews\Plugin;
use lucatume\WPBrowser\TestCase\WPTestCase;
use ReflectionClass;

/**
 * Class PluginTest
 *
 * Tests for the Plugin class
 *
 */
class PluginTest extends WPTestCase {

	public function test_instance_from_function_in_hwp_previews() {
		$instance = hwp_previews_plugin_init();
		$this->assertTrue( $instance instanceof Plugin );
	}

	public function test_singleton_returns_same_instance() {
		$first  = Plugin::init();
		$second = Plugin::init();
		$this->assertSame( $first, $second, 'Plugin::instance() should always return the same instance' );
	}

	public function test_instance_creates_and_sets_up_plugin_when_not_set() {
		$reflection       = new ReflectionClass( Plugin::class );
		$instanceProperty = $reflection->getProperty( 'instance' );
		$instanceProperty->setAccessible( true );
		$instanceProperty->setValue( null );

		$this->assertNull( $instanceProperty->getValue() );
		$instance = Plugin::init();

		$this->assertInstanceOf( Plugin::class, $instanceProperty->getValue() );
		$this->assertSame( $instance, $instanceProperty->getValue(), 'Plugin::instance() should set the static instance property' );
	}


	public function test_clone_method_throws_error() {
		// Create a fresh instance instead of using singleton
		$reflection = new ReflectionClass( Plugin::class );
		$plugin     = $reflection->newInstanceWithoutConstructor();

		$this->setExpectedIncorrectUsage( 'HWP\Previews\Plugin::__clone' );
		$clone = clone $plugin;

		// Verify the clone exists to ensure the operation completed
		$this->assertInstanceOf( Plugin::class, $clone );
	}

	public function test_wakeup_method_throws_error() {
		$this->setExpectedIncorrectUsage( 'HWP\Previews\Plugin::__wakeup' );

		// Create a fresh instance
		$reflection = new ReflectionClass( Plugin::class );
		$plugin     = $reflection->newInstanceWithoutConstructor();

		$serialized   = serialize( $plugin );
		$unserialized = unserialize( $serialized );

		// Verify the unserialized object exists
		$this->assertInstanceOf( Plugin::class, $unserialized );
	}
}
