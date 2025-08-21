<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Tests\Service;

use lucatume\WPBrowser\TestCase\WPTestCase;
use WPGraphQL\Logging\Service\Config_Settings_Service;
use WPGraphQL\Logging\Admin\Settings\Fields\Tab\Basic_Configuration_Tab;

class ConfigSettingsServiceTest extends WPTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        delete_option(WPGRAPHQL_LOGGING_SETTINGS_KEY);

		// Ensure the singleton is reset for each test so config is re-initialized.
		$reflection = new \ReflectionClass(Config_Settings_Service::class);
		if ($reflection->hasProperty('instance')) {
			$property = $reflection->getProperty('instance');
			$property->setAccessible(true);
			$property->setValue(null, null);
		}
    }

    public function test_singleton_returns_same_instance(): void
    {
        $first = Config_Settings_Service::init();
        $second = Config_Settings_Service::init();
        $this->assertSame($first, $second);
    }

    public function test_get_config_helpers_with_defaults(): void
    {
        $service = Config_Settings_Service::init();

        $this->assertFalse($service->is_enabled());
        $this->assertFalse($service->is_admin_restricted());
        $this->assertFalse($service->is_ip_restricted());
        $this->assertFalse($service->has_query_restrictions());
        $this->assertFalse($service->has_performance_metric());
        $this->assertTrue($service->is_data_sampling_enabled());
        $this->assertSame($service::DEFAULT_DATA_SAMPLING_PERCENTAGE, $service->get_data_sampling_percentage()); // 25
        $this->assertSame(0.25, $service->get_performance_metric_threshold());
        $this->assertSame([], $service->get_ip_restrictions());
        $this->assertSame([], $service->get_query_restrictions());
		$this->assertSame([], $service->get_config_values());
    }

	/**
	 * Test the IP restrictions are sanitized and validated.
	 */
    public function test_ip_restrictions_sanitization_and_validation(): void {

        $raw_ips = ' 127.0.0.1 , <b>invalid</b> , 2001:0db8:85a3:0000:0000:8a2e:0370:7334 , 10.0.0.1 , not-an-ip , ';
        $options = [
            Basic_Configuration_Tab::get_name() => [
                Basic_Configuration_Tab::IP_RESTRICTIONS => $raw_ips,
            ],
        ];
        update_option(WPGRAPHQL_LOGGING_SETTINGS_KEY, $options);

        $service = Config_Settings_Service::init();

        $this->assertTrue($service->is_ip_restricted());
        $ips = $service->get_ip_restrictions();

        $this->assertSame(['127.0.0.1', '2001:0db8:85a3:0000:0000:8a2e:0370:7334', '10.0.0.1'], $ips);
    }

    public function test_query_restrictions_are_sanitized_and_split(): void
    {
        $raw = ' GetPost , <i>GetPosts</i> , introspection ';
        $options = [
            Basic_Configuration_Tab::get_name() => [
                Basic_Configuration_Tab::WPGRAPHQL_FILTERING => $raw,
            ],
        ];
        update_option(WPGRAPHQL_LOGGING_SETTINGS_KEY, $options);
        $service = Config_Settings_Service::init();

        $this->assertTrue($service->has_query_restrictions());
        $queries = $service->get_query_restrictions();
        $this->assertSame(['GetPost', 'GetPosts', 'introspection'], $queries);
    }

    public function test_performance_metric_threshold_parsing(): void
    {
        $options = [
            Basic_Configuration_Tab::get_name() => [
                Basic_Configuration_Tab::PERFORMANCE_METRICS => ' 1.5 ',
            ],
        ];
        update_option(WPGRAPHQL_LOGGING_SETTINGS_KEY, $options);
        $service = Config_Settings_Service::init();

        $this->assertTrue($service->has_performance_metric());
        $this->assertSame(1.5, $service->get_performance_metric_threshold());
    }

	public function test_performance_metric_threshold_parsing_no_value(): void
    {
        $service = Config_Settings_Service::init();

        $this->assertFalse($service->has_performance_metric());
        $this->assertSame($service::DEFAULT_PERFORMANCE_METRIC_THRESHOLD, $service->get_performance_metric_threshold());
    }

	public function test_data_sampling_percentage_parsing_no_value(): void
    {
        $service = Config_Settings_Service::init();

        $this->assertTrue($service->is_data_sampling_enabled());
        $this->assertSame($service::DEFAULT_DATA_SAMPLING_PERCENTAGE, $service->get_data_sampling_percentage());
    }

}
