<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Tests\Handlers;

use lucatume\WPBrowser\TestCase\WPTestCase;
use Monolog\Level;
use Monolog\LogRecord;
use DateTimeImmutable;
use WPGraphQL\Logging\Logger\Database\DatabaseEntity;
use WPGraphQL\Logging\Logger\Handlers\WordPressDatabaseHandler;

/**
 * Class WordPressDatabaseHandlerTest
 *
 * Tests for the WordPressDatabaseHandler class.
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
        DatabaseEntity::create_table();

		// Setup test record data.
		$this->log_data = [
			'channel'    => 'wpgraphql_logging',
			'level'      => Level::Info,
			'message'    => 'Test log message',
			'context'    => ['test_key' => 'test_value'],
			'extra'      => ['extra_key' => 'extra_value'],
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
        DatabaseEntity::drop_table();
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
        $table_name = DatabaseEntity::get_table_name();
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
        DatabaseEntity::drop_table();
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
