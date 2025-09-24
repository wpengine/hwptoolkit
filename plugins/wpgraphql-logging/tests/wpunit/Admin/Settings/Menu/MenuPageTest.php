<?php

declare(strict_types=1);

namespace Tests\WPUnit\Admin\Settings\Menu;

use WPGraphQL\Logging\Admin\SettingsPage;
use WPGraphQL\Logging\Admin\Settings\Menu\MenuPage;
use Codeception\TestCase\WPTestCase;

/**
 * Test class for MenuPage.
 *
 * @package WPGraphQL\Logging
 *
 * @since 0.0.1
 */
class MenuPageTest extends WPTestCase {

	public function test_constructor_sets_properties_correctly(): void {

		$page_title = 'Test Page Title';
		$menu_title = 'Test Menu';
		$menu_slug = 'test-menu-slug';
		$template = '/path/to/template.php';
		$args = ['test_var' => ['key' => 'value']];

		$menu_page = new MenuPage($page_title, $menu_title, $menu_slug, $template, $args);

		$this->assertInstanceOf(MenuPage::class, $menu_page);
	}

	public function test_registration_callback_with_missing_template(): void {
		$menu_page = new MenuPage(
			'Test Page',
			'Test Menu',
			'test-slug',
			'/non/existent/template.php'
		);

		ob_start();
		$menu_page->registration_callback();
		$output = ob_get_clean();

		$this->assertStringContainsString('notice notice-error', $output);
		$this->assertStringContainsString('The WPGraphQL Logging Settings template does not exist.', $output);
	}

	public function test_registration_callback_with_empty_template(): void {
		$menu_page = new MenuPage(
			'Test Page',
			'Test Menu',
			'test-slug',
			''
		);

		ob_start();
		$menu_page->registration_callback();
		$output = ob_get_clean();

		$this->assertStringContainsString('notice notice-error', $output);
	}

	public function test_registration_callback_sets_query_vars(): void {
		// Create a temporary template file
		$template_path = wp_tempnam('test-template');
		file_put_contents($template_path, '<?php echo "Template loaded"; ?>');

		$args = [
			'test_var1' => ['key1' => 'value1'],
			'test_var2' => ['key2' => 'value2']
		];

		$menu_page = new MenuPage(
			'Test Page',
			'Test Menu',
			'test-slug',
			$template_path,
			$args
		);

		ob_start();
		$menu_page->registration_callback();
		$output = ob_get_clean();

		// Check that query vars were set
		$this->assertEquals(['key1' => 'value1'], get_query_var('test_var1'));
		$this->assertEquals(['key2' => 'value2'], get_query_var('test_var2'));
		$this->assertStringContainsString('Template loaded', $output);

		// Clean up
		unlink($template_path);
	}
}
