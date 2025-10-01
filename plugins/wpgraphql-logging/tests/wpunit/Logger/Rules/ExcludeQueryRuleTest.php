<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Tests\Logging\Rules;

use lucatume\WPBrowser\TestCase\WPTestCase;
use WPGraphQL\Logging\Admin\Settings\Fields\Tab\BasicConfigurationTab;
use WPGraphQL\Logging\Logger\Rules\ExcludeQueryRule;


/**
 * Test cases for the ExcludeQueryRule
 *
 * @package WPGraphQL\Logging
 *
 * @since 0.0.1
 */
class ExcludeQueryRuleTest extends WPTestCase {

	private ExcludeQueryRule $rule;

	public function setUp(): void {
		parent::setUp();
		$this->rule = new ExcludeQueryRule();
	}

	public function test_passes_when_no_excluded_queries_configured(): void {
		$config = [];
		$query_string = 'query { posts { nodes { id title } } }';

		$this->assertTrue($this->rule->passes($config, $query_string));
	}

	public function test_passes_when_excluded_queries_is_empty_string(): void {
		$config = [BasicConfigurationTab::EXCLUDE_QUERY => ''];
		$query_string = 'query { posts { nodes { id title } } }';

		$this->assertTrue($this->rule->passes($config, $query_string));
	}

	public function test_passes_when_query_string_is_null(): void {
		$config = [BasicConfigurationTab::EXCLUDE_QUERY => 'introspection'];

		$this->assertTrue($this->rule->passes($config, null));
	}

	public function test_fails_when_query_contains_excluded_term(): void {
		$config = [BasicConfigurationTab::EXCLUDE_QUERY => 'introspection'];
		$query_string = 'query IntrospectionQuery { __schema { types { name } } }';

		$this->assertFalse($this->rule->passes($config, $query_string));
	}

	public function test_fails_with_case_insensitive_matching(): void {
		$config = [BasicConfigurationTab::EXCLUDE_QUERY => 'INTROSPECTION'];
		$query_string = 'query introspectionQuery { __schema { types { name } } }';

		$this->assertFalse($this->rule->passes($config, $query_string));
	}

	public function test_handles_multiple_excluded_queries(): void {
		$config = [BasicConfigurationTab::EXCLUDE_QUERY => 'introspection, __schema, GetSeedNode'];

		$this->assertFalse($this->rule->passes($config, 'query { __schema { types } }'));
		$this->assertTrue($this->rule->passes($config, 'query { posts { nodes { id } } }'));
		$this->assertFalse($this->rule->passes($config, 'query GetSeedNode { node(id: "1") { id } }'));
	}

	public function test_passes_when_query_does_not_match_excluded_terms(): void {
		$config = [BasicConfigurationTab::EXCLUDE_QUERY => 'introspection, debug'];
		$query_string = 'query { posts { nodes { id title content } } }';

		$this->assertTrue($this->rule->passes($config, $query_string));
	}

	public function test_get_name_returns_correct_name(): void {
		$this->assertEquals('exclude_query_rule', $this->rule->get_name());
	}
}
