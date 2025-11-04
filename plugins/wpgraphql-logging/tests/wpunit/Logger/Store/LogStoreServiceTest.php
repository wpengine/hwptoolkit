<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Unit\Logger\Store;

use WPGraphQL\Logging\Logger\Database\WordPressDatabaseLogService;
use WPGraphQL\Logging\Logger\Store\LogStoreService;
use lucatume\WPBrowser\TestCase\WPTestCase;

require_once __DIR__ . '/CustomLogServiceStub.php';

class LogStoreServiceTest extends WPTestCase
{
    /**
     * Resets the singleton instance in LogStoreService using reflection.
     */
    protected function resetLogStoreService()
    {
        $reflection = new \ReflectionClass(LogStoreService::class);
        $instanceProperty = $reflection->getProperty('instance');
        $instanceProperty->setAccessible(true);
        $instanceProperty->setValue(null, null);
        $instanceProperty->setAccessible(false);
    }

    public function testGetLogServiceReturnsDefaultService()
    {
        $this->resetLogStoreService();
        remove_all_filters('wpgraphql_logging_log_store_service');

        $service = LogStoreService::get_log_service();
        $this->assertInstanceOf(WordPressDatabaseLogService::class, $service);
    }

    public function testGetLogServiceCanBeReplacedByFilter()
    {
        $this->resetLogStoreService();
        remove_all_filters('wpgraphql_logging_log_store_service');

        $customService = new CustomLogServiceStub();

        add_filter('wpgraphql_logging_log_store_service', function ($service) use ($customService) {
            return $customService;
        });

        $service = LogStoreService::get_log_service();

        $this->assertInstanceOf(CustomLogServiceStub::class, $service);
        $this->assertSame($customService, $service);

        // It should return the same instance on subsequent calls
        $service2 = LogStoreService::get_log_service();
        $this->assertSame($customService, $service2);

        remove_all_filters('wpgraphql_logging_log_store_service');
    }

    public function testFilterReturningNullUsesDefaultService()
    {
        $this->resetLogStoreService();
        remove_all_filters('wpgraphql_logging_log_store_service');

        add_filter('wpgraphql_logging_log_store_service', function ($service) {
            // A filter that does nothing or returns null
            return null;
        });

        $service = LogStoreService::get_log_service();
        $this->assertInstanceOf(WordPressDatabaseLogService::class, $service);

        remove_all_filters('wpgraphql_logging_log_store_service');
    }

    public function testFilterReturningInvalidObjectUsesDefaultService()
    {
        $this->resetLogStoreService();
        remove_all_filters('wpgraphql_logging_log_store_service');

        add_filter('wpgraphql_logging_log_store_service', function ($service) {
            // Return something that does not implement the interface
            return new \stdClass();
        });

        $service = LogStoreService::get_log_service();
        $this->assertInstanceOf(WordPressDatabaseLogService::class, $service);

        remove_all_filters('wpgraphql_logging_log_store_service');
    }
}
