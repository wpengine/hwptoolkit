<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Tests\Database;

use lucatume\WPBrowser\TestCase\WPTestCase;
use DateTimeImmutable;
use ReflectionClass;
use WPGraphQL\Logging\Logger\Database\DatabaseEntity;

/**
 * Class DatabaseEntityTest
 *
 * Tests for the DatabaseEntity class
 */
class DatabaseEntityTest extends WPTestCase
{

    public function setUp(): void
    {
        parent::setUp();
        $this->drop_table();
        $this->create_table();
    }

    public function tearDown(): void
    {
        $this->drop_table();
        parent::tearDown();
    }

    private function drop_table(): void
    {
        DatabaseEntity::drop_table();
    }

    private function create_table(): void
    {
	    DatabaseEntity::create_table();
    }

    public function test_save_method_inserts_log_into_database(): void
    {
        global $wpdb;

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

        // Create and save the entity
        $entity = DatabaseEntity::create(...array_values($log_data));
        $insert_id = $entity->save();

		$this->assertIsInt( $insert_id );
        $this->assertGreaterThan(0, $insert_id, 'The save method should return a positive insert ID.');

		$entity = DatabaseEntity::find($insert_id);
		$this->assertInstanceOf(DatabaseEntity::class, $entity, 'The find method should return an instance of DatabaseEntity.');
		$this->assertEquals($log_data['channel'], $entity->get_channel(), 'The channel should match the saved data.');
		$this->assertEquals($log_data['level'], $entity->get_level(), 'The level should match the saved data.');
		$this->assertEquals($log_data['level_name'], $entity->get_level_name(), 'The level name should match the saved data.');
		$this->assertEquals($log_data['message'], $entity->get_message(), 'The message should match the saved data.');
		$this->assertEquals($log_data['context'], $entity->get_context(), 'The context should match the saved data.');
		$this->assertEquals($log_data['extra'], $entity->get_extra(), 'The extra data should match the saved data.');

		$this->assertNotEmpty($entity->get_datetime(), 'The datetime should not be empty.');
		$this->assertIsInt($entity->get_id(), 'The ID should be an integer.');
		$this->assertGreaterThan(0, $entity->get_id(), 'The ID should be greater than 0.');
	}

	public function test_find_returns_null_for_nonexistent_id(): void
	{
		$entity = DatabaseEntity::find(999999);
		$this->assertNull($entity, 'find() should return null for a non-existent ID.');
	}

   /**
    * @test
    * It should sanitize an array recursively.
    */
   public function test_sanitize_array_field_method(): void
   {
       $reflection = new ReflectionClass(DatabaseEntity::class);
       $method = $reflection->getMethod('sanitize_array_field');
       $method->setAccessible(true);

       $dirty_array = [
           'field1' => 'Safe',
           'field2' => 'Unsafe <script>alert(1)</script>',
           'nested' => [
               'deep_field' => 'Also <html>unsafe</html>',
               'numeric' => 123,
               'bool' => true,
           ],
       ];

       $expected_clean_array = [
           'field1' => 'Safe',
           'field2' => 'Unsafe',
           'nested' => [
               'deep_field' => 'Also unsafe',
               'numeric' => 123,
               'bool' => true,
           ],
       ];

       $clean_array = $method->invoke($this->entity_instance, $dirty_array);

       $this->assertEquals($expected_clean_array, $clean_array);
   }

       /**
     * @test
     * It should return 0 when the database insert operation fails.
     *
     * This test uses a WordPress filter to intercept the database query
     * and force it to fail, simulating a database error.
     */
    public function test_save_returns_zero_on_database_failure(): void
    {
        // 1. Define a callback that will force the DB query to fail.
        $force_fail_callback = function ($query) {
            // Check if this is the specific INSERT query we want to fail.
            if (strpos($query, 'INSERT INTO `' . DatabaseEntity::get_table_name() . '`') !== false) {
                // Returning a non-string value like `false` will cause the
                // $wpdb->insert() method to fail and return false.
                return false;
            }
            // For all other queries, let them pass through untouched.
            return $query;
        };

        // 2. Add the filter to intercept the query.
        add_filter('query', $force_fail_callback);

        // 3. Create a valid entity that we will attempt to save.
        $entity = DatabaseEntity::create(
            'failure_test',
            500,
            'CRITICAL',
            'This save operation is designed to fail.'
        );

        // 4. Call the save method. Due to our filter, this will fail.
        $result_id = $entity->save();

        // 5. CRUCIAL: Remove the filter so it doesn't affect other tests.
        remove_filter('query', $force_fail_callback);

        // 6. Assert that the save method returned 0, indicating failure.
        $this->assertSame(0, $result_id, 'The save() method should return 0 when the database insert fails.');
    }
}
