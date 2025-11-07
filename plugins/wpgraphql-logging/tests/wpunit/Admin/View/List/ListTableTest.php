<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Tests\Admin\View\List;


use WPGraphQL\Logging\Admin\View\List\ListTable;
use WPGraphQL\Logging\Logger\Database\WordPressDatabaseEntity;
use WPGraphQL\Logging\Logger\Database\WordPressDatabaseLogService;
use Codeception\TestCase\WPTestCase;
use Mockery;

/**
 * Test class for ListTable.
 *
 * @package WPGraphQL\Logging
 *
 * @since 0.0.1
 */
class ListTableTest extends WPTestCase {

	private ListTable $list_table;
	private WordPressDatabaseLogService $log_service;

	public function setUp(): void {
		parent::setUp();

		$this->log_service = Mockery::mock(WordPressDatabaseLogService::class);
		$this->list_table = new ListTable($this->log_service);
	}

	public function tearDown(): void {
		Mockery::close();
		parent::tearDown();
	}

	public function test_constructor_sets_default_args(): void {
		$args = [
			'singular' => 'Custom Log',
			'plural'   => 'Custom Logs',
			'ajax'     => true,
		];

		$list_table = new ListTable($this->log_service, $args);

		$this->assertInstanceOf(ListTable::class, $list_table);
	}

	public function test_get_columns_returns_expected_columns(): void {
		$columns = $this->list_table->get_columns();

		$expected_columns = [
			'cb',
			'id',
			'date',
			'wpgraphql_query',
			'level',
			'level_name',
			'event',
			'process_id',
			'request_headers',
			'memory_usage',
		];

		foreach ($expected_columns as $column) {
			$this->assertArrayHasKey($column, $columns);
		}
	}

	public function test_get_bulk_actions_returns_expected_actions(): void {
		$actions = $this->list_table->get_bulk_actions();

		$this->assertArrayHasKey('delete', $actions);
		$this->assertArrayHasKey('delete_all', $actions);
		$this->assertEquals('Delete Selected', $actions['delete']);
		$this->assertEquals('Delete All', $actions['delete_all']);
	}

	public function test_get_sortable_columns_returns_expected_columns(): void {
		$reflection = new \ReflectionClass($this->list_table);
		$method = $reflection->getMethod('get_sortable_columns');
		$method->setAccessible(true);

		$sortable = $method->invoke($this->list_table);

		$this->assertArrayHasKey('id', $sortable);
		$this->assertArrayHasKey('date', $sortable);
		$this->assertArrayHasKey('level', $sortable);
		$this->assertArrayHasKey('level_name', $sortable);
	}

	public function test_column_cb_returns_checkbox_for_valid_item(): void {
		$entity = Mockery::mock(WordPressDatabaseEntity::class);
		$entity->shouldReceive('get_id')->andReturn(123);

		$result = $this->list_table->column_cb($entity);

		$this->assertStringContainsString('<input type="checkbox"', $result);
		$this->assertStringContainsString('value="123"', $result);
	}

	public function test_column_cb_returns_empty_string_for_invalid_item(): void {
		$result = $this->list_table->column_cb('invalid');

		$this->assertEquals('', $result);
	}

	public function test_column_id_returns_formatted_id_with_actions(): void {
		$entity = Mockery::mock(WordPressDatabaseEntity::class);
		$entity->shouldReceive('get_id')->andReturn(456);

		$result = $this->list_table->column_id($entity);

		$this->assertStringContainsString('456', $result);
		$this->assertStringContainsString('View', $result);
		$this->assertStringContainsString('Download', $result);
	}

	public function test_column_default_returns_null_for_invalid_item(): void {
		$result = $this->list_table->column_default('invalid', 'date');

		$this->assertNull($result);
	}

	public function test_column_default_returns_date_for_date_column(): void {
		$entity = Mockery::mock(WordPressDatabaseEntity::class);
		$entity->shouldReceive('get_datetime')->andReturn('2023-01-01 12:00:00');

		$result = $this->list_table->column_default($entity, 'date');

		$this->assertEquals('2023-01-01 12:00:00', $result);
	}

	public function test_column_default_returns_level_for_level_column(): void {
		$entity = Mockery::mock(WordPressDatabaseEntity::class);
		$entity->shouldReceive('get_level')->andReturn(200);

		$result = $this->list_table->column_default($entity, 'level');

		$this->assertEquals(200, $result);
	}

	public function test_get_query_returns_formatted_query(): void {
		$entity = Mockery::mock(WordPressDatabaseEntity::class);
		$entity->shouldReceive('get_query')->andReturn('{ user { id name } }');

		$result = $this->list_table->get_query($entity);

		$this->assertStringContainsString('<pre', $result);
		$this->assertStringContainsString('{ user { id name } }', $result);
	}

	public function test_get_query_returns_empty_string_for_empty_query(): void {
		$entity = Mockery::mock(WordPressDatabaseEntity::class);
		$entity->shouldReceive('get_query')->andReturn('');

		$result = $this->list_table->get_query($entity);

		$this->assertEquals('', $result);
	}

	public function test_get_event_returns_event_from_extra(): void {
		$entity = Mockery::mock(WordPressDatabaseEntity::class);
		$entity->shouldReceive('get_extra')->andReturn(['wpgraphql_event' => 'query_executed']);

		$result = $this->list_table->get_event($entity);

		$this->assertEquals('query_executed', $result);
	}

	public function test_get_event_returns_message_when_no_event_in_extra(): void {
		$entity = Mockery::mock(WordPressDatabaseEntity::class);
		$entity->shouldReceive('get_extra')->andReturn([]);
		$entity->shouldReceive('get_message')->andReturn('Default message');

		$result = $this->list_table->get_event($entity);

		$this->assertEquals('Default message', $result);
	}

	public function test_get_process_id_returns_process_id_from_extra(): void {
		$entity = Mockery::mock(WordPressDatabaseEntity::class);
		$entity->shouldReceive('get_extra')->andReturn(['process_id' => '12345']);

		$result = $this->list_table->get_process_id($entity);

		$this->assertEquals(12345, $result);
	}

	public function test_get_process_id_returns_zero_when_not_in_extra(): void {
		$entity = Mockery::mock(WordPressDatabaseEntity::class);
		$entity->shouldReceive('get_extra')->andReturn([]);

		$result = $this->list_table->get_process_id($entity);

		$this->assertEquals(0, $result);
	}

	public function test_get_memory_usage_returns_memory_from_extra(): void {
		$entity = Mockery::mock(WordPressDatabaseEntity::class);
		$entity->shouldReceive('get_extra')->andReturn(['memory_peak_usage' => '2MB']);

		$result = $this->list_table->get_memory_usage($entity);

		$this->assertEquals('2MB', $result);
	}

	public function test_get_request_headers_returns_formatted_headers(): void {
		$headers = ['Content-Type' => 'application/json', 'Authorization' => 'Bearer token'];
		$entity = Mockery::mock(WordPressDatabaseEntity::class);
		$entity->shouldReceive('get_extra')->andReturn(['request_headers' => $headers]);

		$result = $this->list_table->get_request_headers($entity);

		$this->assertStringContainsString('<pre', $result);
		$this->assertStringContainsString('Content-Type', $result);
		$this->assertStringContainsString('Authorization', $result);
	}

	public function test_get_request_headers_returns_empty_string_for_empty_headers(): void {
		$entity = Mockery::mock(WordPressDatabaseEntity::class);
		$entity->shouldReceive('get_extra')->andReturn([]);

		$result = $this->list_table->get_request_headers($entity);

		$this->assertEquals('', $result);
	}

	public function test_format_code_returns_formatted_pre_tag(): void {
		$reflection = new \ReflectionClass($this->list_table);
		$method = $reflection->getMethod('format_code');
		$method->setAccessible(true);

		$result = $method->invoke($this->list_table, 'test code');

		$this->assertStringContainsString('<pre', $result);
		$this->assertStringContainsString('test code', $result);
	}

	public function test_format_code_returns_empty_string_for_empty_input(): void {
		$reflection = new \ReflectionClass($this->list_table);
		$method = $reflection->getMethod('format_code');
		$method->setAccessible(true);

		$result = $method->invoke($this->list_table, '');

		$this->assertEquals('', $result);
	}

	public function test_process_where_returns_empty_array_for_invalid_nonce(): void {
		$reflection = new \ReflectionClass($this->list_table);
		$method = $reflection->getMethod('process_where');
		$method->setAccessible(true);

		$request = ['wpgraphql_logging_nonce' => 'invalid_nonce'];
		$result = $method->invoke($this->list_table, $request);

		$this->assertEquals([], $result);
	}

	public function test_process_where_handles_level_filter(): void {
		$reflection = new \ReflectionClass($this->list_table);
		$method = $reflection->getMethod('process_where');
		$method->setAccessible(true);

		$request = ['level_filter' => 'ERROR'];
		$result = $method->invoke($this->list_table, $request);

		$this->assertSame([
			[
				'column' => 'level_name',
				'operator' => '=',
				'value' => 'ERROR',
			],
		], $result);
	}

	public function test_process_where_handles_date_filters(): void {
		$reflection = new \ReflectionClass($this->list_table);
		$method = $reflection->getMethod('process_where');
		$method->setAccessible(true);

		$request = [
			'start_date' => '2025-01-01',
			'end_date' => '2025-12-31'
		];
		$result = $method->invoke($this->list_table, $request);

		$this->assertSame([
			[
				'column' => 'datetime',
				'operator' => '>=',
				'value' => '2025-01-01 00:00:00',
			],
			[
				'column' => 'datetime',
				'operator' => '<=',
				'value' => '2025-12-31 00:00:00',
			],
		], $result);
	}

	public function test_prepare_items_sets_pagination_args(): void {
		$this->log_service->shouldReceive('count_entities_by_where')->andReturn(50);
		$this->log_service->shouldReceive('find_entities_by_where')->andReturn([]);

		$_REQUEST = [];

		$this->list_table->prepare_items();

		// Verify that pagination was set up (indirectly through no exceptions)
		$this->assertTrue(true);
	}

	public function test_prepare_items_handles_orderby_and_order_params(): void {
		$this->log_service->shouldReceive('count_entities_by_where')->andReturn(10);
		$this->log_service->shouldReceive('find_entities_by_where')->andReturn([]);

		$_REQUEST = [
			'orderby' => 'date',
			'order' => 'DESC',
			'_wpnonce' => wp_create_nonce('wpgraphql-logging-sort'),
		];

		$this->list_table->prepare_items();

		// Verify that no exceptions were thrown
		$this->assertTrue(true);
	}

	public function test_column_query_returns_query_from_extra(): void {
		$entity = Mockery::mock(WordPressDatabaseEntity::class);
		$entity->shouldReceive('get_extra')->andReturn(['wpgraphql_query' => '{ user { id name } }']);

		$result = $this->list_table->column_query($entity);

		$this->assertEquals('{ user { id name } }', $result);
	}

	public function test_column_default_returns_channel_for_channel_column(): void {
		$entity = Mockery::mock(WordPressDatabaseEntity::class);
		$entity->shouldReceive('get_channel')->andReturn('wpgraphql');

		$result = $this->list_table->column_default($entity, 'channel');

		$this->assertEquals('wpgraphql', $result);
	}

	public function test_column_default_returns_level_name_for_level_name_column(): void {
		$entity = Mockery::mock(WordPressDatabaseEntity::class);
		$entity->shouldReceive('get_level_name')->andReturn('ERROR');

		$result = $this->list_table->column_default($entity, 'level_name');

		$this->assertEquals('ERROR', $result);
	}

	public function test_column_default_returns_message_for_message_column(): void {
		$entity = Mockery::mock(WordPressDatabaseEntity::class);
		$entity->shouldReceive('get_message')->andReturn('Test log message');

		$result = $this->list_table->column_default($entity, 'message');

		$this->assertEquals('Test log message', $result);
	}

	public function test_column_default_returns_event_for_event_column(): void {
		$entity = Mockery::mock(WordPressDatabaseEntity::class);
		$entity->shouldReceive('get_extra')->andReturn(['wpgraphql_event' => 'query_executed']);

		$result = $this->list_table->column_default($entity, 'event');

		$this->assertEquals('query_executed', $result);
	}

	public function test_column_default_returns_process_id_for_process_id_column(): void {
		$entity = Mockery::mock(WordPressDatabaseEntity::class);
		$entity->shouldReceive('get_extra')->andReturn(['process_id' => '98765']);

		$result = $this->list_table->column_default($entity, 'process_id');

		$this->assertEquals(98765, $result);
	}

	public function test_column_default_returns_memory_usage_for_memory_usage_column(): void {
		$entity = Mockery::mock(WordPressDatabaseEntity::class);
		$entity->shouldReceive('get_extra')->andReturn(['memory_peak_usage' => '5MB']);

		$result = $this->list_table->column_default($entity, 'memory_usage');

		$this->assertEquals('5MB', $result);
	}

	public function test_column_default_returns_query_for_wpgraphql_query_column(): void {
		$entity = Mockery::mock(WordPressDatabaseEntity::class);
		$entity->shouldReceive('get_query')->andReturn('{ posts { id title } }');

		$result = $this->list_table->column_default($entity, 'wpgraphql_query');

		$this->assertStringContainsString('<pre', $result);
		$this->assertStringContainsString('{ posts { id title } }', $result);
	}

	public function test_column_default_returns_headers_for_request_headers_column(): void {
		$headers = ['User-Agent' => 'Test Agent', 'Accept' => 'application/json'];
		$entity = Mockery::mock(WordPressDatabaseEntity::class);
		$entity->shouldReceive('get_extra')->andReturn(['request_headers' => $headers]);

		$result = $this->list_table->column_default($entity, 'request_headers');

		$this->assertStringContainsString('<pre', $result);
		$this->assertStringContainsString('User-Agent', $result);
		$this->assertStringContainsString('Test Agent', $result);
	}

	public function test_column_default_returns_empty_string_for_unknown_column(): void {
		$entity = Mockery::mock(WordPressDatabaseEntity::class);

		$result = $this->list_table->column_default($entity, 'unknown_column');

		$this->assertEquals('', $result);
	}

}
