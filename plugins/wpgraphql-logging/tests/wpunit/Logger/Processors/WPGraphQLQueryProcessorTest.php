<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Tests\Processors;

use lucatume\WPBrowser\TestCase\WPTestCase;
use Monolog\Level;
use Monolog\LogRecord;
use DateTimeImmutable;
use ReflectionProperty;
use WPGraphQL\Logging\Logger\Processors\WPGraphQL_Query_Processor;

/**
 * Class WPGraphQL_Query_ProcessorTest
 *
 * Tests for the WPGraphQL_Query_Processor class.
 *
 * @package WPGraphQL\Logging
 *
 * @since 0.0.1
 */
class WPGraphQL_Query_ProcessorTest extends WPTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        WPGraphQL_Query_Processor::clear_request_data();
    }

    public function tearDown(): void
    {
        WPGraphQL_Query_Processor::clear_request_data();
        parent::tearDown();
    }

    public function test_invoke_adds_nothing_when_data_is_not_captured(): void
    {
        $processor = new WPGraphQL_Query_Processor();
        $record = $this->get_test_record();

        $processed_record = $processor($record);

        $this->assertEmpty($processed_record->extra, 'Extra array should be empty when no data is captured.');
    }

    public function test_invoke_adds_captured_data_to_log_record(): void
    {
        $request_data = [
            'query'         => 'query TestQuery { posts { nodes { id } } }',
            'variables'     => ['first' => 10],
            'operationName' => 'TestQuery',
        ];

        // Manually capture the data to simulate a GraphQL request starting.
        WPGraphQL_Query_Processor::capture_request_data($request_data);

        $processor = new WPGraphQL_Query_Processor();
        $record = $this->get_test_record();

        $processed_record = $processor($record);

        // Assert that the extra array contains the correct GraphQL data.
        $this->assertArrayHasKey('wpgraphql_query', $processed_record->extra);
        $this->assertEquals($request_data['query'], $processed_record->extra['wpgraphql_query']);

        $this->assertArrayHasKey('wpgraphql_operation_name', $processed_record->extra);
        $this->assertEquals($request_data['operationName'], $processed_record->extra['wpgraphql_operation_name']);

        $this->assertArrayHasKey('wpgraphql_variables', $processed_record->extra);
        $this->assertEquals($request_data['variables'], $processed_record->extra['wpgraphql_variables']);
    }

    public function test_clear_request_data_resets_static_properties(): void
    {
        // 1. Capture some data.
        WPGraphQL_Query_Processor::capture_request_data([
            'query' => 'query Test { posts { nodes { id } } }'
        ]);

        // 2. Call the clear method.
        WPGraphQL_Query_Processor::clear_request_data();

        // 3. Use reflection to access the private static properties and check their values.
        $query_prop = new ReflectionProperty(WPGraphQL_Query_Processor::class, 'query');
        $query_prop->setAccessible(true);
        $this->assertNull($query_prop->getValue(), 'The static query property should be null after clearing.');

        $variables_prop = new ReflectionProperty(WPGraphQL_Query_Processor::class, 'variables');
        $variables_prop->setAccessible(true);
        $this->assertNull($variables_prop->getValue(), 'The static variables property should be null after clearing.');

        $operation_name_prop = new ReflectionProperty(WPGraphQL_Query_Processor::class, 'operation_name');
        $operation_name_prop->setAccessible(true);
        $this->assertNull($operation_name_prop->getValue(), 'The static operation_name property should be null after clearing.');
    }

    /**
     * @test
     * It should hook into WordPress actions upon instantiation.
     */
    public function test_constructor_hooks_into_wordpress_actions(): void
    {
        // Instantiate the processor to trigger its constructor.
        $processor = new WPGraphQL_Query_Processor();

        // Check if the hooks have been added correctly.
        $this->assertNotFalse(
            has_action('graphql_request_data', [WPGraphQL_Query_Processor::class, 'capture_request_data']),
            'The capture_request_data method should be hooked to graphql_request_data.'
        );

        $this->assertNotFalse(
            has_action('graphql_process_http_request_response', [WPGraphQL_Query_Processor::class, 'clear_request_data']),
            'The clear_request_data method should be hooked to graphql_process_http_request_response.'
        );

        // Clean up the hooks after the test.
        remove_action('graphql_request_data', [WPGraphQL_Query_Processor::class, 'capture_request_data'], 10);
        remove_action('graphql_process_http_request_response', [WPGraphQL_Query_Processor::class, 'clear_request_data'], 999);
    }

    /**
     * Helper method to get a basic LogRecord for testing.
     */
    private function get_test_record(): LogRecord
    {
        return new LogRecord(
            new DateTimeImmutable(),
            'test-channel',
            Level::Debug,
            'This is a test message.'
        );
    }
}
