<?php

declare(strict_types=1);

namespace HWP\Previews\Tests\Unit\Preview;

use WP_Post;
use WP_UnitTestCase;
use HWP\Previews\Preview\Template_Resolver;
use WP_UnitTestCase_Base;

class Template_Resolver_Test extends \lucatume\WPBrowser\TestCase\WPTestCase {

    /**
     * Test post object for testing.
     *
     * @var WP_Post
     */
    private WP_Post $test_post;

    /**
     * Set up test fixtures.
     */
    public function setUp(): void {
        parent::setUp();

        // Create a test post
	    $post_id = $this->factory()->post->create([
			'post_title' => 'Test Post',
			'post_type' => 'post',
			'post_status' => 'publish'
		]);

        $this->test_post = get_post($post_id);
    }


	/**
	 * Clean up after each test.
	 */
	public function tearDown(): void {
		// Clean up query vars
		set_query_var(Template_Resolver::HWP_PREVIEWS_IFRAME_PREVIEW_URL, '');

		// Remove any filters that might have been added
		remove_all_filters('hwp_previews_template_path');

		parent::tearDown();
	}

    /**
     * Test constructor sets properties correctly.
     */
    public function test_constructor_sets_properties_correctly(): void {
        $post_types = ['post', 'page'];
        $post_statuses = ['publish', 'draft'];

        $resolver = new Template_Resolver($this->test_post, $post_types, $post_statuses);

        // Use reflection to access private properties for testing
        $reflection = new \ReflectionClass($resolver);

        $post_property = $reflection->getProperty('post');
        $post_property->setAccessible(true);
        $this->assertEquals($this->test_post, $post_property->getValue($resolver));

        $post_types_property = $reflection->getProperty('post_types');
        $post_types_property->setAccessible(true);
        $this->assertEquals($post_types, $post_types_property->getValue($resolver));

        $post_statuses_property = $reflection->getProperty('post_statuses');
        $post_statuses_property->setAccessible(true);
        $this->assertEquals($post_statuses, $post_statuses_property->getValue($resolver));
    }

    /**
     * Test constructor with default empty arrays.
     */
    public function test_constructor_with_default_arrays(): void {
        $resolver = new Template_Resolver($this->test_post);

        $reflection = new \ReflectionClass($resolver);

        $post_types_property = $reflection->getProperty('post_types');
        $post_types_property->setAccessible(true);
        $this->assertEquals([], $post_types_property->getValue($resolver));

        $post_statuses_property = $reflection->getProperty('post_statuses');
        $post_statuses_property->setAccessible(true);
        $this->assertEquals([], $post_statuses_property->getValue($resolver));
    }

    /**
     * Test is_allowed returns true when post type and status are allowed.
     */
    public function test_is_allowed_returns_true_when_post_is_allowed(): void {
        $post_types = ['post', 'page'];
        $post_statuses = ['publish', 'draft'];

        $resolver = new Template_Resolver($this->test_post, $post_types, $post_statuses);

        $this->assertTrue($resolver->is_allowed());
    }

    /**
     * Test is_allowed returns false when post type is not allowed.
     */
    public function test_is_allowed_returns_false_when_post_type_not_allowed(): void {
        $post_types = ['page', 'custom_post_type']; // 'post' not included
        $post_statuses = ['publish', 'draft'];

        $resolver = new Template_Resolver($this->test_post, $post_types, $post_statuses);

        $this->assertFalse($resolver->is_allowed());
    }

    /**
     * Test is_allowed returns false when post status is not allowed.
     */
    public function test_is_allowed_returns_false_when_post_status_not_allowed(): void {
        $post_types = ['post', 'page'];
        $post_statuses = ['draft', 'private']; // 'publish' not included

        $resolver = new Template_Resolver($this->test_post, $post_types, $post_statuses);

        $this->assertFalse($resolver->is_allowed());
    }

    /**
     * Test is_allowed returns false when both arrays are empty.
     */
    public function test_is_allowed_returns_false_when_arrays_are_empty(): void {
        $resolver = new Template_Resolver($this->test_post, [], []);

        $this->assertFalse($resolver->is_allowed());
    }

    /**
     * Test is_allowed with custom post type and status.
     */
    public function test_is_allowed_with_custom_post_type_and_status(): void {
        // Create a custom post with specific type and status
        $custom_post_id = $this->factory->post->create([
            'post_type' => 'custom_type',
            'post_status' => 'custom_status'
        ]);
        $custom_post = get_post($custom_post_id);

        $post_types = ['custom_type'];
        $post_statuses = ['custom_status'];

        $resolver = new Template_Resolver($custom_post, $post_types, $post_statuses);

        $this->assertTrue($resolver->is_allowed());
    }

    /**
     * Test get_iframe_template returns template path when file exists.
     */
    public function test_get_iframe_template_returns_path_when_file_exists(): void {
        $resolver = new Template_Resolver($this->test_post);

        // Mock the filter to return a path to an existing file
        $existing_file = tempnam(sys_get_temp_dir(), 'iframe_template');
        file_put_contents($existing_file, '<?php // Test template');

        add_filter('hwp_previews_template_path', function() use ($existing_file) {
            return $existing_file;
        });

        $result = $resolver->get_iframe_template();

        $this->assertEquals($existing_file, $result);

        // Cleanup
        unlink($existing_file);
        remove_all_filters('hwp_previews_template_path');
    }

    /**
     * Test get_iframe_template returns empty string when file does not exist.
     */
    public function test_get_iframe_template_returns_empty_string_when_file_not_exists(): void {
        $resolver = new Template_Resolver($this->test_post);

        // Mock the filter to return a non-existent file path
        $non_existent_file = '/path/that/does/not/exist/iframe.php';

        add_filter('hwp_previews_template_path', function() use ($non_existent_file) {
            return $non_existent_file;
        });

        $result = $resolver->get_iframe_template();

        $this->assertEquals('', $result);

        // Cleanup
        remove_all_filters('hwp_previews_template_path');
    }

    /**
     * Test get_iframe_template applies the hwp_previews_template_path filter.
     */
    public function test_get_iframe_template_applies_filter(): void {
        $resolver = new Template_Resolver($this->test_post);

        $custom_path = '/custom/template/path.php';
        $filter_called = false;

        add_filter('hwp_previews_template_path', function($path) use ($custom_path, &$filter_called) {
            $filter_called = true;
            return $custom_path;
        });

        // Call the method (will return empty string since file doesn't exist)
        $resolver->get_iframe_template();

        $this->assertTrue($filter_called);

        // Cleanup
        remove_all_filters('hwp_previews_template_path');
    }

    /**
     * Test set_query_variable sets the query variable correctly.
     */
    public function test_set_query_variable_sets_query_var(): void {
        $resolver = new Template_Resolver($this->test_post);
        $test_url = 'https://example.com/preview';

        $resolver->set_query_variable($test_url);

        $this->assertEquals($test_url, get_query_var(Template_Resolver::HWP_PREVIEWS_IFRAME_PREVIEW_URL));
    }

    /**
     * Test set_query_variable with empty string.
     */
    public function test_set_query_variable_with_empty_string(): void {
        $resolver = new Template_Resolver($this->test_post);

        $resolver->set_query_variable('');

        $this->assertEquals('', get_query_var(Template_Resolver::HWP_PREVIEWS_IFRAME_PREVIEW_URL));
    }

    /**
     * Test get_query_variable returns the correct value.
     */
    public function test_get_query_variable_returns_correct_value(): void {
        $test_url = 'https://example.com/preview';

        set_query_var(Template_Resolver::HWP_PREVIEWS_IFRAME_PREVIEW_URL, $test_url);

        $result = Template_Resolver::get_query_variable();

        $this->assertEquals($test_url, $result);
    }

    /**
     * Test get_query_variable returns empty string when not set.
     */
    public function test_get_query_variable_returns_empty_string_when_not_set(): void {
        // Ensure the query var is not set
        set_query_var(Template_Resolver::HWP_PREVIEWS_IFRAME_PREVIEW_URL, '');

        $result = Template_Resolver::get_query_variable();

        $this->assertEquals('', $result);
    }

    /**
     * Test get_query_variable is static and works without instance.
     */
    public function test_get_query_variable_is_static(): void {
        $test_url = 'https://example.com/static-test';

        set_query_var(Template_Resolver::HWP_PREVIEWS_IFRAME_PREVIEW_URL, $test_url);

        // Call static method without creating an instance
        $result = Template_Resolver::get_query_variable();

        $this->assertEquals($test_url, $result);
    }

    /**
     * Test constant is defined correctly.
     */
    public function test_constant_is_defined_correctly(): void {
        $this->assertEquals('hwp_previews_iframe_preview_url', Template_Resolver::HWP_PREVIEWS_IFRAME_PREVIEW_URL);
    }

    /**
     * Test integration: set and get query variable using the same constant.
     */
    public function test_set_and_get_query_variable_integration(): void {
        $resolver = new Template_Resolver($this->test_post);
        $test_url = 'https://example.com/integration-test';

        $resolver->set_query_variable($test_url);
        $retrieved_url = Template_Resolver::get_query_variable();

        $this->assertEquals($test_url, $retrieved_url);
    }

    /**
     * Test is_allowed with different post objects.
     */
    public function test_is_allowed_with_different_post_objects(): void {
        // Create posts with different types and statuses
        $page_id = $this->factory->post->create([
            'post_type' => 'page',
            'post_status' => 'draft'
        ]);
        $page_post = get_post($page_id);

        $post_types = ['page'];
        $post_statuses = ['draft'];

        $resolver = new Template_Resolver($page_post, $post_types, $post_statuses);

        $this->assertTrue($resolver->is_allowed());
    }

    /**
     * Test type safety - ensure post_types array contains only strings.
     */
    public function test_type_safety_post_types(): void {
        $post_types = ['post', 'page', 'custom_type'];

        $resolver = new Template_Resolver($this->test_post, $post_types);

        $reflection = new \ReflectionClass($resolver);
        $post_types_property = $reflection->getProperty('post_types');
        $post_types_property->setAccessible(true);

        $stored_types = $post_types_property->getValue($resolver);

        foreach ($stored_types as $type) {
            $this->assertIsString($type);
        }
    }

    /**
     * Test type safety - ensure post_statuses array contains only strings.
     */
    public function test_type_safety_post_statuses(): void {
        $post_statuses = ['publish', 'draft', 'private'];

        $resolver = new Template_Resolver($this->test_post, [], $post_statuses);

        $reflection = new \ReflectionClass($resolver);
        $post_statuses_property = $reflection->getProperty('post_statuses');
        $post_statuses_property->setAccessible(true);

        $stored_statuses = $post_statuses_property->getValue($resolver);

        foreach ($stored_statuses as $status) {
            $this->assertIsString($status);
        }
    }
}
