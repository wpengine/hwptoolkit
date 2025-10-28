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
	private WordPressDatabaseLogService $repository;

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
		$this->repository = new WordPressDatabaseLogService();
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
		$this->repository = \Mockery::mock(WordPressDatabaseLogService::class);
		$this->repository->shouldReceive('find_entity_by_id')->with(123)->andReturn($entity);

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
	}

}
