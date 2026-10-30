<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Tests\Admin\View\Download;

use lucatume\WPBrowser\TestCase\WPTestCase;
use WPGraphQL\Logging\Admin\View\Download\DownloadLogService;
use WPGraphQL\Logging\Logger\Database\WordPressDatabaseEntity;
use WPGraphQL\Logging\Logger\Database\WordPressDatabaseLogService;
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
	private WordPressDatabaseLogService $log_service;

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
		$this->log_service = new WordPressDatabaseLogService();
		$this->service = new DownloadLogService($this->log_service);
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
		$entity = \Mockery::mock(WordPressDatabaseEntity::class);
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
		$this->log_service = \Mockery::mock(WordPressDatabaseLogService::class);
		$this->log_service->shouldReceive('find_entity_by_id')->with(123)->andReturn($entity);

		// Inject the mocked repository into the service
		$this->service = new DownloadLogService($this->log_service);

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
	}

	/**
	 * Creates a real log entry in the database and returns its ID.
	 */
	private function create_log(string $message): int {
		$entity = $this->log_service->create_log_entity(
			$this->fixture['channel'],
			$this->fixture['level'],
			$this->fixture['level_name'],
			$message,
			$this->fixture['context'],
			$this->fixture['extra']
		);
		$this->assertNotNull($entity);
		return $entity->get_id();
	}

	/**
	 * Reads every row back out of a CSV stream.
	 *
	 * @param resource $stream
	 */
	private function read_csv($stream): array {
		rewind($stream);
		$rows = [];
		while (false !== ($row = fgetcsv($stream, null, ',', '"', '\\'))) {
			$rows[] = $row;
		}
		return $rows;
	}

	public function test_generate_bulk_csv_requires_admin_capabilities(): void {
		wp_set_current_user(0);

		$this->expectException(\WPDieException::class);
		$this->expectExceptionMessage('You do not have sufficient permissions to access this page.');

		$this->service->generate_bulk_csv([1, 2]);
	}

	public function test_generate_bulk_csv_requires_valid_ids(): void {
		$this->set_as_admin();

		$this->expectException(\WPDieException::class);
		$this->expectExceptionMessage('No logs selected.');

		$this->service->generate_bulk_csv([0, 'abc', null]);
	}

	public function test_generate_bulk_csv_dies_when_no_logs_found(): void {
		$this->set_as_admin();

		$this->expectException(\WPDieException::class);
		$this->expectExceptionMessage('Logs not found.');

		$this->service->generate_bulk_csv([9999998, 9999999]);
	}

	public function test_sanitize_log_ids_removes_invalid_and_duplicate_ids(): void {
		$this->assertSame([3, 7, 12], $this->service->sanitize_log_ids(['3', 7, 0, 'abc', 3, '12', null]));
	}

	public function test_sanitize_log_ids_drops_non_scalar_values(): void {
		// absint() would turn a non-empty array into 1.
		$this->assertSame([6], $this->service->sanitize_log_ids([['5'], '6', [1, 2], new \stdClass()]));
	}

	public function test_sanitize_log_ids_caps_at_bulk_download_limit(): void {
		$this->assertSame(DownloadLogService::DEFAULT_BULK_DOWNLOAD_LIMIT, $this->service->get_bulk_download_limit());

		$limit_filter = static fn() => 2;
		add_filter('wpgraphql_logging_bulk_download_limit', $limit_filter);

		$this->assertSame(2, $this->service->get_bulk_download_limit());
		$this->assertSame([5, 6], $this->service->sanitize_log_ids([5, 6, 7, 8]));

		remove_filter('wpgraphql_logging_bulk_download_limit', $limit_filter);
	}

	public function test_get_bulk_download_limit_is_at_least_one(): void {
		$limit_filter = static fn() => 0;
		add_filter('wpgraphql_logging_bulk_download_limit', $limit_filter);

		$this->assertSame(1, $this->service->get_bulk_download_limit());

		remove_filter('wpgraphql_logging_bulk_download_limit', $limit_filter);
	}

	public function test_write_csv_writes_one_header_row_and_one_row_per_log(): void {
		$first_id  = $this->create_log('First bulk log');
		$second_id = $this->create_log('Second bulk log');

		$logs = [
			$this->log_service->find_entity_by_id($first_id),
			$this->log_service->find_entity_by_id($second_id),
		];

		$stream = fopen('php://temp', 'w+');
		$count  = $this->service->write_csv($stream, $logs);
		$rows   = $this->read_csv($stream);
		fclose($stream);

		$this->assertSame(2, $count);
		$this->assertCount(3, $rows);
		$this->assertSame('ID', $rows[0][0]);
		$this->assertSame((string) $first_id, $rows[1][0]);
		$this->assertSame('First bulk log', $rows[1][4]);
		$this->assertSame((string) $second_id, $rows[2][0]);
		$this->assertSame('Second bulk log', $rows[2][4]);
	}

	public function test_write_csv_escapes_spreadsheet_formulas(): void {
		$log_id = $this->create_log('=HYPERLINK("http://example.com","click")');

		$stream = fopen('php://temp', 'w+');
		$this->service->write_csv($stream, [$this->log_service->find_entity_by_id($log_id)]);
		$rows = $this->read_csv($stream);
		fclose($stream);

		$this->assertSame("'=HYPERLINK(\"http://example.com\",\"click\")", $rows[1][4]);
		// Values that are not formulas are left alone.
		$this->assertSame((string) $log_id, $rows[1][0]);
		$this->assertSame(wp_json_encode($this->fixture['context']), $rows[1][7]);
	}

	public function test_write_csv_with_no_logs_writes_nothing(): void {
		$stream = fopen('php://temp', 'w+');
		$count  = $this->service->write_csv($stream, []);
		$rows   = $this->read_csv($stream);
		fclose($stream);

		$this->assertSame(0, $count);
		$this->assertSame([], $rows);
	}

	public function test_get_logs_skips_missing_entries(): void {
		$first_id  = $this->create_log('Kept log one');
		$second_id = $this->create_log('Kept log two');

		$reflection = new \ReflectionClass($this->service);
		$method     = $reflection->getMethod('get_logs');
		$method->setAccessible(true);

		$logs = iterator_to_array($method->invoke($this->service, [$first_id, 9999999, $second_id]), false);

		$this->assertCount(2, $logs);
		$this->assertSame($first_id, $logs[0]->get_id());
		$this->assertSame($second_id, $logs[1]->get_id());
	}
}
