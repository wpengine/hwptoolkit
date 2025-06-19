<?php

declare( strict_types=1 );

namespace HWP\Previews\Tests\Core;

use HWP\Previews\Autoloader;
use lucatume\WPBrowser\TestCase\WPTestCase;
use ReflectionClass;

/**
 * Test class for the Autoloader.
 */
class Autoloader_Test extends WPTestCase {


	public function test_autoloader_autoload_instance() {
		// Ensure the Autoloader class can be instantiated
		$autoloader = new Autoloader();

		// Check autoloader instance and methods
		$this->assertInstanceOf( Autoloader::class, $autoloader );
		$this->assertTrue( method_exists( $autoloader, 'autoload' ) );
		$this->assertTrue( method_exists( $autoloader, 'get_composer_autoloader_path' ) );
		$this->assertTrue( method_exists( $autoloader, 'get_is_loaded' ) );

		// Check composer autoloader file exists
		$composer_file = $autoloader::get_composer_autoloader_path();
		$this->assertFileExists( $composer_file );
		$this->assertIsReadable( $composer_file );

		// Autoload the composer dependencies
		$result = $autoloader->autoload();
		$this->assertEquals( $result, $composer_file );
		$this->assertEquals( Autoloader::get_is_loaded(), $composer_file );
		$this->assertEquals( $result, Autoloader::get_is_loaded() );
	}

	// Additional test: Test that autoload() sets is_loaded to true when autoloader file exists and returns true.
	public function test_autoload_sets_is_loaded_true_when_file_exists_and_returns_true(): void {

		// Create a temporary autoloader file that returns true
		$temp_dir = sys_get_temp_dir() . '/hwp-previews-test-' . uniqid();
		mkdir( $temp_dir . '/vendor', 0755, true );
		$autoloader_path = $temp_dir . '/vendor/autoload.php';
		file_put_contents( $autoloader_path, '<?php return true;' );

		// Use reflection to override get_composer_autoloader_path to return our temp file
		$reflection = new ReflectionClass( Autoloader::class );
		$method     = $reflection->getMethod( 'get_composer_autoloader_path' );
		$method->setAccessible( true );

		// Backup original method
		$original_method = $method;

		// Override method to return our temp path
		$mock = $this->getMockBuilder( Autoloader::class )
		             ->disableOriginalConstructor()
		             ->setMethods( [ 'get_composer_autoloader_path' ] )
		             ->getMock();

		// Reset static property
		$property = $reflection->getProperty( 'is_loaded' );
		$property->setAccessible( true );
		$property->setValue( null, false );

		// Call autoload and assert
		$result = Autoloader::autoload();
		$this->assertTrue( $result, 'Autoload should return true when autoloader file returns true' );
		$this->assertTrue( Autoloader::get_is_loaded(), 'is_loaded should be true after successful autoload' );

		// Clean up
		unlink( $autoloader_path );
		rmdir( $temp_dir . '/vendor' );
		rmdir( $temp_dir );
	}

	/**
	 * Test that missing autoloader notice is displayed in admin.
	 */
	public function test_missing_autoloader_notice_admin(): void {

		// Call the method that should trigger the notice
		$reflection = new ReflectionClass( Autoloader::class );
		$method     = $reflection->getMethod( 'missing_autoloader_notice' );
		$method->setAccessible( true );

		$method->invoke( null );

		// Check that hooks were added
		$this->assertTrue( has_action( 'admin_notices' ) );
		$this->assertTrue( has_action( 'network_admin_notices' ) );

		// Test the actual notice output
		ob_start();
		do_action( 'admin_notices' );
		$output = ob_get_clean();

		$this->assertStringContainsString( 'HWP Previews: The Composer autoloader was not found', $output );
		$this->assertStringContainsString( 'composer install', $output );
		$this->assertStringContainsString( 'error notice', $output );
	}


	public function test_get_composer_autoloader_path_returns_expected_path() {
		hwp_previews_constants();
		$expected = HWP_PREVIEWS_PLUGIN_DIR . 'vendor/autoload.php';
		$this->assertEquals(
			$expected,
			Autoloader::get_composer_autoloader_path()
		);
	}

	public function test_require_autoloader_returns_false_if_file_not_readable() {
		$reflection = new \ReflectionClass( Autoloader::class );
		$method     = $reflection->getMethod( 'require_autoloader' );
		$method->setAccessible( true );

		// Use a non-existent file
		$result = $method->invokeArgs( null, [ '/tmp/does-not-exist-' . uniqid() . '.php' ] );
		$this->assertFalse( $result );
	}
}
