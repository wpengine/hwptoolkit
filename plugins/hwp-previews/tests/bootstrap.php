<?php
/**
 * This is a global bootstrap file for autoloading.
 *
 * @package Tests\HWP\Previews
 *
 * @link https://github.com/wpengine/hwptoolkit/blob/main/plugins/hwp-previews/TESTING.md
 */

if (file_exists(__DIR__ . '/../../vendor/autoload.php')) {
	require_once __DIR__ . '/../../vendor/autoload.php';
}

if (file_exists(__DIR__ . '/../../hwp-previews.php')) {
	require_once __DIR__ . '/../../hwp-previews.php';
}

if (file_exists(__DIR__ . '/../../access-functions.php')) {
	require_once __DIR__ . '/../../access-functions.php';
}
