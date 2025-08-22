<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Tests\Events;

use lucatume\WPBrowser\TestCase\WPTestCase;
use WPGraphQL\Logging\Events\Request_Context_Service;

/**
 * Class RequestContextServiceTest
 *
 * Tests for the Request_Context_Service class.
 */
class RequestContextServiceTest extends WPTestCase {

	public function test_constructor_with_all_parameters(): void {
		$query = 'query GetPost { post(id: 1) { title } }';
		$operation_name = 'GetPost';
		$variables = ['id' => 1];

		$service = new Request_Context_Service($query, $operation_name, $variables);

		$this->assertEquals($query, $service->query);
		$this->assertEquals($operation_name, $service->operation_name);
		$this->assertEquals($variables, $service->variables);
	}

	public function test_constructor_with_null_parameters(): void {
		$service = new Request_Context_Service();

		$this->assertNull($service->query);
		$this->assertNull($service->operation_name);
		$this->assertNull($service->variables);
	}

	public function test_constructor_with_partial_parameters(): void {
		$query = 'query GetUser { user { name } }';

		$service = new Request_Context_Service($query);

		$this->assertEquals($query, $service->query);
		$this->assertNull($service->operation_name);
		$this->assertNull($service->variables);
	}

	public function test_set_data_stores_additional_data(): void {
		$service = new Request_Context_Service();

		$service->set_data('user_id', 123);
		$service->set_data('timestamp', '2024-01-01');

		$this->assertEquals(123, $service->get_data('user_id'));
		$this->assertEquals('2024-01-01', $service->get_data('timestamp'));
	}

	public function test_get_data_returns_default_when_key_not_exists(): void {
		$service = new Request_Context_Service();

		$this->assertNull($service->get_data('nonexistent'));
		$this->assertEquals('default_value', $service->get_data('nonexistent', 'default_value'));
		$this->assertEquals(42, $service->get_data('nonexistent', 42));
	}

	public function test_get_data_returns_stored_value(): void {
		$service = new Request_Context_Service();

		$service->set_data('test_key', 'test_value');

		$this->assertEquals('test_value', $service->get_data('test_key'));
		$this->assertEquals('test_value', $service->get_data('test_key', 'default'));
	}

	public function test_set_data_overwrites_existing_data(): void {
		$service = new Request_Context_Service();

		$service->set_data('key', 'original');
		$service->set_data('key', 'updated');

		$this->assertEquals('updated', $service->get_data('key'));
	}

	public function test_remove_data_removes_stored_data(): void {
		$service = new Request_Context_Service();

		$service->set_data('to_remove', 'value');
		$service->set_data('to_keep', 'value');

		$this->assertEquals('value', $service->get_data('to_remove'));

		$service->remove_data('to_remove');

		$this->assertNull($service->get_data('to_remove'));
		$this->assertEquals('value', $service->get_data('to_keep'));
	}

	public function test_remove_data_handles_nonexistent_key(): void {
		$service = new Request_Context_Service();

		// Should not throw an error
		$service->remove_data('nonexistent_key');

		$this->assertNull($service->get_data('nonexistent_key'));
	}

	public function test_get_all_additional_data_returns_all_stored_data(): void {
		$service = new Request_Context_Service();

		$service->set_data('key1', 'value1');
		$service->set_data('key2', 'value2');
		$service->set_data('key3', 'value3');

		$all_data = $service->get_all_additional_data();

		$expected = [
			'key1' => 'value1',
			'key2' => 'value2',
			'key3' => 'value3',
		];

		$this->assertEquals($expected, $all_data);
	}

	public function test_get_all_additional_data_returns_empty_array_when_no_data(): void {
		$service = new Request_Context_Service();

		$this->assertEquals([], $service->get_all_additional_data());
	}

	public function test_clear_additional_data_removes_all_data(): void {
		$service = new Request_Context_Service();

		$service->set_data('key1', 'value1');
		$service->set_data('key2', 'value2');

		$this->assertNotEmpty($service->get_all_additional_data());

		$service->clear_additional_data();

		$this->assertEquals([], $service->get_all_additional_data());
		$this->assertNull($service->get_data('key1'));
		$this->assertNull($service->get_data('key2'));
	}

	public function test_to_array_includes_constructor_parameters(): void {
		$query = 'query GetPost { post { title } }';
		$operation_name = 'GetPost';
		$variables = ['id' => 1];

		$service = new Request_Context_Service($query, $operation_name, $variables);

		$array = $service->to_array();

		$this->assertEquals($query, $array['query']);
		$this->assertEquals($operation_name, $array['operation_name']);
		$this->assertEquals($variables, $array['variables']);
	}

	public function test_to_array_includes_additional_data(): void {
		$service = new Request_Context_Service();

		$service->set_data('user_id', 123);
		$service->set_data('timestamp', '2024-01-01');

		$array = $service->to_array();

		$this->assertEquals(123, $array['user_id']);
		$this->assertEquals('2024-01-01', $array['timestamp']);
	}

	public function test_to_array_combines_constructor_and_additional_data(): void {
		$query = 'query GetUser { user { name } }';
		$operation_name = 'GetUser';
		$variables = ['id' => 42];

		$service = new Request_Context_Service($query, $operation_name, $variables);

		$service->set_data('user_id', 123);
		$service->set_data('execution_time', 0.5);

		$array = $service->to_array();

		$expected = [
			'query' => $query,
			'operation_name' => $operation_name,
			'variables' => $variables,
			'user_id' => 123,
			'execution_time' => 0.5,
		];

		$this->assertEquals($expected, $array);
	}

	public function test_to_array_with_null_constructor_parameters(): void {
		$service = new Request_Context_Service();

		$service->set_data('custom_data', 'value');

		$array = $service->to_array();

		$expected = [
			'query' => null,
			'operation_name' => null,
			'variables' => null,
			'custom_data' => 'value',
		];

		$this->assertEquals($expected, $array);
	}

	public function test_additional_data_key_overwrites_constructor_parameter(): void {
		$service = new Request_Context_Service('original_query');

		// Additional data should overwrite constructor parameter in to_array output
		$service->set_data('query', 'overwritten_query');

		$array = $service->to_array();

		// The spread operator in to_array should make additional_data values take precedence
		$this->assertEquals('overwritten_query', $array['query']);
	}

	public function test_supports_various_data_types(): void {
		$service = new Request_Context_Service();

		$service->set_data('string', 'text');
		$service->set_data('integer', 42);
		$service->set_data('float', 3.14);
		$service->set_data('boolean', true);
		$service->set_data('array', ['a', 'b', 'c']);
		$service->set_data('object', (object) ['key' => 'value']);
		$service->set_data('null', null);

		$this->assertEquals('text', $service->get_data('string'));
		$this->assertEquals(42, $service->get_data('integer'));
		$this->assertEquals(3.14, $service->get_data('float'));
		$this->assertTrue($service->get_data('boolean'));
		$this->assertEquals(['a', 'b', 'c'], $service->get_data('array'));
		$this->assertEquals((object) ['key' => 'value'], $service->get_data('object'));
		$this->assertNull($service->get_data('null'));
	}
}
