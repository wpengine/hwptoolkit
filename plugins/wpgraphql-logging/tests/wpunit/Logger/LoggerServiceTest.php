<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Tests\Logger;

use lucatume\WPBrowser\TestCase\WPTestCase;
use Monolog\Handler\TestHandler;
use Monolog\Processor\ProcessorInterface;
use ReflectionClass;
use WPGraphQL\Logging\Logger\LoggerService;
use Monolog\LogRecord;


/**
 * Test for the LoggerService
 *
 * @package WPGraphQL\Logging
 * @since 0.0.1
 */
class LoggerServiceTest extends WPTestCase
{

    public function tearDown(): void
    {
        parent::tearDown();
        $this->reset_logger_instance();
    }

	private function reset_logger_instance(): void
    {
		$reflection = new ReflectionClass(LoggerService::class);
		$instance_prop = $reflection->getProperty('instances');
		$instance_prop->setAccessible(true);
		$instance_prop->setValue(null, []);
    }

	private function get_monolog_instance(LoggerService $service): \Monolog\Logger
    {
        $reflection = new ReflectionClass($service);
        $monolog_prop = $reflection->getProperty('monolog');
        $monolog_prop->setAccessible(true);
        return $monolog_prop->getValue($service);
    }

    public function test_get_instance_returns_singleton(): void
    {
        $instance1 = LoggerService::get_instance();
        $instance2 = LoggerService::get_instance();

        $this->assertSame($instance1, $instance2, 'get_instance() should always return the same object.');

		$instance3 = LoggerService::get_instance('custom_channel');
		$this->assertInstanceOf(LoggerService::class, $instance3, 'Instance should be of type LoggerService.');
		$this->assertNotSame($instance1, $instance3, 'get_instance() should return different instances for different channels.');
		$this->assertEquals('custom_channel', $instance3->channel);

		$monolog = $this->get_monolog_instance($instance3);
		$this->assertCount(count(LoggerService::get_default_handlers()), $monolog->getHandlers(), 'Should have the default number of handlers.');
		$this->assertCount(count(LoggerService::get_default_processors()), $monolog->getProcessors(), 'Should have the default number of processors.');
    }

	public function test_get_instance_with_custom_configuration(): void
	{
		$custom_handler = new TestHandler();
		$custom_processor = new class implements ProcessorInterface {
			public function __invoke(LogRecord $record): LogRecord
			{
				$record = $record->with(extra: array_merge($record->extra, ['custom' => true]));
				return $record;
			}
		};

		$logger_service = LoggerService::get_instance(
			'custom_channel',
			[$custom_handler],
			[$custom_processor]
		);

		$monolog = $this->get_monolog_instance($logger_service);

		$this->assertEquals('custom_channel', $monolog->getName());
		$this->assertCount(1, $monolog->getHandlers());
		$this->assertSame($custom_handler, $monolog->getHandlers()[0]);
		$this->assertCount(1, $monolog->getProcessors());
	}

	/**
	 * Provides log levels and corresponding LoggerService methods.
	 */
	public function logLevelProvider(): array
	{
		return [
			['debug',    'debug'],
			['info',     'info'],
			['notice',   'notice'],
			['warning',  'warning'],
			['error',    'error'],
			['critical', 'critical'],
			['alert',    'alert'],
			['emergency','emergency'],
		];
	}

	/**
	 * @dataProvider logLevelProvider
	 */
	public function test_logging_methods_write_to_handler(string $level, string $method): void
	{
		$test_handler = new TestHandler();
		$logger_service = LoggerService::get_instance(
			'log_test_channel_' . $level,
			[$test_handler],
			[],
			['default_key' => 'default_value']
		);

		$message = "Test $level message";
		$context = ['foo' => 'bar'];

		$logger_service->$method($message, $context);

		$checkMethod = 'has' . ucfirst($level) . 'Records';
		$this->assertTrue($test_handler->$checkMethod(), "Handler should have $level record.");

		$records = $test_handler->getRecords();
		$this->assertNotEmpty($records, 'Handler should have at least one record.');
		$record = $records[0];

		$this->assertEquals($message, $record['message']);
		$this->assertArrayHasKey('default_key', $record['context']);
		$this->assertEquals('default_value', $record['context']['default_key']);
		$this->assertArrayHasKey('foo', $record['context']);
		$this->assertEquals('bar', $record['context']['foo']);
	}

	public function test_log_method_accepts_arbitrary_level(): void
	{
		$test_handler = new TestHandler();
		$logger_service = LoggerService::get_instance(
			'arbitrary_level',
			[$test_handler],
			[],
			['default_key' => 'default_value']
		);

		$logger_service->log('notice', 'Arbitrary level message', ['baz' => 'qux']);

		$this->assertTrue($test_handler->hasNoticeRecords());
		$records = $test_handler->getRecords();
		$this->assertEquals('Arbitrary level message', $records[0]['message']);
		$this->assertEquals('default_value', $records[0]['context']['default_key']);
		$this->assertEquals('qux', $records[0]['context']['baz']);
	}
}
