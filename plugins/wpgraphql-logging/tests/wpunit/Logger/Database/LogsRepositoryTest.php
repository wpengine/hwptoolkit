<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Tests\Logger\Database;

use lucatume\WPBrowser\TestCase\WPTestCase;
use DateTimeImmutable;
use ReflectionClass;
use WPGraphQL\Logging\Logger\Database\DatabaseEntity;
use WPGraphQL\Logging\Logger\Database\LogsRepository;
use Mockery;

/**
 * Test for the LogsRepository
 *
 * @package WPGraphQL\Logging
 * @since 0.0.1
 */
class LogsRepositoryTest extends WPTestCase
{
	private LogsRepository $logs_repository;

	protected function setUp(): void
	{
		parent::setUp();
		$this->logs_repository = new LogsRepository();

		// Create the database table for testing
		DatabaseEntity::create_table();
	}

	protected function tearDown(): void
	{
		// Clean up the database table
		$this->logs_repository->delete_all();
		parent::tearDown();
	}

	public function insert_mock_data(): void
	{
		$log_data = [
			'channel'    => 'wpgraphql_logging',
			'level'      => 200,
			'level_name' => 'INFO',
			'message'    => 'WPGraphQL Outgoing Response',
			'context'    => [
				'site_url'      => 'http://test.local',
				'wp_version'    => '6.8.2',
				'wp_debug_mode' => true,
				'plugin_version'=> '0.0.1'
			],
			'extra'      => [
				'ip' => '127.0.0.1',
				'url' => '/index.php?graphql',
				'server' => 'test.local',
				'referrer' => 'http://test.local/wp-admin/admin.php?page=graphiql-ide',
				'process_id' => 5819,
				'http_method' => 'POST',
				'memory_usage' => '14 MB',
				'wpgraphql_query' => 'query GetPost($uri: ID!) { post(id: $uri, idType: URI) { title content } }',
				'memory_peak_usage' => '14 MB',
				'wpgraphql_variables' => [
					'uri' => 'hello-world'
				],
				'wpgraphql_operation_name' => 'GetPost'
			]
		];

		$log_entry = DatabaseEntity::create(...$log_data);
		$log_entry->save();
		$log_data['level'] = 400;
		$log_data['level_name'] = 'ERROR';
		$log_data['message'] = 'WPGraphQL Error';
		sleep(1); // Ensure different timestamps
		$log_entry = DatabaseEntity::create(...$log_data);
		$log_entry->save();
	}

	public function test_get_logs_with_default_args(): void
	{
		$this->insert_mock_data();
		$logs = $this->logs_repository->get_logs();
		$this->assertIsArray($logs);
		$this->assertCount(2, $logs);
		$this->assertInstanceOf(DatabaseEntity::class, $logs[0]);
		$this->assertInstanceOf(DatabaseEntity::class, $logs[1]);
	}

	public function test_get_logs_with_custom_args(): void
	{
		$this->insert_mock_data();
		$args = [
			'number' => 50,
			'offset' => 0,
			'orderby' => 'datetime',
			'order' => 'DESC'
		];
		$logs = $this->logs_repository->get_logs($args);
		$this->assertIsArray($logs);
		$this->assertCount(2, $logs);

		// Should get the last inserted log first
		$this->assertEquals('WPGraphQL Error', $logs[0]->get_message());

		/**
		 * Test default orderby
		 */
		$args['orderby'] = '';
		$logs = $this->logs_repository->get_logs($args);
		$this->assertIsArray($logs);

		// Should be last as default is DESC
		$this->assertEquals('WPGraphQL Error', $logs[0]->get_message());

		/**
		 * Test where is string should not work
		 */
		$args['where'] = 'level = 200';
		$logs = $this->logs_repository->get_logs($args);
		$this->assertIsArray($logs);

		/**
		 * Test invalid order
		 */
		$args['order'] = '';
		$logs = $this->logs_repository->get_logs($args);
		$this->assertIsArray($logs);
		$this->assertCount(2, $logs);

		// Should be last one as where clause is ignored
		$this->assertEquals('WPGraphQL Error', $logs[0]->get_message());

		// Check log count
		$this->assertEquals(2, $this->logs_repository->get_log_count([]));
		$this->assertEquals(1, $this->logs_repository->get_log_count(["level = 400", "channel = 'wpgraphql_logging'"]));
	}

	public function test_delete_logs(): void
	{
		$this->insert_mock_data();
		$logs = $this->logs_repository->get_logs();
		$this->assertCount(2, $logs);

		// Delete one log
		$result = $this->logs_repository->delete($logs[0]->get_id());
		$this->assertTrue($result);

		// Delete invalid logs
		$result = $this->logs_repository->delete(0);
		$this->assertFalse($result);

		// Check remaining logs
		$logs = $this->logs_repository->get_logs();
		$this->assertCount(1, $logs);

		// Delete all logs
		$this->logs_repository->delete_all();
		$logs = $this->logs_repository->get_logs();
		$this->assertCount(0, $logs);
	}

	public function test_delete_log_older_than(): void
	{
		$this->insert_mock_data();
		$logs = $this->logs_repository->get_logs();
		$this->assertCount(2, $logs);
		$date = $logs[0]->get_datetime();
		$dateTime = new \DateTime($date);

		$result = $this->logs_repository->delete_log_older_than($dateTime);
		$this->assertTrue($result);
		$logs = $this->logs_repository->get_logs();
		$this->assertCount(1, $logs);

		// Delete last log
		$dateTime->modify('+1 second');
		$result = $this->logs_repository->delete_log_older_than($dateTime);
		$this->assertTrue($result);
		$logs = $this->logs_repository->get_logs();
		$this->assertCount(0, $logs);
	}
}
