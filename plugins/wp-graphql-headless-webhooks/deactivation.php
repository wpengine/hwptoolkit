<?php

declare(strict_types=1);

/**
 * Deactivation Hook
 *
 * @package WPGraphql\Webhooks
 */

/**
 * Runs when WPGraphQL Headless Webhooks is de-activated.
 *
 */
function graphql_headless_webhooks_deactivation_callback(): callable {
	return static function (): void {
		// Fire an action when WPGraphQL Headless Webhook is de-activating.
		do_action( 'graphql_webhooks_deactivate' );
	};
}
