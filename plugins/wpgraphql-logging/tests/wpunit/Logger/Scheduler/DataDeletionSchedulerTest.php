<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Tests\Logger\Scheduler;

use WPGraphQL\Logging\Admin\Settings\ConfigurationHelper;
use WPGraphQL\Logging\Admin\Settings\Fields\Tab\DataManagementTab;
use WPGraphQL\Logging\Logger\Database\LogsRepository;
use WPGraphQL\Logging\Logger\Scheduler\DataDeletionScheduler;
use WPGraphQL\Logging\Logger\Database\DatabaseEntity;
use Monolog\Level;
use lucatume\WPBrowser\TestCase\WPTestCase;
use Mockery;


class DataDeletionSchedulerTest extends WPTestCase {


	protected $initial_log_count = 0;

	protected LogsRepository $repository;

	protected function setUp(): void {
		parent::setUp();
		$this->repository = new LogsRepository();
		$this->generate_logs();
		$this->initial_log_count = $this->get_total_log_count();
		// wp_clear_scheduled_hook(DataDeletionScheduler::DELETION_HOOK);
	}

	protected function tearDown(): void {
		$this->delete_logs();
		// wp_clear_scheduled_hook(DataDeletionScheduler::DELETION_HOOK);
		parent::tearDown();
	}


	public function generate_logs() : void {

		global $wpdb;
		$table_name = DatabaseEntity::get_table_name();
		$repository = new LogsRepository();
		$now = new \DateTime();
		for ($i = 0; $i < 10; $i++) {
			$entity = DatabaseEntity::create(
				'wpgraphql_logging',
				200,
				'info',
				'Test log ' . $i,
				['query' => 'query { posts { id title } }'],
				[]
			);


			$id = $entity->save();

			// Manually set the datetime to simulate old logs
			$log_date = (clone $now)->modify("-" . $i . " days");
			$wpdb->update(
				$table_name,
				['datetime' => $log_date->format('Y-m-d H:i:s')],
				['id' => $id],
				['%s'],
				['%d']
			);
		}
	}

	public function delete_logs() : void {
		$this->repository->delete_all();
	}

	public function get_total_log_count() : int {
		return $this->repository->get_log_count([]);
	}

	public function test_init_creates_singleton_instance(): void {
		$reflection = new \ReflectionClass(DataDeletionScheduler::class);
		$instanceProperty = $reflection->getProperty('instance');
		$instanceProperty->setAccessible(true);
		$instanceProperty->setValue(null, null);

		$instance1 = DataDeletionScheduler::init();
		$instance2 = DataDeletionScheduler::init();
		$this->assertSame($instance1, $instance2);
		$this->assertInstanceOf(DataDeletionScheduler::class, $instance1);
		$this->assertInstanceOf(DataDeletionScheduler::class, $instance2);
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

		$expected_count = $this->repository->get_log_count(['datetime >= NOW() - INTERVAL 3 DAY']);
		// Delete logs
		$scheduler->perform_deletion();
		$total_count = $this->get_total_log_count();
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
