<?php

declare(strict_types=1);

namespace Headless\Documents\Rest;

use Headless\Documents\Identifiers;
use Headless\Documents\Store\Document_Store;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Registers and handles the document REST routes.
 *
 * Routes, under the headless/v1 namespace:
 *
 * - GET    /collections/{collection}/documents        List documents, newest first.
 * - POST   /collections/{collection}/documents        Add a document with a generated ID.
 * - GET    /collections/{collection}/documents/{id}   Read a document.
 * - PUT    /collections/{collection}/documents/{id}   Create (version 0) or update (current version) a document.
 * - DELETE /collections/{collection}/documents/{id}   Delete the current version of a document.
 *
 * @package Headless\Documents\Rest
 */
final class Documents_Controller {
	/**
	 * Callback for the 'rest_api_init' action.
	 */
	public static function register_routes(): void {
		$collection_route = '/collections/(?P<collection>' . Document_Store::COLLECTION_PATTERN . ')/documents';

		register_rest_route(
			Identifiers::REST_NAMESPACE,
			$collection_route,
			[
				[
					'methods'             => 'GET',
					'callback'            => [ self::class, 'list_documents' ],
					'permission_callback' => [ self::class, 'can_read' ],
					'args'                => [
						'limit'  => [
							'type'              => 'integer',
							'default'           => 20,
							'minimum'           => 1,
							'maximum'           => 100,
							'validate_callback' => 'rest_validate_request_arg',
						],
						'cursor' => [
							'type'              => 'string',
							'default'           => '',
							'pattern'           => '^[0-9]{0,20}$',
							'validate_callback' => 'rest_validate_request_arg',
						],
					],
				],
				[
					'methods'             => 'POST',
					'callback'            => [ self::class, 'add_document' ],
					'permission_callback' => [ self::class, 'can_edit' ],
					'args'                => [ 'data' => self::data_arg() ],
				],
			]
		);

		register_rest_route(
			Identifiers::REST_NAMESPACE,
			$collection_route . '/(?P<id>' . Document_Store::ID_PATTERN . ')',
			[
				[
					'methods'             => 'GET',
					'callback'            => [ self::class, 'get_document' ],
					'permission_callback' => [ self::class, 'can_read' ],
				],
				[
					'methods'             => 'PUT',
					'callback'            => [ self::class, 'put_document' ],
					'permission_callback' => [ self::class, 'can_edit' ],
					'args'                => [
						'version' => self::version_arg( 0 ),
						'data'    => self::data_arg(),
					],
				],
				[
					'methods'             => 'DELETE',
					'callback'            => [ self::class, 'delete_document' ],
					'permission_callback' => [ self::class, 'can_edit' ],
					'args'                => [
						'version' => self::version_arg( 1 ),
					],
				],
			]
		);
	}

	/**
	 * Permission callback for reading documents.
	 */
	public static function can_read(): bool {
		return current_user_can( Identifiers::READ_CAPABILITY ); // phpcs:ignore WordPress.WP.Capabilities.Undetermined -- Custom capability.
	}

	/**
	 * Permission callback for writing documents.
	 *
	 * Writers also need read access, because a rejected write returns the
	 * current document.
	 */
	public static function can_edit(): bool {
		return current_user_can( Identifiers::READ_CAPABILITY ) && current_user_can( Identifiers::EDIT_CAPABILITY ); // phpcs:ignore WordPress.WP.Capabilities.Undetermined -- Custom capabilities.
	}

	/**
	 * Validate callback for the document data argument.
	 *
	 * @param mixed $value Document data from the request body.
	 *
	 * @return bool True if the data is a JSON object or array.
	 */
	public static function validate_data( $value ): bool {
		return is_array( $value );
	}

	/**
	 * Callback for GET /collections/{collection}/documents/{id}.
	 *
	 * @param \WP_REST_Request $request Current request.
	 *
	 * @return \WP_REST_Response|\WP_Error The document, or a 404 error.
	 */
	public static function get_document( WP_REST_Request $request ) {
		$document = Document_Store::get( self::route_param( $request, 'collection' ), self::route_param( $request, 'id' ) );

		if ( null === $document ) {
			return Document_Store::not_found_error();
		}

		return new WP_REST_Response( $document, 200 );
	}

	/**
	 * Callback for PUT /collections/{collection}/documents/{id}.
	 *
	 * The body is JSON with the version the write is based on (0 to create)
	 * and the new data. A write based on an old version returns a 409 error
	 * with the current document.
	 *
	 * @param \WP_REST_Request $request Current request.
	 *
	 * @return \WP_REST_Response|\WP_Error The saved document, or an error.
	 */
	public static function put_document( WP_REST_Request $request ) {
		$result = Document_Store::save(
			self::route_param( $request, 'collection' ),
			self::route_param( $request, 'id' ),
			(int) $request->get_param( 'version' ),
			self::request_data( $request )
		);

		return self::document_response( $result );
	}

	/**
	 * Callback for GET /collections/{collection}/documents.
	 *
	 * @param \WP_REST_Request $request Current request.
	 *
	 * @return \WP_REST_Response Documents, newest first, and the cursor for the next page.
	 */
	public static function list_documents( WP_REST_Request $request ): WP_REST_Response {
		$result = Document_Store::query(
			self::route_param( $request, 'collection' ),
			(int) $request->get_param( 'limit' ),
			(string) $request->get_param( 'cursor' )
		);

		return new WP_REST_Response( $result, 200 );
	}

	/**
	 * Callback for POST /collections/{collection}/documents.
	 *
	 * Creates a document with a generated ID.
	 *
	 * @param \WP_REST_Request $request Current request.
	 *
	 * @return \WP_REST_Response|\WP_Error The new document, or an error.
	 */
	public static function add_document( WP_REST_Request $request ) {
		return self::document_response( Document_Store::add( self::route_param( $request, 'collection' ), self::request_data( $request ) ) );
	}

	/**
	 * Callback for DELETE /collections/{collection}/documents/{id}.
	 *
	 * The version the delete is based on is required, as a query or body
	 * parameter. A stale version returns a 409 error with the current document.
	 *
	 * @param \WP_REST_Request $request Current request.
	 *
	 * @return \WP_REST_Response|\WP_Error The deleted document, or an error.
	 */
	public static function delete_document( WP_REST_Request $request ) {
		$result = Document_Store::delete(
			self::route_param( $request, 'collection' ),
			self::route_param( $request, 'id' ),
			(int) $request->get_param( 'version' )
		);

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return new WP_REST_Response(
			[
				'deleted'  => true,
				'previous' => $result,
			],
			200
		);
	}

	/**
	 * Arguments for the document data parameter.
	 *
	 * @return array<string,mixed> Argument definition.
	 */
	private static function data_arg(): array {
		return [
			'required'          => true,
			'validate_callback' => [ self::class, 'validate_data' ],
		];
	}

	/**
	 * Arguments for the version parameter.
	 *
	 * @param int $minimum Smallest version the route accepts.
	 *
	 * @return array<string,mixed> Argument definition.
	 */
	private static function version_arg( int $minimum ): array {
		return [
			'required'          => true,
			'type'              => 'integer',
			'minimum'           => $minimum,
			'validate_callback' => 'rest_validate_request_arg',
		];
	}

	/**
	 * Get a collection name or document ID from the route.
	 *
	 * WP_REST_Request::get_param() prefers JSON body and query parameters over
	 * the route's, so a request could otherwise swap in an identifier the route
	 * pattern never checked.
	 *
	 * @param \WP_REST_Request $request Current request.
	 * @param string           $name    Route parameter name.
	 */
	private static function route_param( WP_REST_Request $request, string $name ): string {
		$params = $request->get_url_params();

		return isset( $params[ $name ] ) ? (string) $params[ $name ] : '';
	}

	/**
	 * Get the document data from a request.
	 *
	 * WordPress decodes JSON request bodies into associative arrays, which
	 * turns an empty object into an empty array. The body is decoded again
	 * here with objects kept as objects, so the stored data matches what was
	 * sent.
	 *
	 * @param \WP_REST_Request $request Current request.
	 *
	 * @return mixed Document data.
	 */
	private static function request_data( WP_REST_Request $request ) {
		$body = json_decode( $request->get_body() );

		if ( is_object( $body ) && property_exists( $body, 'data' ) ) {
			return $body->data;
		}

		return $request->get_param( 'data' );
	}

	/**
	 * Build the response for a saved document.
	 *
	 * @param array<string,mixed>|\WP_Error $result Result of Document_Store::save() or Document_Store::add().
	 *
	 * @return \WP_REST_Response|\WP_Error 201 for a new document, 200 for an update, or the error.
	 */
	private static function document_response( $result ) {
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		$status = true === $result['is_new'] ? 201 : 200;
		unset( $result['is_new'] );

		return new WP_REST_Response( $result, $status );
	}
}
