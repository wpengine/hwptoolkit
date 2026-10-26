<?php
/**
 * This is a global bootstrap file for autoloading.
 *
 * @package HWP\Documents\Tests
 *
 * @link https://github.com/wpengine/hwptoolkit/blob/main/plugins/hwp-documents/TESTING.md
 */

if (file_exists(__DIR__ . '/../../vendor/autoload.php')) {
	require_once __DIR__ . '/../../vendor/autoload.php';
}

if (file_exists(__DIR__ . '/../../hwp-documents.php')) {
	require_once __DIR__ . '/../../hwp-documents.php';
}

if (file_exists(__DIR__ . '/../../access-functions.php')) {
	require_once __DIR__ . '/../../access-functions.php';
}
