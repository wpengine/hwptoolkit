<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Tests\wpunit\Events;


use WPGraphQL\Logging\Plugin;
use lucatume\WPBrowser\TestCase\WPTestCase;
use WPGraphQL\Logging\Events\QueryFilterLogger;
use WPGraphQL\Logging\Events\Events;
use GraphQL\Executor\ExecutionResult;
use WPGraphQL\Logging\Logger\Database\LogsRepository;
use WPGraphQL\Logging\Logger\LoggerService;
use WPGraphQL\Logging\Logger\Database\DatabaseEntity;
use WPGraphQL\Logging\Admin\Settings\Fields\Tab\BasicConfigurationTab;
use Monolog\Level;
use WPGraphQL;
use GraphQL\Error\Error;
use GraphQL\Type\SchemaConfig;
use WPGraphQL\WPSchema;
use WPGraphQL\Request;

/**
 * Tests for the QueryFilterLogger class.
 *
 * @package WPGraphQL\Logging\Tests\wpunit\Events
 *
 * @since 0.0.1
 */
class QueryFilterLoggerTest extends WPTestCase {


	protected LogsRepository $repository;

	protected LoggerService $logger;

	public function setUp(): void {
		parent::setUp();
		$this->repository = new LogsRepository();
		$this->logger = LoggerService::get_instance();
	}

	public function tearDown(): void {
		parent::tearDown();
		$this->repository->delete_all();
	}

	public function create_instance(array $config) : QueryFilterLogger {
		return new QueryFilterLogger($this->logger, $config);
	}

	public function get_log_count(): int {
		return $this->repository->get_log_count([]);
	}

	public function assert_log_count(int $expected_count): void {
		$actual_count = $this->get_log_count();
		$this->assertEquals($expected_count, $actual_count, "Expected log count to be {$expected_count}, but got {$actual_count}.");
	}

	/**************************************************************
	 * graphql_request_data
	 **************************************************************/

	public function test_graphql_request_data_no_logging_as_config_disabled(): void {
		$instance = $this->create_instance(
			[
				BasicConfigurationTab::ENABLED => false,
			]
		);
		$instance->log_graphql_request_data(['query' => '{ testQuery }']);
		$this->assert_log_count(0);
	}

	public function test_graphql_request_data_no_logging_as_not_selected(): void {
		$instance = $this->create_instance(
			[
				BasicConfigurationTab::ENABLED => true,
				BasicConfigurationTab::EVENT_LOG_SELECTION => [Events::PRE_REQUEST],
			]
		);
		$instance->log_graphql_request_data(['query' => '{ testQuery }']);
		$this->assert_log_count(0);
	}

	public function test_graphql_request_data_log_event(): void {
		$instance = $this->create_instance(
			[
				BasicConfigurationTab::ENABLED => true,
				BasicConfigurationTab::EVENT_LOG_SELECTION => [Events::REQUEST_DATA],
			]
		);
		$instance->log_graphql_request_data(['query' => '{ testQuery }']);
		$this->assert_log_count(1);
	}


	public function test_graphql_request_data_log_event_with_context_data_from_subscriber(): void {

		// Add a subscriber to modify the context data and log level.
		Plugin::transform(Events::REQUEST_DATA, function(array $payload): array {
				$payload['context']['meta_data'] = 'This is meta value.';
				$payload['level'] = Level::Critical;

				return $payload;
			}
		);


		$instance = $this->create_instance(
			[
				BasicConfigurationTab::ENABLED => true,
				BasicConfigurationTab::EVENT_LOG_SELECTION => [Events::REQUEST_DATA],
			]
		);
		$instance->log_graphql_request_data(['query' => '{ testQuery }']);
		$this->assert_log_count(1);

		// Check for the new meta_data field in the log entry.
		$logs = $this->repository->get_logs([], 10, 0);
		$this->assertNotEmpty($logs);
		$log_entry = $logs[0];
		$this->assertArrayHasKey('meta_data', $log_entry->get_context());
		$this->assertEquals('This is meta value.', $log_entry->get_context()['meta_data']);
		$this->assertEquals(Level::Critical->value, $log_entry->get_level());
	}


	/**************************************************************
	 * graphql_request_results
	 **************************************************************/


	public function test_graphql_request_results_no_logging_as_not_enabled(): void {
		$instance = $this->create_instance(
			[
				BasicConfigurationTab::ENABLED => false,
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
		$instance->log_graphql_request_results($response, $schema, null, $query, $variables, new Request(), null);
		$this->assert_log_count(0);
	}


	public function test_graphql_request_results_log_event(): void {
		$instance = $this->create_instance(
			[
				BasicConfigurationTab::ENABLED => true,
				BasicConfigurationTab::EVENT_LOG_SELECTION => [Events::REQUEST_RESULTS],
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
		$instance->log_graphql_request_results($response, $schema, 'test_operation', $query, $variables, new Request(), null);
		$this->assert_log_count(1);
	}

	public function test_graphql_request_results_log_data_with_errors_array(): void {
		$instance = $this->create_instance(
			[
				BasicConfigurationTab::ENABLED => true,
				BasicConfigurationTab::EVENT_LOG_SELECTION => [Events::REQUEST_RESULTS],
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
		$instance->log_graphql_request_results($response, $schema, 'test_operation', $query, $variables, new Request(), null);
		$this->assert_log_count(1);


		// Check for error level and context
		$logs = $this->repository->get_logs([]);
		$this->assertCount(1, $logs);
		$log = $logs[0];
		$this->assertInstanceOf(DatabaseEntity::class, $log);

		$this->assertEquals(Level::Error->value, $log->get_level());
		$this->assertArrayHasKey('errors', $log->get_context());
		$this->assertEquals('WPGraphQL Response with Errors', $log->get_message());
	}


	public function test_graphql_request_results_log_event_add_context_with_subscriber(): void {

		// Add a subscriber to modify the context data and log level.
		Plugin::transform(Events::REQUEST_RESULTS, function(array $payload): array {
				$payload['context']['meta_data'] = 'This is meta value.';
				$payload['level'] = Level::Debug;

				return $payload;
			}
		);


		$instance = $this->create_instance(
			[
				BasicConfigurationTab::ENABLED => true,
				BasicConfigurationTab::EVENT_LOG_SELECTION => [Events::REQUEST_RESULTS],
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
		$instance->log_graphql_request_results($response, $schema, 'test_operation', $query, $variables, new Request(), null);
		$this->assert_log_count(1);


		// Check for the new meta_data field in the log entry.
		$logs = $this->repository->get_logs([], 10, 0);
		$this->assertNotEmpty($logs);
		$log_entry = $logs[0];
		$this->assertArrayHasKey('meta_data', $log_entry->get_context());
		$this->assertEquals('This is meta value.', $log_entry->get_context()['meta_data']);
		$this->assertEquals(Level::Debug->value, $log_entry->get_level());
	}
}
