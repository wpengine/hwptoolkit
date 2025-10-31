<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Tests\Logger\Scheduler;

use WPGraphQL\Logging\Admin\Settings\ConfigurationHelper;
use WPGraphQL\Logging\Admin\Settings\Fields\Tab\DataManagementTab;
use WPGraphQL\Logging\Logger\Scheduler\DataDeletionScheduler;
use Monolog\Level;
use lucatume\WPBrowser\TestCase\WPTestCase;
use Mockery;
use WPGraphQL\Logging\Logger\Database\WordPressDatabaseEntity;
use WPGraphQL\Logging\Logger\Api\LogServiceInterface;
use WPGraphQL\Logging\Logger\Store\LogStoreService;
use WPGraphQL\Logging\Plugin;

class DataDeletionSchedulerTest extends WPTestCase {


	protected $initial_log_count = 0;

	private LogServiceInterface $log_service;

	protected function setUp(): void {
		parent::setUp();
		Plugin::activate();
		$this->log_service = LogStoreService::get_log_service();
		$this->generate_logs();
		$this->initial_log_count = $this->get_total_log_count();
	}

	protected function tearDown(): void {
		$this->delete_logs();
		parent::tearDown();
	}

	public function generate_logs() : void {

		global $wpdb;
		$now = new \DateTime();
		$table_name = WordPressDatabaseEntity::get_table_name();
		for ($i = 0; $i < 10; $i++) {
			$entity = $this->log_service->create_log_entity(
				'wpgraphql_logging',
				200,
				'info',
				'Test log ' . $i,
				['query' => 'query { posts { id title } }'],
				[]
			);

			// Manually set the datetime to simulate old logs
			$log_date = (clone $now)->modify("-" . $i . " days");
			$wpdb->update(
				$table_name,
				['datetime' => $log_date->format('Y-m-d H:i:s')],
				['id' => $entity->get_id()],
				['%s'],
				['%d']
			);
		}
	}

	public function delete_logs() : void {
		$this->log_service->delete_all_entities();
	}

	public function get_total_log_count() : int {
		return $this->log_service->count_entities_by_where([]);
	}


	public function create_mock_scheduler(array $config) : DataDeletionScheduler {
		$scheduler = DataDeletionScheduler::init();
		$reflection = new \ReflectionClass($scheduler);
		$configProperty = $reflection->getProperty('config');
		$configProperty->setAccessible(true);
		$configProperty->setValue($scheduler, $config);
		return $scheduler;
	}


	public function test_data_deletion_scheduler_no_deletion_when_disabled(): void {

		$scheduler = $this->create_mock_scheduler(
			[
				DataManagementTab::DATA_DELETION_ENABLED => false,
				DataManagementTab::DATA_RETENTION_DAYS => 30
			]
		);

		$scheduler->perform_deletion();
		$this->assertEquals($this->initial_log_count, $this->get_total_log_count());
	}

	public function test_data_deletion_scheduler_no_deletion_invalid_retention_days(): void {

		$scheduler = $this->create_mock_scheduler(
			[
				DataManagementTab::DATA_DELETION_ENABLED => true,
				DataManagementTab::DATA_RETENTION_DAYS => 'invalid_integer'
			]
		);

		$scheduler->perform_deletion();
		$this->assertEquals($this->initial_log_count, $this->get_total_log_count());
	}

	public function test_data_deletion_scheduler_no_deletion_zero_retention_days(): void {

		$scheduler = $this->create_mock_scheduler(
			[
				DataManagementTab::DATA_DELETION_ENABLED => true,
				DataManagementTab::DATA_RETENTION_DAYS => 0
			]
		);

		$scheduler->perform_deletion();
		$this->assertEquals($this->initial_log_count, $this->get_total_log_count());
	}

	public function test_data_deletion_scheduler_valid_config(): void {

		$scheduler = $this->create_mock_scheduler(
			[
				DataManagementTab::DATA_DELETION_ENABLED => true,
				DataManagementTab::DATA_RETENTION_DAYS => 3
			]
		);

		// datetime >= NOW() - INTERVAL 3 DAY
		$args = [
			[
				'column' => 'datetime',
				'operator' => '>=',
				'value' => date('Y-m-d H:i:s', strtotime('-3 day')),
			],
		];
		$expected_count = $this->log_service->count_entities_by_where($args);
		$this->assertEquals(4, $expected_count);

		// Delete logs
		$scheduler->perform_deletion();
		$total_count = $this->log_service->count_entities_by_where([]);
		$this->assertLessThan($this->initial_log_count, $total_count);
		$this->assertGreaterThanOrEqual(0, $total_count);
		$this->assertEquals($expected_count, $total_count);
	}

	public function test_schedule_deletion_schedules_event(): void {
		wp_clear_scheduled_hook(DataDeletionScheduler::DELETION_HOOK);
		$scheduler = DataDeletionScheduler::init();
		$this->assertFalse(wp_next_scheduled(DataDeletionScheduler::DELETION_HOOK));
		$scheduler->schedule_deletion();
		$this->assertNotFalse(wp_next_scheduled(DataDeletionScheduler::DELETION_HOOK));
	}
}
