<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Tests\Events;

use lucatume\WPBrowser\TestCase\WPTestCase;
use ReflectionClass;
use WPGraphQL\Logging\Events\EventManager;
use WPGraphQL\Logging\Events\Events;

/**
 * Class EventManagerTest
 *
 * Tests for the EventManager class.
 */
class EventManagerTest extends WPTestCase {

	/**
	 * Reset EventManager state after each test.
	 */
	public function tearDown(): void {
		parent::tearDown();
		$this->reset_event_manager();
	}

	/**
	 * Reset the EventManager static state.
	 */
	private function reset_event_manager(): void {
		$reflection = new ReflectionClass(EventManager::class);

		$events_prop = $reflection->getProperty('events');
		$events_prop->setAccessible(true);
		$events_prop->setValue(null, []);

		$transforms_prop = $reflection->getProperty('transforms');
		$transforms_prop->setAccessible(true);
		$transforms_prop->setValue(null, []);
	}

	/**
	 * Get the internal events array for testing.
	 */
	private function get_events_array(): array {
		$reflection = new ReflectionClass(EventManager::class);
		$events_prop = $reflection->getProperty('events');
		$events_prop->setAccessible(true);
		return $events_prop->getValue();
	}

	/**
	 * Get the internal transforms array for testing.
	 */
	private function get_transforms_array(): array {
		$reflection = new ReflectionClass(EventManager::class);
		$transforms_prop = $reflection->getProperty('transforms');
		$transforms_prop->setAccessible(true);
		return $transforms_prop->getValue();
	}

	public function test_subscribe_adds_listener_to_event(): void {
		$listener_called = false;
		$listener = function(array $payload) use (&$listener_called) {
			$listener_called = true;
		};

		EventManager::subscribe('test_event', $listener);

		$events = $this->get_events_array();
		$this->assertArrayHasKey('test_event', $events);
		$this->assertArrayHasKey(10, $events['test_event']); // Default priority
		$this->assertCount(1, $events['test_event'][10]);
		$this->assertSame($listener, $events['test_event'][10][0]);
	}

	public function test_subscribe_with_custom_priority(): void {
		$listener1 = function() {};
		$listener2 = function() {};

		EventManager::subscribe('priority_test', $listener1, 5);
		EventManager::subscribe('priority_test', $listener2, 15);

		$events = $this->get_events_array();
		$this->assertArrayHasKey(5, $events['priority_test']);
		$this->assertArrayHasKey(15, $events['priority_test']);
		$this->assertSame($listener1, $events['priority_test'][5][0]);
		$this->assertSame($listener2, $events['priority_test'][15][0]);
	}

	public function test_subscribe_multiple_listeners_same_priority(): void {
		$listener1 = function() {};
		$listener2 = function() {};

		EventManager::subscribe('multi_test', $listener1);
		EventManager::subscribe('multi_test', $listener2);

		$events = $this->get_events_array();
		$this->assertCount(2, $events['multi_test'][10]);
		$this->assertSame($listener1, $events['multi_test'][10][0]);
		$this->assertSame($listener2, $events['multi_test'][10][1]);
	}

	public function test_publish_calls_subscribed_listeners(): void {
		$payload_received = null;
		$listener_calls = 0;

		$listener = function(array $payload) use (&$payload_received, &$listener_calls) {
			$payload_received = $payload;
			$listener_calls++;
		};

		EventManager::subscribe('publish_test', $listener);

		$test_payload = ['test' => 'data'];
		EventManager::publish('publish_test', $test_payload);

		$this->assertEquals(1, $listener_calls);
		$this->assertEquals($test_payload, $payload_received);
	}

	public function test_publish_calls_listeners_in_priority_order(): void {
		$call_order = [];

		$high_priority = function() use (&$call_order) {
			$call_order[] = 'high';
		};

		$low_priority = function() use (&$call_order) {
			$call_order[] = 'low';
		};

		$medium_priority = function() use (&$call_order) {
			$call_order[] = 'medium';
		};

		EventManager::subscribe('order_test', $high_priority, 5);  // Lower number = higher priority
		EventManager::subscribe('order_test', $low_priority, 20);
		EventManager::subscribe('order_test', $medium_priority, 10);

		EventManager::publish('order_test');

		$this->assertEquals(['high', 'medium', 'low'], $call_order);
	}

	public function test_publish_with_no_listeners_triggers_wordpress_action(): void {
		$action_called = false;
		$received_payload = null;

		// Mock WordPress do_action by overriding the global function
		$this->mock_wordpress_action('wpgraphql_logging_event_no_listeners', function($payload) use (&$action_called, &$received_payload) {
			$action_called = true;
			$received_payload = $payload;
		});

		$test_payload = ['empty' => 'test'];
		EventManager::publish('no_listeners', $test_payload);

		$this->assertTrue($action_called);
		$this->assertEquals($test_payload, $received_payload);
	}

	public function test_publish_triggers_wordpress_action_after_listeners(): void {
		$execution_order = [];

		$listener = function() use (&$execution_order) {
			$execution_order[] = 'listener';
		};

		EventManager::subscribe('wp_action_test', $listener);

		$this->mock_wordpress_action('wpgraphql_logging_event_wp_action_test', function() use (&$execution_order) {
			$execution_order[] = 'wp_action';
		});

		EventManager::publish('wp_action_test');

		$this->assertEquals(['listener', 'wp_action'], $execution_order);
	}

	public function test_subscribe_to_transform_adds_transformer(): void {
		$transformer = function(array $payload): array {
			return $payload;
		};

		EventManager::subscribe_to_transform('transform_test', $transformer);

		$transforms = $this->get_transforms_array();
		$this->assertArrayHasKey('transform_test', $transforms);
		$this->assertArrayHasKey(10, $transforms['transform_test']);
		$this->assertSame($transformer, $transforms['transform_test'][10][0]);
	}

	public function test_transform_modifies_payload(): void {
		$transformer = function(array $payload): array {
			$payload['transformed'] = true;
			return $payload;
		};

		EventManager::subscribe_to_transform('modify_test', $transformer);

		$original = ['original' => 'data'];
		$result = EventManager::transform('modify_test', $original);

		$this->assertTrue($result['transformed']);
		$this->assertEquals('data', $result['original']);
	}

	public function test_transform_with_multiple_transformers_in_priority_order(): void {
		$first_transformer = function(array $payload): array {
			$payload['order'][] = 'first';
			return $payload;
		};

		$second_transformer = function(array $payload): array {
			$payload['order'][] = 'second';
			return $payload;
		};

		EventManager::subscribe_to_transform('multi_transform', $first_transformer, 5);
		EventManager::subscribe_to_transform('multi_transform', $second_transformer, 10);

		$result = EventManager::transform('multi_transform', ['order' => []]);

		$this->assertEquals(['first', 'second'], $result['order']);
	}

	public function test_transform_with_no_transformers_applies_wordpress_filter(): void {
		$filter_applied = false;
		$received_payload = null;

		$this->mock_wordpress_filter('wpgraphql_logging_filter_filter_test', function($payload) use (&$filter_applied, &$received_payload) {
			$filter_applied = true;
			$received_payload = $payload;
			$payload['filtered'] = true;
			return $payload;
		});

		$original = ['test' => 'data'];
		$result = EventManager::transform('filter_test', $original);

		$this->assertTrue($filter_applied);
		$this->assertEquals($original, $received_payload);
		$this->assertTrue($result['filtered']);
	}

	public function test_transform_applies_wordpress_filter_after_transformers(): void {
		$execution_order = [];

		$transformer = function(array $payload) use (&$execution_order): array {
			$execution_order[] = 'transformer';
			return $payload;
		};

		EventManager::subscribe_to_transform('filter_order_test', $transformer);

		$this->mock_wordpress_filter('wpgraphql_logging_filter_filter_order_test', function($payload) use (&$execution_order) {
			$execution_order[] = 'filter';
			return $payload;
		});

		EventManager::transform('filter_order_test', []);

		$this->assertEquals(['transformer', 'filter'], $execution_order);
	}

	public function test_listener_exceptions_are_caught_and_logged(): void {
		$good_listener_called = false;
		$exception_thrown = false;

		$bad_listener = function() use (&$exception_thrown) {
			$exception_thrown = true;
			throw new \Exception('Test exception');
		};

		$good_listener = function() use (&$good_listener_called) {
			$good_listener_called = true;
		};

		EventManager::subscribe('exception_test', $bad_listener, 5);
		EventManager::subscribe('exception_test', $good_listener, 10);

		// Publishing should not throw an exception even if a listener throws
		EventManager::publish('exception_test');

		$this->assertTrue($exception_thrown, 'Exception should have been thrown by bad listener');
		$this->assertTrue($good_listener_called, 'Good listener should still be called after exception');
	}

	public function test_transformer_exceptions_are_caught_and_logged(): void {
		$exception_thrown = false;

		$bad_transformer = function(array $payload) use (&$exception_thrown): array {
			$exception_thrown = true;
			throw new \Exception('Transform exception');
		};

		EventManager::subscribe_to_transform('transform_exception_test', $bad_transformer);

		$original = ['test' => 'data'];
		$result = EventManager::transform('transform_exception_test', $original);

		$this->assertTrue($exception_thrown, 'Exception should have been thrown by bad transformer');
		$this->assertEquals($original, $result, 'Original payload should be returned on exception');
	}

	public function test_transformer_returning_non_array_is_ignored(): void {
		$transformer_called = false;

		$bad_transformer = function(array $payload) use (&$transformer_called) {
			$transformer_called = true;
			return 'not an array';
		};

		EventManager::subscribe_to_transform('bad_return_test', $bad_transformer);

		$original = ['test' => 'data'];
		$result = EventManager::transform('bad_return_test', $original);

		$this->assertTrue($transformer_called, 'Transformer should have been called');
		$this->assertEquals($original, $result, 'Original payload should be returned when transformer returns non-array');
	}

	/**
	 * Data provider for event constants.
	 */
	public function eventConstantsProvider(): array {
		return [
			[Events::PRE_REQUEST, 'do_graphql_request'],
			[Events::BEFORE_GRAPHQL_EXECUTION, 'graphql_before_execute'],
			[Events::BEFORE_RESPONSE_RETURNED, 'graphql_return_response'],
		];
	}

	/**
	 * @dataProvider eventConstantsProvider
	 */
	public function test_works_with_event_constants(string $event_constant, string $expected_value): void {
		$this->assertEquals($expected_value, $event_constant);

		$listener_called = false;
		$listener = function() use (&$listener_called) {
			$listener_called = true;
		};

		EventManager::subscribe($event_constant, $listener);
		EventManager::publish($event_constant);

		$this->assertTrue($listener_called, "Listener should be called for event: {$event_constant}");
	}

	/**
	 * Helper method to mock WordPress do_action function.
	 */
	private function mock_wordpress_action(string $action_name, callable $callback): void {
		add_action($action_name, $callback);
	}

	/**
	 * Helper method to mock WordPress apply_filters function.
	 */
	private function mock_wordpress_filter(string $filter_name, callable $callback): void {
		add_filter($filter_name, $callback);
	}
}
