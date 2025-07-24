<?php
/**
 * This is a global bootstrap file for autoloading.
 *
 * @package Tests\WPGraphQL\Logging
 *
 * @link https://github.com/wpengine/hwptoolkit/blob/main/plugins/wpgraphql-logging/TESTING.md
 */

if (file_exists(__DIR__ . '/../../vendor/autoload.php')) {
	require_once __DIR__ . '/../../vendor/autoload.php';
}

if (file_exists(__DIR__ . '/../../wpgraphql-logging.php')) {
	require_once __DIR__ . '/../../wpgraphql-logging.php';
}

if (file_exists(__DIR__ . '/../../access-functions.php')) {
	require_once __DIR__ . '/../../access-functions.php';
}
