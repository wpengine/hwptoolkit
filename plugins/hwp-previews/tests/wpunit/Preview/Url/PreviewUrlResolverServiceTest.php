<?php

declare( strict_types=1 );

namespace HWP\Previews\Tests\Unit\Preview\Template;

use HWP\Previews\Preview\Parameter\Preview_Parameter_Registry;
use HWP\Previews\Preview\Url\Preview_Url_Resolver_Service;
use lucatume\WPBrowser\TestCase\WPTestCase;

class Preview_Url_Resolver_Service_Test extends WPTestCase {

	/**
	 * Test get_iframe_template returns template path when file exists.
	 */
	public function test_class_instance(): void {
		$registry = Preview_Parameter_Registry::get_instance();
		$service = new Preview_Url_Resolver_Service($registry);

		$this->assertInstanceOf(Preview_Url_Resolver_Service::class, $service);
	}

	// @TODO add more tests for resolve method
}
