<?php

declare(strict_types=1);

/**
 * Deactivation Hook
 *
 * @package WPGraphql\Webhooks
 */

/**
 * Runs when WPGraphQL is de-activated.
 *
 * This cleans up data that WPGraphQL stores.
 */
function graphql_headless_webhooks_deactivation_callback(): callable {
	return static function (): void {
		// Fire an action when WPGraphQL is de-activating.
		do_action( 'graphql_webhooks_deactivate' );
	};
}
