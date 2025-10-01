<?php

namespace WPGraphQL\Logging\Tests\Logger\Processors;

use WPGraphQL\Logging\Logger\Processors\DataSanitizationProcessor;
use WPGraphQL\Logging\Admin\Settings\ConfigurationHelper;
use lucatume\WPBrowser\TestCase\WPTestCase;
use WPGraphQL\Logging\Admin\Settings\Fields\Tab\DataManagementTab;
use Monolog\LogRecord;
use Monolog\Level;
use DateTimeImmutable;

/**
 * Test cases for the DataSanitizationProcessor
 *
 * @package WPGraphQL\Logging
 *
 * @since 0.0.1
 */
class DataSanitizationProcessorTest extends WPTestCase {


	public function create_mock_processor(array $config) : DataSanitizationProcessor {
		$processor = new DataSanitizationProcessor();
		$reflection = new \ReflectionClass($processor);
		$configProperty = $reflection->getProperty('config');
		$configProperty->setAccessible(true);
		$configProperty->setValue($processor, $config);
		return $processor;
	}

	public function test_process_record_not_enabled(): void
	{
		$processor = $this->create_mock_processor(
			[
				DataManagementTab::DATA_SANITIZATION_ENABLED => false,
				DataManagementTab::DATA_SANITIZATION_METHOD => 'recommended',
			]
		);

		$record = new LogRecord(
			new DateTimeImmutable('now'),
			'wpgraphql_logging',
			Level::Info,
			'Test log message',
			['test_field' => 'test_value'],
			[]
		);
		$result = $processor->__invoke($record);
		$this->assertSame($record, $result);
	}


	public function test_process_record_empty_custom_rules_no_processing(): void {

		$processor = $this->create_mock_processor(
			[
				DataManagementTab::DATA_SANITIZATION_ENABLED => true,
				DataManagementTab::DATA_SANITIZATION_METHOD => 'custom',
				DataManagementTab::DATA_SANITIZATION_CUSTOM_FIELD_ANONYMIZE => '',
				DataManagementTab::DATA_SANITIZATION_CUSTOM_FIELD_REMOVE => '',
				DataManagementTab::DATA_SANITIZATION_CUSTOM_FIELD_TRUNCATE => ''
			]
		);

		$record = new LogRecord(
			new DateTimeImmutable('now'),
			'wpgraphql_logging',
			Level::Info,
			'Test log message',
			['test_field' => 'test_value'],
			[]
		);
		$result = $processor->__invoke($record);
		$this->assertSame($record, $result);
	}


	public function test_process_record_process_recommended_rules(): void
	{
		$processor = $this->create_mock_processor(
			[
				DataManagementTab::DATA_SANITIZATION_ENABLED => true,
				DataManagementTab::DATA_SANITIZATION_METHOD => 'recommended',
			]
		);

		$record = new LogRecord(
			new DateTimeImmutable('now'),
			'wpgraphql_logging',
			Level::Info,
			'Test log message',
			[
				'request' => [
					'app_context' => [
						'viewer' => [
							'data' => [
								'user_email' => 'sensitive_data',
							],
							'allcaps' => 'administrator',
							'cap_key' => 'sensitive_data',
							'caps' => 'sensitive_data',
							'safe_field' => 'safe_data'
						]
					]
				],
				'query' => 'query { posts { id } }'
			],
			[]
		);
		$result = $processor->__invoke($record);
		$this->assertNotSame($record, $result);

		$data = $result->toArray();

		$this->assertSame($data['context'], [
			'request' => [
				'app_context' => [
					'viewer' => [
						'safe_field' => 'safe_data'
					]
				]
			],
			'query' => 'query { posts { id } }'
		]);
	}


	public function test_process_record_process_custom_rules(): void
	{
		$processor = $this->create_mock_processor(
			[
				DataManagementTab::DATA_SANITIZATION_ENABLED => true,
				DataManagementTab::DATA_SANITIZATION_METHOD => 'custom',
				DataManagementTab::DATA_SANITIZATION_CUSTOM_FIELD_ANONYMIZE => 'request.app_context.viewer.user_email, request.app_context.viewer.display_name',
				DataManagementTab::DATA_SANITIZATION_CUSTOM_FIELD_REMOVE => 'request.app_context.viewer.allcaps, request.app_context.viewer.cap_key',
				DataManagementTab::DATA_SANITIZATION_CUSTOM_FIELD_TRUNCATE => 'request.app_context.viewer.caps'
			]
		);

		$record = new LogRecord(
			new DateTimeImmutable('now'),
			'wpgraphql_logging',
			Level::Info,
			'Test log message',
			[
				'request' => [
					'app_context' => [
						'viewer' => [
							'display_name' => 'Sensitive Name',
							'user_email' => 'sensitive_data',
							'allcaps' => 'administrator',
							'cap_key' => 'sensitive_data',
							'caps' => 'This is a really long string that should be truncated',
							'safe_field' => 'safe_data'
						]
					]
				],
				'query' => 'query { posts { id } }'
			],
			[]
		);
		$result = $processor->__invoke($record);
		$this->assertNotSame($record, $result);

		$data = $result->toArray();

		$this->assertSame($data['context'], [
			'request' => [
				'app_context' => [
					'viewer' => [
						'display_name' => '***',
						'user_email' => '***',
						'caps' => 'This is a really long string that should be tru...',
						'safe_field' => 'safe_data',
					]
				]
			],
			'query' => 'query { posts { id } }'
		]);
	}
}
