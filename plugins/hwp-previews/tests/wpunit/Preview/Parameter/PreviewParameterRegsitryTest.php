<?php

declare( strict_types=1 );

namespace HWP\Previews\Tests\Unit\Preview\Parameter;

use HWP\Previews\Preview\Parameter\Preview_Parameter;
use HWP\Previews\Preview\Parameter\Preview_Parameter_Registry;
use lucatume\WPBrowser\TestCase\WPTestCase;
use ReflectionClass;

class Preview_Parameter_Registry_Test extends WPTestCase {


	public function test_instance_creates_and_sets_up_preview_parameter_registry_when_not_set() {
		$reflection       = new ReflectionClass( Preview_Parameter_Registry::class );
		$instanceProperty = $reflection->getProperty( 'instance' );
		$instanceProperty->setAccessible( true );
		$instanceProperty->setValue( null );

		$this->assertNull( $instanceProperty->getValue() );
		$instance = Preview_Parameter_Registry::get_instance();

		$this->assertInstanceOf( Preview_Parameter_Registry::class, $instanceProperty->getValue() );
		$this->assertSame( $instance, $instanceProperty->getValue(), 'Preview_Parameter_Registry::get_instance() should set the static instance property' );
	}

	public function test_registering_new_parameter() {
		$registry = Preview_Parameter_Registry::get_instance();
		$registry->register(
			new Preview_Parameter( 'test_param', static fn() => 'test_value', 'Test parameter' )
		);

		$parameter = $registry->get( 'test_param' );
		$this->assertInstanceOf( Preview_Parameter::class, $parameter );
		$this->assertSame( 'test_param', $parameter->get_name() );
		$this->assertSame( 'test_value', $parameter->get_value( new \WP_Post( (object) [ 'ID' => 1 ] ) ) );

		$registry->unregister( 'test_param' );
		$this->assertNull( $registry->get( 'test_param' ) );
	}


	public function test_get_all_descriptions() {
		$registry = Preview_Parameter_Registry::get_instance();
		$descriptions = $registry->get_descriptions();
		$this->assertNotEmpty( $descriptions );
	}

}
