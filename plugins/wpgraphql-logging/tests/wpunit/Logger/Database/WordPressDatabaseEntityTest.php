<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Tests\Logger\Database;

use lucatume\WPBrowser\TestCase\WPTestCase;
use ReflectionClass;
use WPGraphQL\Logging\Logger\Database\WordPressDatabaseEntity;
use Mockery;

/**
 * Test for the WordPressDatabaseEntity
 *
 * @package WPGraphQL\Logging
 * @since 0.0.1
 */
class WordPressDatabaseEntityTest extends WPTestCase
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
		Mockery::close();
    }

    private function drop_table(): void
    {
        global $wpdb;
        $table_name = WordPressDatabaseEntity::get_table_name();
        $wpdb->query("DROP TABLE IF EXISTS {$table_name}");
    }

    private function create_table(): void
    {
		global $wpdb;
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        $schema = WordPressDatabaseEntity::get_schema();
        dbDelta($schema);

		if ($wpdb->last_error) {
			$this->fail("dbDelta failed: " . $wpdb->last_error);
		}
    }

    /**
     * Helper to recursively sanitize an array using sanitize_text_field on string values.
     *
     * @param array<mixed> $data The array to sanitize.
     * @return array<mixed> The sanitized array.
     */
    private function sanitize_array(array $data): array
    {
        foreach ($data as &$value) {
            if (is_string($value)) {
                $value = sanitize_text_field($value);
            } elseif (is_array($value)) {
                $value = $this->sanitize_array($value);
            }
        }
        return $data;
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
        $entity = new WordPressDatabaseEntity(
            $log_data['channel'],
            $log_data['level'],
            $log_data['level_name'],
            $log_data['message'],
            $log_data['context'],
            $log_data['extra']
        );
        $insert_id = $entity->save();

		$this->assertIsInt( $insert_id );
        $this->assertGreaterThan(0, $insert_id, 'The save method should return a positive insert ID.');

		// Retrieve the log directly from the database
        $table_name = WordPressDatabaseEntity::get_table_name();
        $retrieved_log = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table_name} WHERE id = %d", $insert_id), ARRAY_A);

        $this->assertIsArray($retrieved_log, 'The log should be retrieved from the database.');

        $entity_from_db = WordPressDatabaseEntity::from_array($retrieved_log);

		$this->assertInstanceOf(WordPressDatabaseEntity::class, $entity_from_db, 'from_array should return an instance of WordPressDatabaseEntity.');
		$this->assertEquals($log_data['channel'], $entity_from_db->get_channel(), 'The channel should match the saved data.');
		$this->assertEquals($log_data['level'], $entity_from_db->get_level(), 'The level should match the saved data.');
		$this->assertEquals($log_data['level_name'], $entity_from_db->get_level_name(), 'The level name should match the saved data.');
		$this->assertEquals($log_data['message'], $entity_from_db->get_message(), 'The message should match the saved data.');

        // For context and extra, we need to compare them after sanitization is applied on the original data.
        $sanitized_context = $this->sanitize_array($log_data['context']);
        $sanitized_extra = $this->sanitize_array($log_data['extra']);

		$this->assertEquals($sanitized_context, $entity_from_db->get_context(), 'The context should match the saved data.');
		$this->assertEquals($sanitized_extra, $entity_from_db->get_extra(), 'The extra data should match the saved data.');

		$this->assertNotEmpty($entity_from_db->get_datetime(), 'The datetime should not be empty.');
		$this->assertIsInt($entity_from_db->get_id(), 'The ID should be an integer.');
		$this->assertGreaterThan(0, $entity_from_db->get_id(), 'The ID should be greater than 0.');
	}

   /**
    * @test
    * It should sanitize an array recursively.
    */
   public function test_sanitize_array_field_method(): void
   {
       $reflection = new ReflectionClass(WordPressDatabaseEntity::class);
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

       $entity_instance = new WordPressDatabaseEntity('test', 100, 'DEBUG', 'test message');
       $clean_array = $method->invoke($entity_instance, $dirty_array);

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
            if (strpos($query, 'INSERT INTO `' . WordPressDatabaseEntity::get_table_name() . '`') !== false) {
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
        $entity = new WordPressDatabaseEntity(
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

	public function test_get_query() : void
	{
		$mockEntity = Mockery::mock(WordPressDatabaseEntity::class)->makePartial();
		$mockEntity->shouldReceive('get_context')->andReturn([
			'query' => 'query GetAllPosts { posts { nodes { title content } } }'
		]);

		$this->assertEquals(
			'query GetAllPosts { posts { nodes { title content } } }',
			$mockEntity->get_query()
		);
	}

	public function test_get_query_in_request() : void
	{
		$mockEntity = Mockery::mock(WordPressDatabaseEntity::class)->makePartial();
		$mockEntity->shouldReceive('get_context')->andReturn([
			'request' => [
				'params' => [
					'query' => 'query GetAllPosts { posts { nodes { title content } } }'
				]
			]
		]);

		$this->assertEquals(
			'query GetAllPosts { posts { nodes { title content } } }',
			$mockEntity->get_query()
		);
	}

	public function test_get_invalid_query() : void
	{
		$mockEntity = Mockery::mock(WordPressDatabaseEntity::class)->makePartial();
		$mockEntity->shouldReceive('get_context')->andReturn([
			'request' => 'query GetAllPosts { posts { nodes { title content } } }'
		]);

		$this->assertNull(
			$mockEntity->get_query()
		);

		$mockEntity = Mockery::mock(WordPressDatabaseEntity::class)->makePartial();
		$mockEntity->shouldReceive('get_context')->andReturn([
			'request' => [
				'query GetAllPosts { posts { nodes { title content } } }'
			]
		]);

		$this->assertNull(
			$mockEntity->get_query()
		);

		$mockEntity = Mockery::mock(WordPressDatabaseEntity::class)->makePartial();
		$mockEntity->shouldReceive('get_context')->andReturn([]);

		$this->assertNull(
			$mockEntity->get_query()
		);


		$mockEntity = Mockery::mock(WordPressDatabaseEntity::class)->makePartial();
		$mockEntity->shouldReceive('get_context')->andReturn([
			'request' => [
				'params' => [
					'invalid_key' => 'query GetAllPosts { posts { nodes { title content } } }'
				]
			]
		]);

		$this->assertNull(
			$mockEntity->get_query()
		);
	}
}
