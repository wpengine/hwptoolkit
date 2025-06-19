<?php

declare( strict_types=1 );

namespace HWP\Previews\Tests\Unit\Preview\Parameter;

use HWP\Previews\Preview\Parameter\Preview_Parameter;
use lucatume\WPBrowser\TestCase\WPTestCase;
use WP_Post;

class Preview_Parameter_Test extends WPTestCase {


	public function test_create_instance_check_name_description() {
		$preview = new Preview_Parameter('ID', static fn( WP_Post $post ) => (string) $post->ID, 'Post ID.');
		$this->assertEquals( 'Post ID.', $preview->get_description() );
		$this->assertEquals( 'ID', $preview->get_name() );
	}

	public function test_create_instance_get_value() {
		$preview = new Preview_Parameter('status', static fn( WP_Post $post ) => $post->post_status, 'The post status.');

		$post = WPTestCase::factory()->post->create_and_get( [
			'post_title' => 'Test Post',
			'post_status' => 'publish',
			'post_content' => 'This is a test post.',
		] );

		$this->assertEquals($post->post_status, $preview->get_value($post));
	}
}
