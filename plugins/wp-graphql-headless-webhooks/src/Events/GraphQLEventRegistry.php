<?php
namespace WPGraphQL\Webhooks\Events;

use WPGraphQL\Webhooks\Events\Interfaces\EventRegistration;

class GraphQLEventRegistry implements EventRegistration {

	public function registerEvent( string $name, string $hook_name, ?callable $callback, int $priority = 10, int $arg_count = 1 ): void {
		if ( did_action( 'graphql_register_events' ) ) {
			_doing_it_wrong( 'registerEvent', __( 'Call this before EventRegistry::init()', 'wp-graphql-headless-webhooks' ), '1.0.0' );
			return;
		}
		if ( $callback === null ) {
			// Provide a default no-op callback or handle accordingly
			$callback = fn() => null;
		}
		// Assume https://github.com/wp-graphql/wp-graphql/pull/3376 is merged
		if ( function_exists( 'register_graphql_event' ) ) {
			register_graphql_event( $name, $hook_name, $callback, $priority, $arg_count );
		} else {
			error_log( 'register_graphql_event function does not exist.' );
		}
	}
}