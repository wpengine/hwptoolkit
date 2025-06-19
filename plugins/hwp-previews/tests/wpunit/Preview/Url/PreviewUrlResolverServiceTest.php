<?php

declare( strict_types=1 );

namespace HWP\Previews\Tests\Unit\Preview\Template;

use HWP\Previews\Preview\Parameter\Preview_Parameter;
use HWP\Previews\Preview\Parameter\Preview_Parameter_Registry;
use HWP\Previews\Preview\Url\Preview_Url_Resolver_Service;
use lucatume\WPBrowser\TestCase\WPTestCase;
use WP_Post;

class Preview_Url_Resolver_Service_Test extends WPTestCase {

	/**
	 * Test get_iframe_template returns template path when file exists.
	 */
	public function test_class_instance(): void {
		$registry = Preview_Parameter_Registry::get_instance();
		$service = new Preview_Url_Resolver_Service($registry);

		$this->assertInstanceOf(Preview_Url_Resolver_Service::class, $service);
	}

	/**
	 * Main logic for preview URL resolution.
	 *
	 * @return void
	 */
	public function test_resolve_default_parameters(): void {

		$registry = Preview_Parameter_Registry::get_instance();
		$service = new Preview_Url_Resolver_Service($registry);

		$author = WPTestCase::factory()->user->create_and_get( [
			'user_login' => 'test_author'
		]);

		$post = WPTestCase::factory()->post->create_and_get( [
			'post_title' => 'Test Post',
			'post_status' => 'publish',
			'post_content' => 'This is a test post.',
			'post_type' => 'page', // Using this as it can be hierarchical
			'post_author' => $author->ID,
			'post_date' => '2023-10-01 12:00:00',
			'post_date_gmt' => '2023-10-01 12:00:00',
			'post_modified' => '2023-10-01 12:00:00',
		] );

		$this->assertEquals(
			$service->resolve($post, 'https://localhost:3000/preview={ID}' ),
			'https://localhost:3000/preview=' . $post->ID
		);

		$this->assertEquals(
					$service->resolve($post, 'https://localhost:3000/preview={author_ID}' ),
			'https://localhost:3000/preview=' . $author->ID
		);

		$this->assertEquals(
					$service->resolve($post, 'https://localhost:3000/preview={status}' ),
			'https://localhost:3000/preview=' . $post->post_status
		);

		$this->assertEquals(
					$service->resolve($post, 'https://localhost:3000/preview={type}' ),
			'https://localhost:3000/preview=' . $post->post_type
		);

		$this->assertEquals(
					$service->resolve($post, 'https://localhost:3000/preview={template}' ),
			'https://localhost:3000/preview=' . get_page_template_slug( $post )
		);

		// Asserting the parent post ID is 0 as we do not set a parent post.
		$this->assertEquals(
					$service->resolve($post, 'https://localhost:3000/preview={parent_ID}' ),
			'https://localhost:3000/preview=0'
		);


		$child_post = WPTestCase::factory()->post->create_and_get( [
			'post_title' => 'Child Post',
			'post_status' => 'publish',
			'post_content' => 'This is a child post.',
			'post_type' => 'page', // Using this as it can be hierarchical
			'post_author' => $author->ID,
			'post_parent' => $post->ID, // Setting the parent post
			'post_date' => '2023-10-01 12:00:00',
			'post_date_gmt' => '2023-10-01 12:00:00',
			'post_modified' => '2023-10-01 12:00:00',
		] );

		$this->assertEquals(
			$service->resolve($child_post, 'https://localhost:3000/preview={parent_ID}' ),
			'https://localhost:3000/preview=' . $post->ID
		);

	}


	public function test_custom_parameters_resolution() {

		$registry = Preview_Parameter_Registry::get_instance();
		$service = new Preview_Url_Resolver_Service($registry);

		$author = WPTestCase::factory()->user->create_and_get( [
			'user_login' => 'test_author',
			'user_email' => uniqid( 'test_author', true ) . '@example.com'
		]);

		$post = WPTestCase::factory()->post->create_and_get( [
			'post_title' => 'Test Post',
			'post_status' => 'publish',
			'post_content' => 'This is a test post.',
			'post_type' => 'page', // Using this as it can be hierarchical
			'post_author' => $author->ID,
			'post_date' => '2023-10-01 12:00:00',
			'post_date_gmt' => '2023-10-01 12:00:00',
			'post_modified' => '2023-10-01 12:00:00',
		] );

		$registry->register(new Preview_Parameter('custom_param', static fn(WP_Post $post) => 'custom_value', 'A custom parameter for testing.'));

		$this->assertEquals(
			$service->resolve($post, 'https://localhost:3000/preview={custom_param}' ),
			'https://localhost:3000/preview=custom_value'
		);
	}

	public function test_custom_parameters_resolution_no_registered_class_returns_placeholder() {

		$registry = Preview_Parameter_Registry::get_instance();
		$service = new Preview_Url_Resolver_Service($registry);

		$author = WPTestCase::factory()->user->create_and_get( [
			'user_login' => 'test_author',
			'user_email' => uniqid( 'test_author', true ) . '@example.com'
		]);

		$post = WPTestCase::factory()->post->create_and_get( [
			'post_title' => 'Test Post',
			'post_status' => 'publish',
			'post_content' => 'This is a test post.',
			'post_type' => 'page', // Using this as it can be hierarchical
			'post_author' => $author->ID,
			'post_date' => '2023-10-01 12:00:00',
			'post_date_gmt' => '2023-10-01 12:00:00',
			'post_modified' => '2023-10-01 12:00:00',
		] );

		// Ensure the custom parameter is not registered
		$registry->unregister('custom_param');

		$this->assertEquals(
			$service->resolve($post, 'https://localhost:3000/preview={custom_param}' ),
			'https://localhost:3000/preview=' . $service::PLACEHOLDER_NOT_FOUND
		);
	}
}
