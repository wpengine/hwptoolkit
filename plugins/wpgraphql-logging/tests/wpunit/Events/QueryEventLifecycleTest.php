<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Tests\Events;

use GraphQL\Executor\ExecutionResult;
use lucatume\WPBrowser\TestCase\WPTestCase;
use Monolog\Handler\TestHandler;
use Monolog\Level;
use Monolog\LogRecord;
use ReflectionClass;
use WPGraphQL\Logging\Events\Event_Manager;
use WPGraphQL\Logging\Events\Events;
use WPGraphQL\Logging\Events\Query_Event_Lifecycle;
use WPGraphQL\Logging\Logger\Logger_Service;
use WPGraphQL\Request;
use WPGraphQL\WPSchema;
use WPGraphQL\Logging\Events\Request_Context_Service;

/**
 * Class Query_Event_LifecycleTest
 *
 * Tests for the Query_Event_Lifecycle class.
 */
class Query_Event_LifecycleTest extends WPTestCase {

	/**
	 * @var TestHandler
	 */
	private TestHandler $test_handler;

	/**
	 * @var Logger_Service
	 */
	private Logger_Service $mock_logger;

	/**
	 * Set up test fixtures.
	 */
	public function setUp(): void {
		parent::setUp();
		$this->test_handler = new TestHandler();
		$this->mock_logger = Logger_Service::get_instance('test_lifecycle', [$this->test_handler], [], []);
		$this->reset_lifecycle_instance();
		$this->reset_event_manager();
	}

	/**
	 * Clean up after each test.
	 */
	public function tearDown(): void {
		parent::tearDown();
		$this->reset_lifecycle_instance();
		$this->reset_event_manager();
		$this->reset_logger_instances();
	}

	/**
	 * Reset Query_Event_Lifecycle singleton state.
	 */
	private function reset_lifecycle_instance(): void {
		$reflection = new ReflectionClass(Query_Event_Lifecycle::class);
		$instance_prop = $reflection->getProperty('instance');
		$instance_prop->setAccessible(true);
		$instance_prop->setValue(null, null);
	}

	/**
	 * Reset Event_Manager static state.
	 */
	private function reset_event_manager(): void {
		$reflection = new ReflectionClass(Event_Manager::class);

		$events_prop = $reflection->getProperty('events');
		$events_prop->setAccessible(true);
		$events_prop->setValue(null, []);

		$transforms_prop = $reflection->getProperty('transforms');
		$transforms_prop->setAccessible(true);
		$transforms_prop->setValue(null, []);
	}

	/**
	 * Reset Logger_Service instances.
	 */
	private function reset_logger_instances(): void {
		$reflection = new ReflectionClass(Logger_Service::class);
		$instances_prop = $reflection->getProperty('instances');
		$instances_prop->setAccessible(true);
		$instances_prop->setValue(null, []);
	}

	/**
	 * Create a mock Logger_Service instance with injected test handler.
	 */
	private function create_lifecycle_with_mock_logger(): Query_Event_Lifecycle {
		$reflection = new ReflectionClass(Query_Event_Lifecycle::class);
		$lifecycle = $reflection->newInstanceWithoutConstructor();

		$logger_prop = $reflection->getProperty('logger');
		$logger_prop->setAccessible(true);
		$logger_prop->setValue($lifecycle, $this->mock_logger);

		return $lifecycle;
	}

	/**
	 * Create a mock WPGraphQL Request object.
	 */
	private function create_mock_request(string $query = '{ posts { title } }', ?string $operation_name = null, ?array $variables = null): Request {
		$mock_request = $this->createMock(Request::class);

		$mock_params = new \stdClass();
		$mock_params->query = $query;
		$mock_params->operation = $operation_name;
		$mock_params->variables = $variables;

		$mock_request->params = $mock_params;

		return $mock_request;
	}

	/**
	 * Create a mock WPGraphQL Schema object.
	 */
	private function create_mock_schema(): WPSchema {
		return $this->createMock(WPSchema::class);
	}

	/**
	 * Create a mock ExecutionResult object.
	 */
	private function create_mock_execution_result(array $data = [], array $errors = []): ExecutionResult {
		$mock_result = $this->createMock(ExecutionResult::class);
		$mock_result->data = $data;
		$mock_result->errors = $errors;
		return $mock_result;
	}

	public function test_init_returns_singleton_instance(): void {
		$instance1 = Query_Event_Lifecycle::init();
		$instance2 = Query_Event_Lifecycle::init();

		$this->assertInstanceOf(Query_Event_Lifecycle::class, $instance1);
		$this->assertSame($instance1, $instance2, 'init() should return the same singleton instance');
	}

	public function test_init_creates_new_instance_when_none_exists(): void {
		$reflection = new ReflectionClass(Query_Event_Lifecycle::class);
		$instance_prop = $reflection->getProperty('instance');
		$instance_prop->setAccessible(true);

		$this->assertNull($instance_prop->getValue());

		$instance = Query_Event_Lifecycle::init();

		$this->assertInstanceOf(Query_Event_Lifecycle::class, $instance);
		$this->assertSame($instance, $instance_prop->getValue());
	}

	public function test_log_pre_request_logs_correctly(): void {
		$lifecycle = $this->create_lifecycle_with_mock_logger();

		$query = '{ posts { title } }';
		$operation_name = 'GetPosts';
		$variables = ['limit' => 10];

		$lifecycle->log_pre_request($query, $operation_name, $variables);

		$this->assertTrue($this->test_handler->hasInfoRecords());
		$records = $this->test_handler->getRecords();
		$this->assertCount(1, $records);

		$record = $records[0];
		$this->assertEquals('WPGraphQL Pre Request', $record['message']);
		$this->assertEquals($query, $record['context']['query']);
		$this->assertEquals($operation_name, $record['context']['operation_name']);
		$this->assertEquals($variables, $record['context']['variables']);
	}

	public function test_log_pre_request_handles_null_values(): void {
		$lifecycle = $this->create_lifecycle_with_mock_logger();

		$query = '{ posts { title } }';

		$lifecycle->log_pre_request($query, null, null);

		$this->assertTrue($this->test_handler->hasInfoRecords());
		$records = $this->test_handler->getRecords();
		$record = $records[0];

		$this->assertEquals($query, $record['context']['query']);
		$this->assertNull($record['context']['operation_name']);
		$this->assertNull($record['context']['variables']);
	}

	public function test_log_graphql_before_execute_logs_correctly(): void {
		$lifecycle = $this->create_lifecycle_with_mock_logger();

		$query = '{ posts { title } }';
		$operation_name = 'GetPosts';
		$variables = ['limit' => 10];
		$request = $this->create_mock_request($query, $operation_name, $variables);

		$lifecycle->log_graphql_before_execute($request);

		$this->assertTrue($this->test_handler->hasInfoRecords());
		$records = $this->test_handler->getRecords();
		$record = $records[0];

		$this->assertEquals('WPGraphQL Before Query Execution', $record['message']);
		$this->assertEquals($query, $record['context']['query']);
		$this->assertEquals($operation_name, $record['context']['operation_name']);
		$this->assertEquals($variables, $record['context']['variables']);
		$this->assertEquals($request->params, $record['context']['params']);
	}





	public function test_log_before_response_returned_logs_info_for_success(): void {
		$lifecycle = $this->create_lifecycle_with_mock_logger();

		$filtered_response = $this->create_mock_execution_result(['posts' => []]);
		$response = $this->create_mock_execution_result(['posts' => []]);
		$schema = $this->create_mock_schema();
		$operation = 'GetPosts';
		$query = '{ posts { title } }';
		$variables = ['limit' => 10];
		$request = $this->create_mock_request();
		$query_id = 'query_123';

		$lifecycle->log_before_response_returned($filtered_response, $response, $schema, $operation, $query, $variables, $request, $query_id);

		$this->assertTrue($this->test_handler->hasInfoRecords());
		$records = $this->test_handler->getRecords();
		$record = $records[0];

		$this->assertEquals(200, $record['level']);
		$this->assertEquals('WPGraphQL Response', $record['message']);
	}

	public function test_log_before_response_returned_logs_error_for_errors(): void {
		$lifecycle = $this->create_lifecycle_with_mock_logger();

		$errors = [['message' => 'Field not found']];
		$response = $this->create_mock_execution_result([], $errors);
		$schema = $this->create_mock_schema();
		$query = '{ invalidField }';
		$request = $this->create_mock_request();

		$lifecycle->log_before_response_returned($response, $response, $schema, null, $query, null, $request, null);

		$this->assertTrue($this->test_handler->hasErrorRecords());
		$records = $this->test_handler->getRecords();
		$record = $records[0];

		$this->assertEquals('WPGraphQL Response with Errors', $record['message']);
		$this->assertEquals(400, $record['level']);
		$this->assertEquals($errors, $record['context']['errors']);
	}

	public function test_get_response_errors_extracts_from_execution_result(): void {
		$lifecycle = $this->create_lifecycle_with_mock_logger();
		$reflection = new ReflectionClass($lifecycle);
		$method = $reflection->getMethod('get_response_errors');
		$method->setAccessible(true);

		$errors = [['message' => 'Test error']];
		$response = $this->create_mock_execution_result([], $errors);

		$result = $method->invoke($lifecycle, $response);

		$this->assertEquals($errors, $result);
	}

	public function test_get_response_errors_extracts_from_array(): void {
		$lifecycle = $this->create_lifecycle_with_mock_logger();
		$reflection = new ReflectionClass($lifecycle);
		$method = $reflection->getMethod('get_response_errors');
		$method->setAccessible(true);

		$errors = [['message' => 'Test error']];
		$response = ['data' => [], 'errors' => $errors];

		$result = $method->invoke($lifecycle, $response);

		$this->assertEquals($errors, $result);
	}

	public function test_get_response_errors_returns_null_for_no_errors(): void {
		$lifecycle = $this->create_lifecycle_with_mock_logger();
		$reflection = new ReflectionClass($lifecycle);
		$method = $reflection->getMethod('get_response_errors');
		$method->setAccessible(true);

		$response = $this->create_mock_execution_result(['data' => []], []);

		$result = $method->invoke($lifecycle, $response);

		$this->assertNull($result);
	}

	public function test_get_response_errors_returns_null_for_empty_errors_array(): void {
		$lifecycle = $this->create_lifecycle_with_mock_logger();
		$reflection = new ReflectionClass($lifecycle);
		$method = $reflection->getMethod('get_response_errors');
		$method->setAccessible(true);

		$response = ['data' => [], 'errors' => []];

		$result = $method->invoke($lifecycle, $response);

		$this->assertNull($result);
	}

	public function test_integration_with_event_manager_transform(): void {
		$lifecycle = $this->create_lifecycle_with_mock_logger();

		$transform_called = false;
		$received_payload = null;

		Event_Manager::subscribe_to_transform(Events::PRE_REQUEST, function($payload) use (&$transform_called, &$received_payload) {
			$transform_called = true;
			$received_payload = $payload;
			/** @var Request_Context_Service $context */
			$context = $received_payload['context'];
			$context->set_data('transformed', true);
			return $payload;
		});

		$lifecycle->log_pre_request('{ test }', null, null);

		/** @var Request_Context_Service $context */
		$context = $received_payload['context'];

		$this->assertTrue($transform_called);
		$this->assertArrayHasKey('context', $received_payload);
		$this->assertInstanceOf(Request_Context_Service::class, $context);
		$this->assertEquals('{ test }', $context->query);

		$records = $this->test_handler->getRecords();
		$this->assertTrue($records[0]['context']['transformed']);
	}

	public function test_integration_with_event_manager_publish(): void {
		$lifecycle = $this->create_lifecycle_with_mock_logger();

		$publish_called = false;
		$received_payload = null;

		Event_Manager::subscribe(Events::PRE_REQUEST, function($payload) use (&$publish_called, &$received_payload) {
			$publish_called = true;
			$received_payload = $payload;
		});

		$lifecycle->log_pre_request('{ test }', null, null);

		$this->assertTrue($publish_called);
		$this->assertArrayHasKey('context', $received_payload);
		$this->assertArrayHasKey('level', $received_payload);
		$this->assertEquals('INFO', $received_payload['level']);
	}

	public function test_exception_handling_in_log_pre_request(): void {
		// Create a lifecycle with a mock logger that throws an exception
		$mock_logger = $this->createMock(Logger_Service::class);
		$mock_logger->method('log')->willThrowException(new \Exception('Logger error'));

		$reflection = new ReflectionClass(Query_Event_Lifecycle::class);
		$lifecycle = $reflection->newInstanceWithoutConstructor();

		$logger_prop = $reflection->getProperty('logger');
		$logger_prop->setAccessible(true);
		$logger_prop->setValue($lifecycle, $mock_logger);

		// Should not throw an exception
		$lifecycle->log_pre_request('{ test }', null, null);

		// The test passes if no exception is thrown
		$this->assertTrue(true);
	}


	public function test_setup_registers_correct_priorities(): void {
		$lifecycle = Query_Event_Lifecycle::init();

		$this->assertEquals(10, has_action('do_graphql_request', [$lifecycle, 'log_pre_request']));
		$this->assertEquals(10, has_action('graphql_before_execute', [$lifecycle, 'log_graphql_before_execute']));
		$this->assertEquals(10, has_action('graphql_return_response', [$lifecycle, 'log_before_response_returned']));
	}

	/**
	 * Data provider for lifecycle methods.
	 */
	public function lifecycleMethodsProvider(): array {
		return [
			[
				'method' => 'log_pre_request',
				'event' => Events::PRE_REQUEST,
				'args' => ['{ test }', null, null],
				'expected_message' => 'WPGraphQL Pre Request',
			],
			[
				'method' => 'log_graphql_before_execute',
				'event' => Events::BEFORE_GRAPHQL_EXECUTION,
				'args' => [null], // Will be replaced with mock request
				'expected_message' => 'WPGraphQL Before Query Execution',
			],
		];
	}

	/**
	 * @dataProvider lifecycleMethodsProvider
	 */
	public function test_lifecycle_methods_use_correct_events(string $method, string $event, array $args, string $expected_message): void {
		$lifecycle = $this->create_lifecycle_with_mock_logger();

		$event_published = false;
		Event_Manager::subscribe($event, function() use (&$event_published) {
			$event_published = true;
		});

		if ($method === 'log_graphql_before_execute') {
			$args[0] = $this->create_mock_request();
		}

		$lifecycle->$method(...$args);

		$this->assertTrue($event_published, "Event {$event} should be published by {$method}");

		$records = $this->test_handler->getRecords();
		$this->assertEquals($expected_message, $records[0]['message']);
	}

	public function test_process_application_error_method_exists(): void {
		$lifecycle = $this->create_lifecycle_with_mock_logger();
		$reflection = new ReflectionClass($lifecycle);

		$this->assertTrue($reflection->hasMethod('process_application_error'));

		$method = $reflection->getMethod('process_application_error');
		$method->setAccessible(true);

		// Should not throw an exception when called
		$exception = new \Exception('Test exception');
		$method->invoke($lifecycle, Events::PRE_REQUEST, $exception);

		$this->assertTrue(true);
	}

	public function test_all_lifecycle_methods_handle_exceptions_gracefully(): void {
		// Create a mock logger that always throws exceptions
		$mock_logger = $this->createMock(Logger_Service::class);
		$mock_logger->method('log')->willThrowException(new \Exception('Logger error'));

		$reflection = new ReflectionClass(Query_Event_Lifecycle::class);
		$lifecycle = $reflection->newInstanceWithoutConstructor();

		$logger_prop = $reflection->getProperty('logger');
		$logger_prop->setAccessible(true);
		$logger_prop->setValue($lifecycle, $mock_logger);

		// Test all lifecycle methods - none should throw exceptions
		$lifecycle->log_pre_request('{ test }', null, null);

		$request = $this->create_mock_request();
		$lifecycle->log_graphql_before_execute($request);

		$response = $this->create_mock_execution_result();
		$schema = $this->create_mock_schema();

		$lifecycle->log_before_response_returned($response, $response, $schema, null, '{ test }', null, $request, null);

		$this->assertTrue(true, 'All lifecycle methods should handle exceptions gracefully');
	}
}
