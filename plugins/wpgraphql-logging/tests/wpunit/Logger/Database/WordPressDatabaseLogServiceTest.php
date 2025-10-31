<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Tests\Logger\Database;

use lucatume\WPBrowser\TestCase\WPTestCase;
use WPGraphQL\Logging\Logger\Database\WordPressDatabaseLogService;
use WPGraphQL\Logging\Logger\Database\WordPressDatabaseEntity;
use WPGraphQL\Logging\Logger\Api\LogEntityInterface;

/**
 * Test for the WordPressDatabaseLogService
 *
 * @package WPGraphQL\Logging
 * @since 0.0.1
 */
class WordPressDatabaseLogServiceTest extends WPTestCase
{
    private WordPressDatabaseLogService $log_service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->log_service = new WordPressDatabaseLogService();
        $this->log_service->activate();
    }

    protected function tearDown(): void
    {
        $this->log_service->deactivate();
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

        $this->log_service->create_log_entity(...array_values($log_data));

		$log_data['level'] = 400;
		$log_data['level_name'] = 'ERROR';
		$log_data['message'] = 'WPGraphQL Error';
		sleep(1); // Ensure different timestamps

        $this->log_service->create_log_entity(...array_values($log_data));
	}

    public function test_find_entities_with_default_args(): void
    {
        $this->insert_mock_data();
        $logs = $this->log_service->find_entities_by_where();
        $this->assertIsArray($logs);
        $this->assertCount(2, $logs);
        $this->assertInstanceOf(LogEntityInterface::class, $logs[0]);
        $this->assertInstanceOf(LogEntityInterface::class, $logs[1]);
    }

    public function test_find_entities_with_custom_args(): void
    {
        $this->insert_mock_data();
        $args = [
            'number'  => 50,
            'offset'  => 0,
            'orderby' => 'datetime',
            'order'   => 'DESC'
        ];
        $logs = $this->log_service->find_entities_by_where($args);
        $this->assertIsArray($logs);
        $this->assertCount(2, $logs);

        // Should get the last inserted log first
        $this->assertEquals('WPGraphQL Error', $logs[0]->get_message());

        /**
         * Test default orderby
         */
        $args['orderby'] = 'id';
        $logs = $this->log_service->find_entities_by_where($args);
        $this->assertIsArray($logs);

        // Should be last as default is DESC
        $this->assertEquals('WPGraphQL Error', $logs[0]->get_message());

        /**
         * Test where clause
         */
        $args['where'] = [
            [
                'column'   => 'level',
                'operator' => '=',
                'value'    => '200',
            ],
        ];

        $logs = $this->log_service->find_entities_by_where($args);
        $this->assertCount(1, $logs);
        $this->assertEquals('WPGraphQL Outgoing Response', $logs[0]->get_message());

        /**
         * Test invalid order
         */
        $args['order'] = '';
        $logs = $this->log_service->find_entities_by_where($args);
        $this->assertIsArray($logs);
        $this->assertCount(1, $logs);

        // Check log count
        $this->assertEquals(2, $this->log_service->count_entities_by_where([]));
        $this->assertEquals(2, $this->log_service->count_entities_by_where($args));
    }

    public function test_delete_logs(): void
    {
        $this->insert_mock_data();
        $logs = $this->log_service->find_entities_by_where();
        $this->assertCount(2, $logs);

        // Delete one log
        $result = $this->log_service->delete_entity_by_id($logs[0]->get_id());
        $this->assertTrue($result);

        // Delete invalid logs
        $result = $this->log_service->delete_entity_by_id(0);
        $this->assertFalse($result);

        // Check remaining logs
        $logs = $this->log_service->find_entities_by_where();
        $this->assertCount(1, $logs);

        // Delete all logs
        $this->log_service->delete_all_entities();
        $logs = $this->log_service->find_entities_by_where();
        $this->assertCount(0, $logs);
    }

    public function test_delete_log_older_than(): void
    {
        $this->insert_mock_data();
        $logs = $this->log_service->find_entities_by_where(['orderby' => 'datetime', 'order' => 'ASC']);
        $this->assertCount(2, $logs);

        $date = $logs[0]->get_datetime();
        $dateTime = new \DateTime($date);
        $dateTime->modify('+1 second');

        $result = $this->log_service->delete_entities_older_than($dateTime);
        $this->assertTrue($result);

        $logs = $this->log_service->find_entities_by_where();
        $this->assertCount(1, $logs);

        // Delete last log
        $dateTime->modify('+1 second');
        $result = $this->log_service->delete_entities_older_than($dateTime);
        $this->assertTrue($result);

        $logs = $this->log_service->find_entities_by_where();
        $this->assertCount(0, $logs);
    }
}
