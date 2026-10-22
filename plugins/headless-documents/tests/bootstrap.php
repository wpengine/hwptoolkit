<?php
/**
 * This is a global bootstrap file for autoloading.
 *
 * @package Headless\Documents\Tests
 *
 * @link https://github.com/wpengine/hwptoolkit/blob/main/plugins/headless-documents/TESTING.md
 */

if (file_exists(__DIR__ . '/../../vendor/autoload.php')) {
	require_once __DIR__ . '/../../vendor/autoload.php';
}

if (file_exists(__DIR__ . '/../../headless-documents.php')) {
	require_once __DIR__ . '/../../headless-documents.php';
}

if (file_exists(__DIR__ . '/../../access-functions.php')) {
	require_once __DIR__ . '/../../access-functions.php';
}
