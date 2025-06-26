<?php

declare( strict_types=1 );

namespace HWP\Previews\Tests\Unit\Preview\Template;

use HWP\Previews\Preview\Template\Template_Resolver_Service;
use lucatume\WPBrowser\TestCase\WPTestCase;

class Template_Resolver_Service_Test extends WPTestCase {


	/**
	 * Clean up after each test.
	 */
	public function tearDown(): void {
		// Clean up query vars
		set_query_var( Template_Resolver_Service::HWP_PREVIEWS_IFRAME_PREVIEW_URL, '' );

		// Remove any filters that might have been added
		remove_all_filters( 'hwp_previews_template_path' );

		parent::tearDown();
	}

	/**
	 * Test get_iframe_template returns template path when file exists.
	 */
	public function test_get_iframe_template_returns_path_when_file_exists(): void {
		$resolver = new Template_Resolver_Service();

		// Mock the filter to return a path to an existing file
		$existing_file = tempnam( sys_get_temp_dir(), 'iframe_template' );
		file_put_contents( $existing_file, '<?php // Test template' );

		add_filter( 'hwp_previews_template_path', function () use ( $existing_file ) {
			return $existing_file;
		} );

		$result = $resolver->get_iframe_template();

		$this->assertEquals( $existing_file, $result );

		// Cleanup
		unlink( $existing_file );
		remove_all_filters( 'hwp_previews_template_path' );
	}

	/**
	 * Test get_iframe_template returns empty string when file does not exist.
	 */
	public function test_get_iframe_template_returns_empty_string_when_file_not_exists(): void {
		$resolver = new Template_Resolver_Service();

		// Mock the filter to return a non-existent file path
		$non_existent_file = '/path/that/does/not/exist/iframe.php';

		add_filter( 'hwp_previews_template_path', function () use ( $non_existent_file ) {
			return $non_existent_file;
		} );

		$result = $resolver->get_iframe_template();

		$this->assertEquals( '', $result );

		// Cleanup
		remove_all_filters( 'hwp_previews_template_path' );
	}

	/**
	 * Test get_iframe_template applies the hwp_previews_template_path filter.
	 */
	public function test_get_iframe_template_applies_filter(): void {
		$resolver = new Template_Resolver_Service();

		$custom_path   = '/custom/template/path.php';
		$filter_called = false;

		add_filter( 'hwp_previews_template_path', function ( $path ) use ( $custom_path, &$filter_called ) {
			$filter_called = true;

			return $custom_path;
		} );

		// Call the method (will return empty string since file doesn't exist)
		$resolver->get_iframe_template();

		$this->assertTrue( $filter_called );

		// Cleanup
		remove_all_filters( 'hwp_previews_template_path' );
	}

	/**
	 * Test set_query_variable sets the query variable correctly.
	 */
	public function test_set_query_variable_sets_query_var(): void {
		$resolver = new Template_Resolver_Service();
		$test_url = 'https://example.com/preview';

		$resolver->set_query_variable( $test_url );

		$this->assertEquals( $test_url, get_query_var( Template_Resolver_Service::HWP_PREVIEWS_IFRAME_PREVIEW_URL ) );
	}

	/**
	 * Test set_query_variable with empty string.
	 */
	public function test_set_query_variable_with_empty_string(): void {
		$resolver = new Template_Resolver_Service();

		$resolver->set_query_variable( '' );

		$this->assertEquals( '', get_query_var( Template_Resolver_Service::HWP_PREVIEWS_IFRAME_PREVIEW_URL ) );
	}

	/**
	 * Test get_query_variable returns the correct value.
	 */
	public function test_get_query_variable_returns_correct_value(): void {
		$test_url = 'https://example.com/preview';

		set_query_var( Template_Resolver_Service::HWP_PREVIEWS_IFRAME_PREVIEW_URL, $test_url );

		$result = Template_Resolver_Service::get_query_variable();

		$this->assertEquals( $test_url, $result );
	}

	/**
	 * Test get_query_variable returns empty string when not set.
	 */
	public function test_get_query_variable_returns_empty_string_when_not_set(): void {
		// Ensure the query var is not set
		set_query_var( Template_Resolver_Service::HWP_PREVIEWS_IFRAME_PREVIEW_URL, '' );

		$result = Template_Resolver_Service::get_query_variable();

		$this->assertEquals( '', $result );
	}

	/**
	 * Test get_query_variable is static and works without instance.
	 */
	public function test_get_query_variable_is_static(): void {
		$test_url = 'https://example.com/static-test';

		set_query_var( Template_Resolver_Service::HWP_PREVIEWS_IFRAME_PREVIEW_URL, $test_url );

		// Call static method without creating an instance
		$result = Template_Resolver_Service::get_query_variable();

		$this->assertEquals( $test_url, $result );
	}

	/**
	 * Test constant is defined correctly.
	 */
	public function test_constant_is_defined_correctly(): void {
		$this->assertEquals( 'hwp_previews_iframe_preview_url', Template_Resolver_Service::HWP_PREVIEWS_IFRAME_PREVIEW_URL );
	}

	/**
	 * Test integration: set and get query variable using the same constant.
	 */
	public function test_set_and_get_query_variable_integration(): void {
		$resolver = new Template_Resolver_Service();
		$test_url = 'https://example.com/integration-test';

		$resolver->set_query_variable( $test_url );
		$retrieved_url = Template_Resolver_Service::get_query_variable();

		$this->assertEquals( $test_url, $retrieved_url );
	}
}
