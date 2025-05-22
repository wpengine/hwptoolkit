<?php
namespace WPGraphQL\Webhooks\Events\Interfaces;

interface EventRegistry {
	/**
	 * Initialize the event registry.
	 *
	 * @return void
	 */
	public function init(): void;
	/**
	 * Register a GraphQL event.
	 *
	 * @param string   $name      Unique event name.
	 * @param string   $hook_name WordPress hook to listen to.
	 * @param ?callable $callback  Callback to execute on event.
	 * @param int      $priority  Optional priority.
	 * @param int      $arg_count Optional number of callback args.
	 */
	public function register_event( string $name, string $hook_name, ?callable $callback, int $priority = 10, int $arg_count = 1 ): bool;
}