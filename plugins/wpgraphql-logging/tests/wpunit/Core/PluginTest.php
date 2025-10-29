<?php

declare( strict_types=1 );

namespace WPGraphQL\Logging\Tests\Core;

use WPGraphQL\Logging\Plugin;
use lucatume\WPBrowser\TestCase\WPTestCase;
use ReflectionClass;
use WPGraphQL\Logging\Events\EventManager;


/**
 * Test for the Plugin
 *
 * @package WPGraphQL\Logging
 * @since 0.0.1
 */
class PluginTest extends WPTestCase {

	public function test_instance_from_function_in_wpgraphql_logging_plugin_init() {
		$instance = wpgraphql_logging_plugin_init();
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

		$this->setExpectedIncorrectUsage( 'WPGraphQL\Logging\Plugin::__clone' );
		$clone = clone $plugin;

		// Verify the clone exists to ensure the operation completed
		$this->assertInstanceOf( Plugin::class, $clone );
	}

	public function test_wakeup_method_throws_error() {
		$reflection = new ReflectionClass( Plugin::class );
		$plugin     = $reflection->newInstanceWithoutConstructor();

		$this->setExpectedIncorrectUsage( 'WPGraphQL\Logging\Plugin::__wakeup' );
		$plugin->__wakeup();

		$this->assertInstanceOf( Plugin::class, $plugin );
	}

	public function test_clone_is_forbidden_and_triggers_doing_it_wrong() {
		$reflection = new ReflectionClass( Plugin::class );
		$plugin     = $reflection->newInstanceWithoutConstructor();

		$this->setExpectedIncorrectUsage( 'WPGraphQL\Logging\Plugin::__clone' );
		$cloned = null;
		try {
			$cloned = clone $plugin;
		} catch ( \Exception $e ) {
			// Ignore, as __clone should not throw, just trigger doing_it_wrong.
		}
		$this->assertInstanceOf( Plugin::class, $plugin );
	}

	public function test_wakeup_is_forbidden_and_triggers_doing_it_wrong() {
		$reflection = new ReflectionClass( Plugin::class );
		$plugin     = $reflection->newInstanceWithoutConstructor();

		$this->setExpectedIncorrectUsage( 'WPGraphQL\Logging\Plugin::__wakeup' );
		$plugin->__wakeup();

		$this->assertInstanceOf( Plugin::class, $plugin );
	}


	public function test_can_subscribe_and_emit_custom_event() {
		$event_name = 'custom_event_test_' . uniqid();
		$received_payload = null;

		// Subscribe to the custom event.
		Plugin::on( $event_name, function( $payload ) use ( &$received_payload ) {
			$received_payload = $payload;
		} );

		$payload = [ 'foo' => 'bar', 'baz' => 123 ];

		// Emit the event.
		Plugin::emit( $event_name, $payload );

		// The listener should have received the payload.
		$this->assertSame( $payload, $received_payload );
	}

	public function test_transform_event_payload() {
		$event_name = 'transform_event_test_' . uniqid();
		$received_payload = null;

		// Register an event listener.
		Plugin::on( $event_name, function( $payload ) use ( &$received_payload ) {
			$received_payload = $payload;
		} );

		// Subscribe to transform the payload.
		Plugin::transform( $event_name, function( $payload ) {
			$payload['context']['error'] = true;
			return $payload;
		}, 5 );


		// Simulate emitting the event with initial payload.
		$level = 200;
		$context = [
			'query'          => 'query { test }',
			'variables'      => [ 'var1' => 'value' ],
			'operation_name' => 'TestOperation',
		];
		$payload = EventManager::transform( $event_name, [
			'context' => $context,
			'level'   => $level,
		] );

		// Publish the event.
		Plugin::emit( $event_name, $payload );


		// Check the listener received the transformed payload.
		$this->assertSame( $received_payload,
			array_merge( $payload, [ 'context' => array_merge( $context, [ 'error' => true ] ) ] ),
			'The listener should receive the transformed payload'
		);
	}
}
