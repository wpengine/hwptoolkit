<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Tests\Handlers;

use lucatume\WPBrowser\TestCase\WPTestCase;
use Monolog\Level;
use Monolog\LogRecord;
use DateTimeImmutable;
use WPGraphQL\Logging\Logger\Database\WordPressDatabaseEntity;
use WPGraphQL\Logging\Logger\Handlers\WordPressDatabaseHandler;
use WPGraphQL\Logging\Logger\Store\LogStoreService;

/**
 * Class WordPressDatabaseHandlerTest
 *
 * Tests for the WordPressDatabaseHandler class.
 *
 * @package WPGraphQL\Logging
 * @since 0.0.1
 */
class WordPressDatabaseHandlerTest extends WPTestCase
{
	protected LogRecord $record;

	protected array $log_data;

	/**
	 * Set up the test environment.
	 */
	public function setUp(): void
    {
        parent::setUp();
		$log_service = LogStoreService::get_log_service();
		$log_service->activate();

		// Setup test record data.
		$this->log_data = [
			'channel'    => 'wpgraphql_logging',
			'level'      => Level::Info,
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
			],
			'datetime'   => new DateTimeImmutable(),
		];

		// Create a LogRecord instance for testing.
		$this->record = new LogRecord(
			$this->log_data['datetime'],
			$this->log_data['channel'],
			$this->log_data['level'],
			$this->log_data['message'],
			$this->log_data['context'],
			$this->log_data['extra']
		);
    }

    public function tearDown(): void
    {
		$log_service = LogStoreService::get_log_service();
		if ( ! defined( 'WP_GRAPHQL_LOGGING_UNINSTALL_PLUGIN' ) ) {
			define( 'WP_GRAPHQL_LOGGING_UNINSTALL_PLUGIN', true );
		}
		$log_service->deactivate();
        parent::tearDown();
    }

    public function test_write_method_saves_log_to_database(): void
    {
        global $wpdb;

        // 3. Create an instance of the handler and call the write method.
        $handler = new WordPressDatabaseHandler();
  	 	$handler->handle($this->record);
		$log_data = $this->log_data;

        // 4. Verify the data was saved correctly in the database.
        $table_name = WordPressDatabaseEntity::get_table_name();
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
        $saved_row = $wpdb->get_row("SELECT * FROM {$table_name} ORDER BY id DESC LIMIT 1", ARRAY_A);

        $this->assertNotNull($saved_row, 'A log entry should have been created in the database.');
        $this->assertEquals($log_data['channel'], $saved_row['channel']);
        $this->assertEquals($log_data['level']->value, $saved_row['level']);
        $this->assertEquals($log_data['level']->getName(), $saved_row['level_name']);
        $this->assertEquals($log_data['message'], $saved_row['message']);

        // Compare the JSON-decoded context and extra fields.
        $this->assertEquals($log_data['context'], json_decode($saved_row['context'], true));
        $this->assertEquals($log_data['extra'], json_decode($saved_row['extra'], true));
    }

    public function test_write_method_handles_exceptions_gracefully(): void
    {
        // Drop the table to force the save operation to fail.
		$log_service = LogStoreService::get_log_service();
		if ( ! defined( 'WP_GRAPHQL_LOGGING_UNINSTALL_PLUGIN' ) ) {
			define( 'WP_GRAPHQL_LOGGING_UNINSTALL_PLUGIN', true );
		}
		$log_service->deactivate();
		global $wpdb;
		$wpdb->flush();

        $handler = new WordPressDatabaseHandler();
        try {
            $handler->handle($this->record);
            $this->assertTrue(true, 'The handler should catch the exception and not crash.');
        } catch (\Throwable $e) {
            $this->fail('The handler should have caught the exception. Error: ' . $e->getMessage());
        }
    }
}
