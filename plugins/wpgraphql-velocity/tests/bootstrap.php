<?php
/**
 * This is a global bootstrap file for autoloading.
 *
 * @package Tests\WPGraphQL\Velocity
 *
 * @link https://github.com/wpengine/hwptoolkit/blob/main/plugins/wpgraphql-velocity/TESTING.md
 */

if (file_exists(__DIR__ . '/../../vendor/autoload.php')) {
	require_once __DIR__ . '/../../vendor/autoload.php';
}

if (file_exists(__DIR__ . '/../../wpgraphql-velocity.php')) {
	require_once __DIR__ . '/../../wpgraphql-velocity.php';
}

if (file_exists(__DIR__ . '/../../access-functions.php')) {
	require_once __DIR__ . '/../../access-functions.php';
}
