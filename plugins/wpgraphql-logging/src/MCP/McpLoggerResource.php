<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\MCP;

use Automattic\WordpressMcp\Core\RegisterMcpResource;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;
use GraphQL\Utils\SchemaPrinter;

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
		new RegisterMcpResource(
			[
				'uri'         => 'custom://wpgraphql-logging/schema',
				'name'        => 'WPGraphQL Schema',
				'description' => 'Returns the current WPGraphQL Schema Definition Language (SDL).',
				'mimeType'    => 'text/plain',
			],
			[ $this, 'get_content' ]
		);
	}

	public function get_content(): array {
		try {
			// Get the WPGraphQL Schema object.
			$schema = \WPGraphQL::get_schema();
			if ( ! $schema instanceof Schema ) {
				return [
					'contents'   => [
						'error'     => 'Failed to retrieve WPGraphQL Schema object.',
						'code'      => 'schema_object_invalid',
						'timestamp' => current_time( 'mysql' ),
					],
					'statusCode' => 500, // Internal Server Error
				];
			}

			// --- SCHEMA FILTERING LOGIC ---
			// Define the type name you want to filter by.
			// For now, hardcoded to 'Post'. You could make this dynamic via a query parameter.
			$type_name = 'Post'; // Example: filter for the 'Post' type

			// Attempt to get the specific type from the schema.
			$type_to_print = $schema->getType( $type_name );
			if ( ! $type_to_print instanceof Type ) {
				return [
					'contents'   => [
						'error'     => sprintf( 'Type "%s" not found in the GraphQL schema.', $type_name ),
						'code'      => 'type_not_found',
						'timestamp' => current_time( 'mysql' ),
					],
					'statusCode' => 404, // Not Found
				];
			}

			// Convert only the specific Type object to Schema Definition Language (SDL) string.
			$schema_sdl = SchemaPrinter::printType( $type_to_print );

			// Return the SDL string wrapped in a 'contents' key, as MCP expects.
			return [
				'contents'   => $schema_sdl,
				'statusCode' => 200,
			];
		} catch ( \Throwable $e ) {
			error_log( 'Error retrieving WPGraphQL schema for MCP: ' . $e->getMessage() );
			return [
				'contents'   => [
					'error'     => 'An unexpected error occurred while retrieving the schema: ' . $e->getMessage(),
					'code'      => 'internal_error',
					'timestamp' => current_time( 'mysql' ),
				],
				'statusCode' => 500, // Internal Server Error
			];
		}
	}
}
