<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Tests\Logging\Rules;

use lucatume\WPBrowser\TestCase\WPTestCase;
use WPGraphQL\Logging\Admin\Settings\Fields\Tab\BasicConfigurationTab;
use WPGraphQL\Logging\Logger\Rules\ExcludeRestRouteRule;

/**
 * Test cases for the ExcludeRestRouteRule
 *
 * @package WPGraphQL\Logging
 *
 * @since 1.1.0
 */
class ExcludeRestRouteRuleTest extends WPTestCase {

	private ExcludeRestRouteRule $rule;

	public function setUp(): void {
		parent::setUp();
		$this->rule = new ExcludeRestRouteRule();
	}

	public function test_passes_when_no_routes_configured(): void {
		$this->assertTrue( $this->rule->passes( [], '/wp/v2/posts' ) );
		$this->assertTrue( $this->rule->passes( [ BasicConfigurationTab::EXCLUDE_REST_ROUTES => '  ' ], '/wp/v2/posts' ) );
	}

	public function test_passes_when_route_is_null(): void {
		$this->assertTrue( $this->rule->passes( [ BasicConfigurationTab::EXCLUDE_REST_ROUTES => '/wp/v2/posts' ], null ) );
	}

	public function test_fails_for_exact_and_prefixed_routes(): void {
		$config = [ BasicConfigurationTab::EXCLUDE_REST_ROUTES => '/wp/v2/users' ];

		$this->assertFalse( $this->rule->passes( $config, '/wp/v2/users' ) );
		$this->assertFalse( $this->rule->passes( $config, '/wp/v2/users/me' ) );
		$this->assertTrue( $this->rule->passes( $config, '/wp/v2/posts' ) );
	}

	public function test_handles_whitespace_empty_entries_and_missing_slashes(): void {
		$config = [ BasicConfigurationTab::EXCLUDE_REST_ROUTES => ' , wp/v2/types ,  /wp-site-health/v1 ,' ];

		$this->assertFalse( $this->rule->passes( $config, '/wp/v2/types/post' ) );
		$this->assertFalse( $this->rule->passes( $config, 'wp-site-health/v1/tests/background-updates' ) );
		$this->assertTrue( $this->rule->passes( $config, '/wp/v2/posts' ) );
	}

	public function test_does_not_match_sibling_routes_sharing_a_prefix(): void {
		$config = [ BasicConfigurationTab::EXCLUDE_REST_ROUTES => '/wp/v2/users/' ];

		$this->assertFalse( $this->rule->passes( $config, '/wp/v2/users' ) );
		$this->assertTrue( $this->rule->passes( $config, '/wp/v2/users-extra' ) );
	}

	public function test_matching_is_case_insensitive(): void {
		$config = [ BasicConfigurationTab::EXCLUDE_REST_ROUTES => '/wp/v2/Users' ];

		$this->assertFalse( $this->rule->passes( $config, '/WP/V2/USERS/1' ) );
	}

	public function test_get_name(): void {
		$this->assertSame( 'exclude_rest_route_rule', $this->rule->get_name() );
	}
}
