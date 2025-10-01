<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Tests\Admin\View\Download;

use lucatume\WPBrowser\TestCase\WPTestCase;
use WPGraphQL\Logging\Admin\View\Download\DownloadLogService;
use WPGraphQL\Logging\Logger\Database\LogsRepository;
use WPGraphQL\Logging\Logger\Database\DatabaseEntity;
use Mockery;


/**
 * Test class for DownloadLogService.
 *
 * @package WPGraphQL\Logging
 *
 * @since 0.0.1
 */
class DownloadLogServiceTest extends WPTestCase {

	private DownloadLogService $service;
	private LogsRepository $repository;


	protected array $fixture =  [
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


	public function setUp(): void {
		parent::setUp();
		$this->service = new DownloadLogService();
		$this->repository = new LogsRepository();
	}

	public function set_as_admin(): void {
		$admin_user = $this->factory->user->create(['role' => 'administrator']);
		wp_set_current_user($admin_user);
		set_current_screen('dashboard');
	}

	public function test_generate_csv_requires_admin_capabilities(): void {
		// Test without admin capabilities
		wp_set_current_user(0);

		$this->expectException(\WPDieException::class);
		$this->expectExceptionMessage('You do not have sufficient permissions to access this page.');

		$this->service->generate_csv(1);
	}

	public function test_generate_csv_requires_valid_log_id_not_zero(): void {
		$this->set_as_admin();

		$this->expectException(\WPDieException::class);
		$this->expectExceptionMessage('Invalid log ID.');

		$this->service->generate_csv(0);
	}

	public function test_generate_csv_requires_valid_log_id_in_database(): void {
		$this->set_as_admin();

		$this->expectException(\WPDieException::class);
		$this->expectExceptionMessage('Log not found.');

		$this->service->generate_csv(9999999);
	}

	public function test_generate_csv_returns_valid_csv(): void {
		$this->set_as_admin();
		// Mock a database entity instead of creating a real one
		$entity = \Mockery::mock(DatabaseEntity::class);
		$entity->shouldReceive('get_id')->andReturn(123);
		$entity->shouldReceive('get_datetime')->andReturn('2023-01-01 12:00:00');
		$entity->shouldReceive('get_level')->andReturn($this->fixture['level']);
		$entity->shouldReceive('get_level_name')->andReturn($this->fixture['level_name']);
		$entity->shouldReceive('get_message')->andReturn($this->fixture['message']);
		$entity->shouldReceive('get_channel')->andReturn($this->fixture['channel']);
		$entity->shouldReceive('get_query')->andReturn($this->fixture['extra']['wpgraphql_query']);
		$entity->shouldReceive('get_context')->andReturn($this->fixture['context']);
		$entity->shouldReceive('get_extra')->andReturn($this->fixture['extra']);

		// Mock the repository to return our mocked entity
		$this->repository = \Mockery::mock(LogsRepository::class);
		$this->repository->shouldReceive('find_by_id')->with(123)->andReturn($entity);

		// Inject the mocked repository into the service
		$this->service = new DownloadLogService($this->repository);

		$log_id = 123;


		$headers = $this->service->get_headers($entity);
		$content = $this->service->get_content($entity);

		$this->assertSame([
			'ID',
			'Date',
			'Level',
			'Level Name',
			'Message',
			'Channel',
			'Query',
			'Context',
			'Extra',
		], $headers);

		$this->assertIsArray($content);
		$this->assertCount(9, $content);
		$this->assertEquals($log_id, $content[0]);
		$this->assertEquals($this->fixture['level'], $content[2]);
		$this->assertEquals($this->fixture['level_name'], $content[3]);
		$this->assertEquals($this->fixture['message'], $content[4]);
		$this->assertEquals($this->fixture['channel'], $content[5]);
		$this->assertEquals(wp_json_encode($this->fixture['context']), $content[7]);
		$this->assertEquals(wp_json_encode($this->fixture['extra']), $content[8]);


		// Capture output
		// ob_start();
		// $this->service->generate_csv($log_id);
		// $output = ob_get_clean();

		// // // Parse CSV output
		// $lines = explode("\n", trim($output));
		// $headers = str_getcsv($lines[0]);
		// $content = str_getcsv($lines[1]);

		// // Verify headers
		// $expected_headers = [
		// 	'ID',
		// 	'Date',
		// 	'Level',
		// 	'Level Name',
		// 	'Message',
		// 	'Channel',
		// 	'Query',
		// 	'Context',
		// 	'Extra',
		// ];
		// $this->assertEquals($expected_headers, $headers);

	// 	// Verify content
	// 	$this->assertEquals($log_id, $content[0]);
	// 	$this->assertEquals('2023-01-01 12:00:00', $content[1]);
	// 	$this->assertEquals('200', $content[2]);
	// 	$this->assertEquals('INFO', $content[3]);
	// 	$this->assertEquals('Test log message', $content[4]);
	// 	$this->assertEquals('wpgraphql', $content[5]);
	// 	$this->assertEquals('query { posts { nodes { id title } } }', $content[6]);
	// 	$this->assertEquals('{"user_id":1}', $content[7]);
	// 	$this->assertEquals('{"test":"data"}', $content[8]);
	// }

	// public function test_generate_csv_with_nonexistent_log(): void {
	// 	// Set admin user
	// 	$admin_user = $this->factory->user->create(['role' => 'administrator']);
	// 	wp_set_current_user($admin_user);
	// 	set_current_screen('dashboard');

	// 	$this->expectOutputString('');

	// 	ob_start();
	// 	$this->service->generate_csv(999999);
	// 	$output = ob_get_clean();

	// 	$this->assertEmpty($output);
	}

	// public function test_csv_filename_filter(): void {
	// 	// Set admin user
	// 	$admin_user = $this->factory->user->create(['role' => 'administrator']);
	// 	wp_set_current_user($admin_user);
	// 	set_current_screen('dashboard');

	// 	// Create test log entry
	// 	global $wpdb;
	// 	$wpdb->insert(
	// 		$wpdb->prefix . 'wpgraphql_logs',
	// 		[
	// 			'level' => 200,
	// 			'level_name' => 'INFO',
	// 			'message' => 'Test message',
	// 			'channel' => 'wpgraphql',
	// 			'datetime' => '2023-01-01 12:00:00',
	// 			'context' => '{}',
	// 			'extra' => '{}',
	// 			'query' => 'test query'
	// 		]
	// 	);
	// 	$log_id = $wpdb->insert_id;

	// 	// Add filter for filename
	// 	add_filter('wpgraphql_logging_csv_filename', function($filename) {
	// 		return 'custom_log_export.csv';
	// 	});

	// 	// Check headers contain custom filename
	// 	ob_start();
	// 	$this->service->generate_csv($log_id);
	// 	$output = ob_get_contents();
	// 	ob_end_clean();

	// 	$headers = headers_list();
	// 	$content_disposition_found = false;
	// 	foreach ($headers as $header) {
	// 		if (strpos($header, 'Content-Disposition') !== false && strpos($header, 'custom_log_export.csv') !== false) {
	// 			$content_disposition_found = true;
	// 			break;
	// 		}
	// 	}

	// 	$this->assertTrue($content_disposition_found);
	// }

	// public function test_csv_headers_filter(): void {
	// 	// Set admin user
	// 	$admin_user = $this->factory->user->create(['role' => 'administrator']);
	// 	wp_set_current_user($admin_user);
	// 	set_current_screen('dashboard');

	// 	// Create test log entry
	// 	global $wpdb;
	// 	$wpdb->insert(
	// 		$wpdb->prefix . 'wpgraphql_logs',
	// 		[
	// 			'level' => 200,
	// 			'level_name' => 'INFO',
	// 			'message' => 'Test message',
	// 			'channel' => 'wpgraphql',
	// 			'datetime' => '2023-01-01 12:00:00',
	// 			'context' => '{}',
	// 			'extra' => '{}',
	// 			'query' => 'test query'
	// 		]
	// 	);
	// 	$log_id = $wpdb->insert_id;

	// 	// Add filter for headers
	// 	add_filter('wpgraphql_logging_csv_headers', function($headers) {
	// 		return array_merge($headers, ['Custom Field']);
	// 	});

	// 	// Add filter for content
	// 	add_filter('wpgraphql_logging_csv_content', function($content) {
	// 		return array_merge($content, ['Custom Value']);
	// 	});

	// 	ob_start();
	// 	$this->service->generate_csv($log_id);
	// 	$output = ob_get_contents();
	// 	ob_end_clean();

	// 	// Parse CSV output
	// 	$lines = explode("\n", trim($output));
	// 	$headers = str_getcsv($lines[0]);
	// 	$content = str_getcsv($lines[1]);

	// 	// Verify custom header and content
	// 	$this->assertContains('Custom Field', $headers);
	// 	$this->assertContains('Custom Value', $content);
	// }
}
