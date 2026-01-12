<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Tests\Events;

use WPGraphQL\Logging\Plugin;
use Monolog\Handler\BufferHandler;
use lucatume\WPBrowser\TestCase\WPTestCase;
use WPGraphQL\Logging\Events\QueryActionLogger;
use WPGraphQL\Logging\Events\Events;
use GraphQL\Executor\ExecutionResult;
use WPGraphQL\Logging\Logger\LoggerService;
use WPGraphQL\Logging\Admin\Settings\Fields\Tab\BasicConfigurationTab;
use Monolog\Level;
use WPGraphQL;
use GraphQL\Error\Error;
use GraphQL\Type\SchemaConfig;
use WPGraphQL\WPSchema;
use WPGraphQL\Request;
use WPGraphQL\Logging\Logger\Store\LogStoreService;
use WPGraphQL\Logging\Logger\Api\LogServiceInterface;
use WPGraphQL\Logging\Logger\Database\WordPressDatabaseEntity;

/**
 * Tests for the QueryActionLogger class.
 *
 * @package WPGraphQL\Logging\Tests\Events
 *
 * @since 0.0.1
 */
class QueryActionLoggerTest extends WPTestCase {

	protected LoggerService $logger;

	protected LogServiceInterface $log_service;

	public function setUp(): void {
		parent::setUp();
		$this->log_service = LogStoreService::get_log_service();
		$this->logger = LoggerService::get_instance();
	}

	public function tearDown(): void {
		parent::tearDown();
		$this->log_service->delete_all_entities();
	}

	public function create_instance(array $config) : QueryActionLogger {
		return new QueryActionLogger($this->logger, $config);
	}

	public function get_log_count(): int {
		return $this->log_service->count_entities_by_where([]);
	}

	public function assert_log_count(int $expected_count): void {

		// Flush the buffer handler to ensure the log count is accurate.
		$handlers = $this->logger->get_monolog()->getHandlers();
		foreach ($handlers as $handler) {
			if ($handler instanceof BufferHandler) {
				$handler->flush();
			}
		}

		$actual_count = $this->get_log_count();
		$this->assertEquals($expected_count, $actual_count, "Expected log count to be {$expected_count}, but got {$actual_count}.");
	}


	/**************************************************************
	 * Pre request tests
	 **************************************************************/

	public function test_pre_request_no_logging_as_config_disabled(): void {
		$instance = $this->create_instance(
			[
				BasicConfigurationTab::ENABLED => false,
			]
		);
		$instance->log_pre_request('{ test_query }', null, null);
		$this->assert_log_count(0);
	}

	public function test_pre_request_no_logging_as_event_none_selected(): void {
		$instance = $this->create_instance(
			[
				BasicConfigurationTab::ENABLED => true,
				BasicConfigurationTab::EVENT_LOG_SELECTION => '',
			]
		);
		$instance->log_pre_request('{ test_query }', null, null);
		$this->assert_log_count(0);
	}

	public function test_pre_request_no_logging_as_event_not_selected(): void {
		$instance = $this->create_instance(
			[
				BasicConfigurationTab::ENABLED => true,
				BasicConfigurationTab::EVENT_LOG_SELECTION => [Events::BEFORE_GRAPHQL_EXECUTION]
			]
		);
		$instance->log_pre_request('{ test_query }', null, null);
		$this->assert_log_count(0);
	}

	public function test_pre_request_no_logging_as_event_as_excluded(): void {
		$instance = $this->create_instance(
			[
				BasicConfigurationTab::ENABLED => true,
				BasicConfigurationTab::EVENT_LOG_SELECTION => [Events::PRE_REQUEST],
				BasicConfigurationTab::EXCLUDE_QUERY => 'test_query',
			]
		);
		$instance->log_pre_request('{ test_query }', null, null);
		$this->assert_log_count(0);
	}

	public function test_pre_request_log_event(): void {
		$instance = $this->create_instance(
			[
				BasicConfigurationTab::ENABLED => true,
				BasicConfigurationTab::EVENT_LOG_SELECTION => [Events::PRE_REQUEST],
			]
		);
		$instance->log_pre_request('{ test_query }', null, null);
		$this->assert_log_count(1);
	}

	public function test_pre_request_add_context(): void {

		// Test subscribing a transform to add context
		Plugin::transform(Events::PRE_REQUEST, function(array $payload): array {
				$payload['context']['custom_key'] = 'custom_value';
				$payload['level'] = Level::Debug;

				return $payload;
			}
		);

		$instance = $this->create_instance(
			[
				BasicConfigurationTab::ENABLED => true,
				BasicConfigurationTab::EVENT_LOG_SELECTION => [Events::PRE_REQUEST],
			]
		);
		$instance->log_pre_request('{ test_query }', null, null);
		$this->assert_log_count(1);

		// Check that the additional context is present in the log
		$logs = $this->log_service->find_entities_by_where([]);
		$this->assertCount(1, $logs);
		$log = $logs[0];
		$this->assertEquals(Level::Debug->value, $log->get_level());
		$this->assertArrayHasKey('custom_key', $log->get_context());
		$this->assertEquals('custom_value', $log->get_context()['custom_key']);
	}


	/**************************************************************
	 * Before response returned tests
	 **************************************************************/


	public function make_query_graphql_before_execute(QueryActionLogger $instance, ?string $query = null, ?string $operation = null, ?array $variables = null): void {
		$query = $query ?? '{ test_query }';
		$request = new Request();
		$reflection = new \ReflectionClass($request);
		$property = $reflection->getProperty('params');
		$property->setAccessible(true);

		// Set up params object
		$params = new \stdClass();
		$params->query = $query;
		$params->operation = $operation;
		$params->variables = $variables;
		$property->setValue($request, $params);
		$instance->log_graphql_before_execute($request);
	}


	public function test_graphql_before_execute_no_logging_as_config_not_enabled(): void {
		$instance = $this->create_instance(
			[
				BasicConfigurationTab::ENABLED => false,
			]
		);
		$this->make_query_graphql_before_execute($instance);
		$this->assert_log_count(0);
	}

	public function test_graphql_before_execute_no_logging_as_event_none_selected(): void {
		$instance = $this->create_instance(
			[
				BasicConfigurationTab::ENABLED => true,
				BasicConfigurationTab::EVENT_LOG_SELECTION => [],
			]
		);
		$this->make_query_graphql_before_execute($instance);
		$this->assert_log_count(0);
	}


	public function test_graphql_before_execute_no_logging_as_event_not_selected(): void {
		$instance = $this->create_instance(
			[
				BasicConfigurationTab::ENABLED => true,
				BasicConfigurationTab::EVENT_LOG_SELECTION => [Events::PRE_REQUEST],
			]
		);

		$this->make_query_graphql_before_execute($instance);
		$this->assert_log_count(0);
	}


	public function test_graphql_before_execute_no_logging_as_event_as_no_query(): void {
		$instance = $this->create_instance(
			[
				BasicConfigurationTab::ENABLED => true,
				BasicConfigurationTab::EVENT_LOG_SELECTION => [Events::PRE_REQUEST, Events::BEFORE_GRAPHQL_EXECUTION],
			]
		);
		$request = new Request();
		$instance->log_graphql_before_execute($request);
		$this->assert_log_count(0);
	}

	public function test_graphql_before_execute_log_event_add(): void {
		$instance = $this->create_instance(
			[
				BasicConfigurationTab::ENABLED => true,
				BasicConfigurationTab::EVENT_LOG_SELECTION => [Events::PRE_REQUEST, Events::BEFORE_GRAPHQL_EXECUTION],
			]
		);
		$params = [
			'query' =>  '{ query GetPost($uri: ID!) {
				post(id: $uri, idType: URI) {
					title
					content
				}
			} }',
			'variables' => ['var1' => 'value1'],
			'operation' => 'TestOperation',
		];
		$this->make_query_graphql_before_execute(
			$instance,
			$params['query'],
			$params['operation'],
			$params['variables']
		);
		$this->assert_log_count(1);
	}

	public function test_graphql_before_execute_add_context(): void {


		// Test subscribing a transform to add context
		Plugin::transform(Events::BEFORE_GRAPHQL_EXECUTION, function(array $payload): array {
				$payload['context']['custom_key'] = 'custom_value';
				$payload['level'] = Level::Error;

				return $payload;
			}
		);


		$instance = $this->create_instance(
			[
				BasicConfigurationTab::ENABLED => true,
				BasicConfigurationTab::EVENT_LOG_SELECTION => [Events::BEFORE_GRAPHQL_EXECUTION],
			]
		);
		$params = [
			'query' =>  '{ query GetPost($uri: ID!) {
				post(id: $uri, idType: URI) {
					title
					content
				}
			} }',
			'variables' => ['var1' => 'value1'],
			'operation' => 'TestOperation',
		];
		$this->make_query_graphql_before_execute(
			$instance,
			$params['query'],
			$params['operation'],
			$params['variables']
		);
		$this->assert_log_count(1);

		// Check that the additional context is present in the log
		$logs = $this->log_service->find_entities_by_where([]);
		$this->assertCount(1, $logs);
		$log = $logs[0];
		$this->assertInstanceOf(WordPressDatabaseEntity::class, $log);


		$this->assertEquals(Level::Error->value, $log->get_level());
		$this->assertArrayHasKey('custom_key', $log->get_context());
		$this->assertEquals('custom_value', $log->get_context()['custom_key']);
	}

	/**************************************************************
	 * Log before response returned tests
	 **************************************************************/

	public function test_log_before_response_returned_no_logging_as_config_not_enabled(): void {
		$instance = $this->create_instance(
			[
				BasicConfigurationTab::ENABLED => false,
				BasicConfigurationTab::EVENT_LOG_SELECTION => [Events::BEFORE_RESPONSE_RETURNED],
			]
		);


		$schema_config = SchemaConfig::create();
		$schema = new WPSchema($schema_config, WPGraphQL::get_type_registry());
		$query = '{ query GetPost($uri: ID!) {
				post(id: $uri, idType: URI) {
					title
					content
				}
			} }';
		$variables = ['uri' => '/sample-post/'];

		$response = new ExecutionResult(['query' => '{ test_query }']);
		$instance->log_before_response_returned($response, $response, $schema, null, $query, $variables, new Request(), null);
		$this->assert_log_count(0);
	}


	public function test_log_before_response_returned_log_data(): void {
		$instance = $this->create_instance(
			[
				BasicConfigurationTab::ENABLED => true,
				BasicConfigurationTab::EVENT_LOG_SELECTION => [Events::BEFORE_RESPONSE_RETURNED],
			]
		);

		$schema_config = SchemaConfig::create();
		$schema = new WPSchema($schema_config, WPGraphQL::get_type_registry());

		$query = '{ query GetPost($uri: ID!) {
				post(id: $uri, idType: URI) {
					title
					content
				}
			} }';
		$variables = ['uri' => '/sample-post/'];

		$response = new ExecutionResult(['query' => '{ test_query }']);
		$instance->log_before_response_returned($response, $response, $schema, null, $query, $variables, new Request(), null);
		$this->assert_log_count(1);
	}

	public function test_log_before_response_returned_log_data_with_errors(): void {
		$instance = $this->create_instance(
			[
				BasicConfigurationTab::ENABLED => true,
				BasicConfigurationTab::EVENT_LOG_SELECTION => [Events::BEFORE_RESPONSE_RETURNED],
			]
		);

		$schema_config = SchemaConfig::create();
		$schema = new WPSchema($schema_config, WPGraphQL::get_type_registry());

		$query = '{ query GetPost($uri: ID!) {
				post(id: $uri, idType: URI) {
					title
					content
				}
			} }';
		$variables = ['uri' => '/sample-post/'];

		$response = new ExecutionResult(['query' => '{ test_query }'], [new Error('Test error')]);
		$instance->log_before_response_returned($response, $response, $schema, null, $query, $variables, new Request(), null);
		$this->assert_log_count(1);


		// Check for error level and context
		$logs = $this->log_service->find_entities_by_where([]);
		$this->assertCount(1, $logs);
		$log = $logs[0];
		$this->assertInstanceOf(WordPressDatabaseEntity::class, $log);


		$this->assertEquals(Level::Error->value, $log->get_level());
		$this->assertArrayHasKey('errors', $log->get_context());
	}


	public function test_log_before_response_returned_log_data_with_errors_array(): void {
		$instance = $this->create_instance(
			[
				BasicConfigurationTab::ENABLED => true,
				BasicConfigurationTab::EVENT_LOG_SELECTION => [Events::BEFORE_RESPONSE_RETURNED],
			]
		);

		$schema_config = SchemaConfig::create();
		$schema = new WPSchema($schema_config, WPGraphQL::get_type_registry());

		$query = '{ query GetPost($uri: ID!) {
				post(id: $uri, idType: URI) {
					title
					content
				}
			} }';
		$variables = ['uri' => '/sample-post/'];

		$response = [
			'query' => '{ test_query }',
			'errors' => [new Error('Test error')]
		];
		$instance->log_before_response_returned($response, $response, $schema, null, $query, $variables, new Request(), null);
		$this->assert_log_count(1);


		// Check for error level and context
		$logs = $this->log_service->find_entities_by_where([]);
		$this->assertCount(1, $logs);
		$log = $logs[0];
		$this->assertInstanceOf(WordPressDatabaseEntity::class, $log);


		$this->assertEquals(Level::Error->value, $log->get_level());
		$this->assertArrayHasKey('errors', $log->get_context());
		$this->assertEquals('WPGraphQL Response with Errors', $log->get_message());
	}


	public function test_log_before_response_returned_log_data_with_empty_errors_array(): void {
		$instance = $this->create_instance(
			[
				BasicConfigurationTab::ENABLED => true,
				BasicConfigurationTab::EVENT_LOG_SELECTION => [Events::BEFORE_RESPONSE_RETURNED],
			]
		);

		$schema_config = SchemaConfig::create();
		$schema = new WPSchema($schema_config, WPGraphQL::get_type_registry());

		$query = '{ query GetPost($uri: ID!) {
				post(id: $uri, idType: URI) {
					title
					content
				}
			} }';
		$variables = ['uri' => '/sample-post/'];

		$response = [
			'query' => '{ test_query }',
			'errors' => []
		];
		$instance->log_before_response_returned($response, $response, $schema, null, $query, $variables, new Request(), null);
		$this->assert_log_count(1);


		// Check for error level and context
		$logs = $this->log_service->find_entities_by_where([]);
		$this->assertCount(1, $logs);
		$log = $logs[0];
		$this->assertInstanceOf(WordPressDatabaseEntity::class, $log);


		$this->assertNotEquals(Level::Error->value, $log->get_level());
		$this->assertArrayNotHasKey('errors', $log->get_context());
	}

}
