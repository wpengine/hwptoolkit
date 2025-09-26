<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Tests\Logging\Rules;

use lucatume\WPBrowser\TestCase\WPTestCase;
use WPGraphQL\Logging\Logger\Rules\QueryNullRule;

/**
 * Test cases for the QueryNullRule
 *
 * @package WPGraphQL\Logging
 *
 * @since 0.0.1
 */
class QueryNullRuleTest extends WPTestCase {

	private QueryNullRule $rule;

	public function setUp(): void {
		parent::setUp();
		$this->rule = new QueryNullRule();
	}

	/**
	 * Test that the rule passes with a valid query string.
	 */
	public function test_passes_with_valid_query_string(): void {
		$config = [];
		$query_string = 'query { posts { id title } }';

		$this->assertTrue($this->rule->passes($config, $query_string));
	}

	/**
	 * Test that the rule fails with null query string.
	 */
	public function test_fails_with_null_query_string(): void {
		$config = [];

		$this->assertFalse($this->rule->passes($config, null));
	}

	/**
	 * Test that the rule fails with empty string.
	 */
	public function test_fails_with_empty_string(): void {
		$config = [];
		$query_string = '';

		$this->assertFalse($this->rule->passes($config, $query_string));
	}

	/**
	 * Test that the rule fails with whitespace-only string.
	 */
	public function test_fails_with_whitespace_only_string(): void {
		$config = [];
		$query_string = '   ';

		$this->assertFalse($this->rule->passes($config, $query_string));
	}

	/**
	 * Test that the rule passes with string containing whitespace but also content.
	 */
	public function test_passes_with_padded_query_string(): void {
		$config = [];
		$query_string = '  query { posts { id } }  ';

		$this->assertTrue($this->rule->passes($config, $query_string));
	}

	/**
	 * Test that get_name returns the expected rule name.
	 */
	public function test_get_name_returns_correct_name(): void {
		$this->assertEquals('query_null_rule', $this->rule->get_name());
	}

	/**
	 * Test that config parameter doesn't affect the rule outcome.
	 */
	public function test_config_does_not_affect_outcome(): void {
		$config_empty = [];
		$config_with_data = ['some_key' => 'some_value'];
		$query_string = 'query { posts { id } }';

		$this->assertTrue($this->rule->passes($config_empty, $query_string));
		$this->assertTrue($this->rule->passes($config_with_data, $query_string));
	}
}
