<?php

declare( strict_types=1 );

namespace WPGraphQL\Logging\Tests\Core;

use WPGraphQL\Logging\Plugin;
use lucatume\WPBrowser\TestCase\WPTestCase;
use ReflectionClass;
use WPGraphQL\Logging\Events\EventManager;
use WPGraphQL\Logging\Logger\Database\WordPressDatabaseEntity;
use WPGraphQL\Logging\Admin\Settings\Fields\Tab\BasicConfigurationTab;
use WPGraphQL\Logging\Admin\Settings\Fields\Tab\DataManagementTab;
use WPGraphQL\Logging\Events\Events;
use WPGraphQL\Logging\Admin\Settings\ConfigurationHelper;

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

	public function test_plugin_activate() {

		// Delet configuration option
		$configuration = ConfigurationHelper::get_instance();
		$option_key = $configuration->get_option_key();
		delete_option( $option_key );

		// Verify that the configuration option has been deleted
		$option_value = get_option( $option_key );
		$this->assertEmpty( $option_value );


		$plugin = Plugin::init();
		$plugin::activate();

		// Verify that the datatbase has been created
		global $wpdb;
		$table_name = WordPressDatabaseEntity::get_table_name();
		$table_exists = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) );
		$this->assertNotEmpty( $table_exists );
		$this->assertEquals( $table_exists, $table_name );

		// Verify that the default configuration has been set
		$option_value = $configuration->get_option_value( WPGRAPHQL_LOGGING_SETTINGS_KEY );
		$this->assertNotEmpty( $option_value );
		$default_configuration = [
			BasicConfigurationTab::get_name() => [
				BasicConfigurationTab::ENABLED             => true,
				BasicConfigurationTab::EXCLUDE_QUERY       => '__schema,GetSeedNode', // Exclude introspection and GetSeedNode queries.
				BasicConfigurationTab::DATA_SAMPLING       => '10',
				BasicConfigurationTab::EVENT_LOG_SELECTION => [
					Events::PRE_REQUEST,
					Events::BEFORE_GRAPHQL_EXECUTION,
					Events::BEFORE_RESPONSE_RETURNED,
					Events::REQUEST_DATA,
					Events::REQUEST_RESULTS,
					Events::RESPONSE_HEADERS_TO_SEND,
				],
				BasicConfigurationTab::LOG_RESPONSE        => false,
			],
			DataManagementTab::get_name()     => [
				DataManagementTab::DATA_DELETION_ENABLED => true,
				DataManagementTab::DATA_RETENTION_DAYS   => 7,
				DataManagementTab::DATA_SANITIZATION_ENABLED => true,
				DataManagementTab::DATA_SANITIZATION_METHOD => 'recommended',
			],
		];
		$this->assertEquals( $option_value, $default_configuration );
	}

	public function test_plugin_activate_when_configuration_already_exists() {
		$configuration = ConfigurationHelper::get_instance();
		$option_key = $configuration->get_option_key();
		$default_configuration = [
			BasicConfigurationTab::get_name() => [
				BasicConfigurationTab::ENABLED => true,
			],
		];
		update_option( $option_key, $default_configuration );


		$plugin = Plugin::init();
		$plugin::activate();

		// Verify that the default configuration has not been set
		$configuration = ConfigurationHelper::get_instance();
		$option_value = $configuration->get_option_value( WPGRAPHQL_LOGGING_SETTINGS_KEY );
		$this->assertEquals( $option_value, $default_configuration );
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
