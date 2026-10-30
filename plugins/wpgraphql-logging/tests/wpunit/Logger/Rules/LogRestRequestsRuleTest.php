<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Tests\Logging\Rules;

use lucatume\WPBrowser\TestCase\WPTestCase;
use WPGraphQL\Logging\Admin\Settings\Fields\Tab\BasicConfigurationTab;
use WPGraphQL\Logging\Logger\Rules\LogRestRequestsRule;

/**
 * Test cases for the LogRestRequestsRule
 *
 * @package WPGraphQL\Logging
 *
 * @since 1.1.0
 */
class LogRestRequestsRuleTest extends WPTestCase {

	private LogRestRequestsRule $rule;

	public function setUp(): void {
		parent::setUp();
		$this->rule = new LogRestRequestsRule();
	}

	public function test_passes_when_enabled(): void {
		$this->assertTrue( $this->rule->passes( [ BasicConfigurationTab::LOG_REST_REQUESTS => true ], '/wp/v2/posts' ) );
		$this->assertTrue( $this->rule->passes( [ BasicConfigurationTab::LOG_REST_REQUESTS => 1 ] ) );
	}

	public function test_fails_when_disabled_or_missing(): void {
		$this->assertFalse( $this->rule->passes( [ BasicConfigurationTab::LOG_REST_REQUESTS => false ] ) );
		$this->assertFalse( $this->rule->passes( [] ) );
	}

	public function test_get_name(): void {
		$this->assertSame( 'log_rest_requests_rule', $this->rule->get_name() );
	}
}
