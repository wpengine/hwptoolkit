<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Tests\Events;

use lucatume\WPBrowser\TestCase\WPTestCase;
use WPGraphQL\Logging\Events\QueryEventLifecycle;
use \WPGraphQL\Logging\Events\Events;

/**
 * Class QueryEventLifecycleTest
 *
 * @package WPGraphQL\Logging\Tests\Events
 *
 * @since 0.0.1
 */
class QueryEventLifecycleTest extends WPTestCase {

	public function test_init_creates_singleton_instance(): void {

		$reflection = new \ReflectionClass(QueryEventLifecycle::class);
		$instanceProperty = $reflection->getProperty('instance');
		$instanceProperty->setAccessible(true);
		$instanceProperty->setValue(null, null);

		$instance1 = QueryEventLifecycle::init();
		$instance2 = QueryEventLifecycle::init();
		$this->assertSame($instance1, $instance2);
		$this->assertInstanceOf(QueryEventLifecycle::class, $instance1);
		$this->assertInstanceOf(QueryEventLifecycle::class, $instance2);


		// Check that hooks are registered
		$actionLoggerProp = $reflection->getProperty('action_logger');
		$actionLogger = $actionLoggerProp->getValue($instance1);
		$this->assertTrue(has_action(Events::PRE_REQUEST, [ $actionLogger, 'log_pre_request' ]) !== false);
		$this->assertTrue(has_action(Events::BEFORE_GRAPHQL_EXECUTION, [$actionLogger, 'log_graphql_before_execute']) !== false);
		$this->assertTrue(has_action(Events::BEFORE_RESPONSE_RETURNED, [$actionLoggerProp->getValue($instance1), 'log_before_response_returned']) !== false);


		$filterLoggerProp = $reflection->getProperty('filter_logger');
		$filterLogger = $filterLoggerProp->getValue($instance1);
		$this->assertTrue(has_filter(Events::REQUEST_DATA, [$filterLogger, 'log_graphql_request_data']) !== false);
		$this->assertTrue(has_filter(Events::REQUEST_RESULTS, [$filterLogger, 'log_graphql_request_results']) !== false);
		$this->assertTrue(has_filter(Events::RESPONSE_HEADERS_TO_SEND, [$filterLogger, 'add_logging_headers']) !== false);
	}
}
