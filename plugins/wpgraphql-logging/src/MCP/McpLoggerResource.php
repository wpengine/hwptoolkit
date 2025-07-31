<?php

namespace WPGraphQL\Logging\MCP;

use Automattic\WordpressMcp\Core\RegisterMcpResource;

class McpLoggerResource {


	/**
	 * The single instance of the class.
	 *
	 * @var \WPGraphQL\Logging\MCP\McpLoggerResource|null
	 */
	private static ?McpLoggerResource $instance = null;

	/**
	 * Get or create the single instance of the class.
	 */
	public static function init(): McpLoggerResource {
		if ( null === self::$instance ) {
			self::$instance = new self();
			self::$instance->setup();
		}

		return self::$instance;
	}

	public function setup(): void {
		add_action( 'wordpress_mcp_init', [ $this, 'register_resource' ] );
	}

	public function register_resource(): void {
		WPMCP()->register_resource( [
			'uri'         => 'custom://wpgraphql-logging',
			'name'        => 'WPGraphQL Logging Custom Resource',
			'description' => 'Returns a custom resource response for WPGraphQL Logging.',
			'mimeType'    => 'application/json',
			'callback'    => [ $this, 'get_content' ],
		] );
	}

	public function get_content(): array {
		return [
			'contents' => [
				'message'   => 'This is a custom resource response.',
				'timestamp' => current_time( 'mysql' ),
				'data'      => [
					'some_key'    => 'some_value',
					'another_key' => 'another_value',
				],
			]
		];
	}

}
