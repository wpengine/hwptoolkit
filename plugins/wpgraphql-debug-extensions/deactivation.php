<?php

declare(strict_types=1);

/**
 * Deactivation Hook
 *
 * @package WPGraphql\Debug
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Runs when WPGraphQL Debug Extensions is de-activated.
 *
 */
function graphql_debug_deactivation_callback(): callable {
	return static function (): void {
		do_action( 'graphql_debug_deactivate' );
	};
}
