<?php

declare(strict_types=1);

namespace Headless\Documents\Store;

use Headless\Documents\Identifiers;
use WP_Error;
use stdClass;

/**
 * Reads and writes versioned documents.
 *
 * Documents are JSON values grouped into collections, each with an ID that's
 * unique within its collection. Every write names the version it was based on,
 * and a write based on an old version is rejected instead of overwriting a
 * newer one.
 *
 * @package Headless\Documents\Store
 */
final class Document_Store {
	/**
	 * Pattern for collection names: lowercase letters, numbers and hyphens.
	 *
	 * Case-sensitive even inside REST route patterns, which WordPress matches
	 * case-insensitively.
	 *
	 * @var string
	 */
	public const COLLECTION_PATTERN = '(?-i:[a-z0-9-]{1,64})';

	/**
	 * Pattern for document IDs: lowercase letters, numbers and hyphens.
	 *
	 * Case-sensitive even inside REST route patterns, which WordPress matches
	 * case-insensitively.
	 *
	 * @var string
	 */
	public const ID_PATTERN = '(?-i:[a-z0-9-]{1,190})';

	/**
	 * Default largest document, in bytes of encoded JSON.
	 *
	 * @var int
	 */
	public const DEFAULT_MAX_BYTES = 102400;

	/**
	 * Determine if a collection name and document ID are valid.
	 *
	 * @param string $collection Collection name.
	 * @param string $id         Document ID.
	 */
	public static function is_valid_key( string $collection, string $id ): bool {
		return 1 === preg_match( '/^' . self::COLLECTION_PATTERN . '$/', $collection )
			&& 1 === preg_match( '/^' . self::ID_PATTERN . '$/', $id );
	}

	/**
	 * Get the largest document, in bytes of encoded JSON, the store accepts.
	 */
	public static function max_bytes(): int {
		/**
		 * Filters the largest document, in bytes of encoded JSON, the store accepts.
		 *
		 * @param int $max_bytes Maximum size in bytes. Default 100 KB.
		 */
		return (int) apply_filters( 'headless_document_max_bytes', self::DEFAULT_MAX_BYTES );
	}

	/**
	 * Get a document.
	 *
	 * Reads straight from the database, so the version compared during a write
	 * is the version that's actually stored.
	 *
	 * @param string $collection Collection name.
	 * @param string $id         Document ID.
	 *
	 * @return array<string,mixed>|null Document as returned by format(), or null if it doesn't exist.
	 */
	public static function get( string $collection, string $id ): ?array {
		$row = self::get_row( $collection, $id );

		return null === $row ? null : self::format( $row );
	}

	/**
	 * Get a document's version and when and by whom it was last saved, without its data.
	 *
	 * @param string $collection Collection name.
	 * @param string $id         Document ID.
	 *
	 * @return array<string,mixed>|null Array with collection, id, version, modified (ISO 8601) and
	 *                                  modified_by (user ID, or 0 if unknown) keys, or null if the
	 *                                  document doesn't exist.
	 */
	public static function get_summary( string $collection, string $id ): ?array {
		$row = self::get_row( $collection, $id );

		return null === $row ? null : self::format_summary( $row );
	}

	/**
	 * List the documents in a collection, newest first.
	 *
	 * @param string $collection Collection name.
	 * @param int    $limit      Maximum number of documents to return.
	 * @param string $cursor     Cursor from a previous call's next_cursor, or an empty string for the first page.
	 *
	 * @return array{documents: array<array<string,mixed>>, next_cursor: string|null} Documents as returned by
	 *                                                                                format(), and the cursor
	 *                                                                                for the next page, or null
	 *                                                                                on the last page.
	 */
	public static function query( string $collection, int $limit, string $cursor = '' ): array {
		global $wpdb;

		$table = Document_Table::name();
		$limit = max( 1, $limit );
		// The cursor is the table ID of the last document on the previous page.
		$before = '' === $cursor ? PHP_INT_MAX : (int) $cursor;

		/** @var array<\stdClass> $rows */
		$rows = (array) $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE collection = %s AND owner_id = 0 AND deleted_gmt IS NULL AND id < %d ORDER BY id DESC LIMIT %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$collection,
				$before,
				$limit + 1
			)
		);

		$has_more = count( $rows ) > $limit;
		$rows     = array_slice( $rows, 0, $limit );
		$last     = end( $rows );

		return [
			'documents'   => array_map( static fn ( stdClass $row ): array => self::format( $row ), $rows ),
			'next_cursor' => $has_more && false !== $last ? (string) $last->id : null,
		];
	}

	/**
	 * List documents in every collection, newest first, for the admin screen.
	 *
	 * @param int $page     Page number, starting at 1.
	 * @param int $per_page Documents per page.
	 *
	 * @return array{documents: array<array<string,mixed>>, total_pages: int} Summaries as returned by
	 *                                                                        get_summary(), and the number
	 *                                                                        of pages.
	 */
	public static function list_all( int $page = 1, int $per_page = 20 ): array {
		global $wpdb;

		$table    = Document_Table::name();
		$per_page = max( 1, $per_page );
		$offset   = ( max( 1, $page ) - 1 ) * $per_page;

		// phpcs:disable WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		/** @var array<\stdClass> $rows */
		$rows  = (array) $wpdb->get_results(
			$wpdb->prepare(
				"SELECT collection, document_id, version, modified_gmt, modified_by FROM {$table} WHERE owner_id = 0 AND deleted_gmt IS NULL ORDER BY id DESC LIMIT %d OFFSET %d",
				$per_page,
				$offset
			)
		);
		$total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table} WHERE owner_id = 0 AND deleted_gmt IS NULL" );
		// phpcs:enable

		return [
			'documents'   => array_map( static fn ( stdClass $row ): array => self::format_summary( $row ), $rows ),
			'total_pages' => (int) ceil( $total / $per_page ),
		];
	}

	/**
	 * Create or update a document if it's still at the expected version.
	 *
	 * Use version 0 to create a document. An update only matches the row if its
	 * version is still the expected one, so if two writers send the same version
	 * at once, only one of them succeeds.
	 *
	 * Fires headless_document_saved after a successful write.
	 *
	 * @param string $collection       Collection name.
	 * @param string $id               Document ID.
	 * @param int    $expected_version The version the write is based on.
	 * @param mixed  $data             New document data.
	 *
	 * @return array<string,mixed>|\WP_Error The saved document, as returned by format(), plus an is_new flag, or an error.
	 */
	public static function save( string $collection, string $id, int $expected_version, $data ) {
		$encoded_data = self::encode( $data );
		if ( is_wp_error( $encoded_data ) ) {
			return $encoded_data;
		}

		$result = 0 === $expected_version
			? self::create( $collection, $id, $encoded_data )
			: self::update( $collection, $id, $expected_version, $encoded_data );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		$document = [
			'collection' => $collection,
			'id'         => $id,
			'version'    => $result['version'],
			'data'       => $data,
			'created'    => self::format_date( $result['created_gmt'] ),
			'modified'   => self::format_date( $result['modified_gmt'] ),
		];

		/**
		 * Fires after a document is created or updated.
		 *
		 * @param array<string,mixed> $document The saved document.
		 * @param bool                $created  True if the document was created.
		 */
		do_action( 'headless_document_saved', $document, $result['is_new'] );

		return $document + [ 'is_new' => $result['is_new'] ];
	}

	/**
	 * Create a document with a generated ID.
	 *
	 * IDs are 20 random lowercase hexadecimal characters. A clash is retried
	 * with a new ID.
	 *
	 * @param string $collection Collection name.
	 * @param mixed  $data       Document data.
	 *
	 * @return array<string,mixed>|\WP_Error The saved document, as returned by save(), or an error.
	 */
	public static function add( string $collection, $data ) {
		for ( $attempt = 0; $attempt < 3; $attempt++ ) {
			$result = self::save( $collection, bin2hex( random_bytes( 10 ) ), 0, $data );
			if ( ! is_wp_error( $result ) || self::error_code( 'conflict' ) !== $result->get_error_code() ) {
				return $result;
			}
		}

		return self::database_error();
	}

	/**
	 * Delete a document if it's still at the expected version.
	 *
	 * Fires headless_document_deleted after a successful delete.
	 *
	 * @param string $collection       Collection name.
	 * @param string $id               Document ID.
	 * @param int    $expected_version The version the delete is based on.
	 *
	 * @return array<string,mixed>|\WP_Error The deleted document, or an error.
	 */
	public static function delete( string $collection, string $id, int $expected_version ) {
		global $wpdb;

		$previous = self::get( $collection, $id );
		if ( null === $previous ) {
			return self::not_found_error();
		}

		if ( $previous['version'] !== $expected_version ) {
			return self::conflict_error( $collection, $id, $previous );
		}

		$now = current_time( 'mysql', true );

		// Keep the row with its version, so a later create continues from it.
		$deleted = $wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->prepare(
				'UPDATE ' . Document_Table::name() . " SET version = version + 1, data = 'null', modified_gmt = %s, modified_by = %d, deleted_gmt = %s WHERE collection = %s AND owner_id = 0 AND document_id = %s AND version = %d AND deleted_gmt IS NULL", // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				$now,
				get_current_user_id(),
				$now,
				$collection,
				$id,
				$expected_version
			)
		);

		if ( 1 !== $deleted ) {
			return self::failed_write_error( $deleted, $collection, $id );
		}

		/**
		 * Fires after a document is deleted.
		 *
		 * @param array<string,mixed> $previous The document as it was before it was deleted.
		 */
		do_action( 'headless_document_deleted', $previous );

		return $previous;
	}

	/**
	 * Build the error code for a kind of error.
	 *
	 * @param string $kind Kind of error, such as conflict or not_found.
	 */
	public static function error_code( string $kind ): string {
		return Identifiers::ERROR_PREFIX . $kind;
	}

	/**
	 * Build the error returned when a document doesn't exist.
	 */
	public static function not_found_error(): WP_Error {
		return new WP_Error( self::error_code( 'not_found' ), __( 'Document not found.', 'headless-documents' ), [ 'status' => 404 ] );
	}

	/**
	 * Read a document row.
	 *
	 * @param string $collection Collection name.
	 * @param string $id         Document ID.
	 *
	 * @return \stdClass|null Table row, or null if the document doesn't exist.
	 */
	private static function get_row( string $collection, string $id ): ?stdClass {
		global $wpdb;

		$table = Document_Table::name();

		$row = $wpdb->get_row( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE collection = %s AND owner_id = 0 AND document_id = %s AND deleted_gmt IS NULL", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$collection,
				$id
			)
		);

		return $row instanceof stdClass ? $row : null;
	}

	/**
	 * Format a stored date as an ISO 8601 UTC timestamp.
	 *
	 * @param string $gmt Date in MySQL format, in UTC.
	 *
	 * @return string Timestamp such as 2026-10-20T15:04:05Z.
	 */
	private static function format_date( string $gmt ): string {
		return gmdate( 'Y-m-d\TH:i:s\Z', (int) strtotime( $gmt . ' UTC' ) );
	}

	/**
	 * Format a table row as a document.
	 *
	 * @param \stdClass $row Table row.
	 *
	 * @return array<string,mixed> Array with collection, id, version, data, created and modified keys.
	 */
	private static function format( stdClass $row ): array {
		return [
			'collection' => (string) $row->collection,
			'id'         => (string) $row->document_id,
			'version'    => (int) $row->version,
			// Decode JSON objects as objects, so an empty object in the data is
			// returned as {} rather than [].
			'data'       => json_decode( (string) $row->data ),
			'created'    => self::format_date( (string) $row->created_gmt ),
			'modified'   => self::format_date( (string) $row->modified_gmt ),
		];
	}

	/**
	 * Format a table row as a document summary, without its data.
	 *
	 * @param \stdClass $row Table row.
	 *
	 * @return array<string,mixed> Array with collection, id, version, modified and modified_by keys.
	 */
	private static function format_summary( stdClass $row ): array {
		return [
			'collection'  => (string) $row->collection,
			'id'          => (string) $row->document_id,
			'version'     => (int) $row->version,
			'modified'    => self::format_date( (string) $row->modified_gmt ),
			'modified_by' => (int) $row->modified_by,
		];
	}

	/**
	 * Encode document data as JSON and check its size.
	 *
	 * @param mixed $data Document data.
	 *
	 * @return string|\WP_Error JSON string, or an error.
	 */
	private static function encode( $data ) {
		$encoded = wp_json_encode( $data );
		if ( false === $encoded ) {
			return new WP_Error( self::error_code( 'invalid' ), __( 'The document data could not be encoded as JSON.', 'headless-documents' ), [ 'status' => 400 ] );
		}

		if ( strlen( $encoded ) > self::max_bytes() ) {
			return new WP_Error( self::error_code( 'too_large' ), __( 'The document is larger than the maximum size.', 'headless-documents' ), [ 'status' => 413 ] );
		}

		return $encoded;
	}

	/**
	 * Update a document if it's still at the expected version.
	 *
	 * @param string $collection       Collection name.
	 * @param string $id               Document ID.
	 * @param int    $expected_version The version the write is based on.
	 * @param string $encoded_data     Document data encoded as JSON.
	 *
	 * @return array{version: int, created_gmt: string, modified_gmt: string, is_new: bool}|\WP_Error Write result, or an error.
	 */
	private static function update( string $collection, string $id, int $expected_version, string $encoded_data ) {
		global $wpdb;

		$table = Document_Table::name();
		$now   = current_time( 'mysql', true );

		// phpcs:disable WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$updated = $wpdb->query(
			$wpdb->prepare(
				"UPDATE {$table} SET version = version + 1, data = %s, modified_gmt = %s, modified_by = %d WHERE collection = %s AND owner_id = 0 AND document_id = %s AND version = %d AND deleted_gmt IS NULL",
				$encoded_data,
				$now,
				get_current_user_id(),
				$collection,
				$id,
				$expected_version
			)
		);

		if ( 1 !== $updated ) {
			// The document doesn't exist, another write changed it first, or the query failed.
			return false === $updated ? self::database_error() : self::conflict_error( $collection, $id, self::get( $collection, $id ) );
		}

		// The creation date never changes, so reading it after the update is safe.
		$created_gmt = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT created_gmt FROM {$table} WHERE collection = %s AND owner_id = 0 AND document_id = %s",
				$collection,
				$id
			)
		);
		// phpcs:enable

		return [
			'version'      => $expected_version + 1,
			// If another request deleted the document in the meantime, fall back to now.
			'created_gmt'  => is_string( $created_gmt ) ? $created_gmt : $now,
			'modified_gmt' => $now,
			'is_new'       => false,
		];
	}

	/**
	 * Create a new document.
	 *
	 * The table's unique key rejects a second row for the same document, so if
	 * two requests create it at once, only one insert succeeds. A document that
	 * was deleted is revived instead, continuing from its last version.
	 *
	 * @param string $collection   Collection name.
	 * @param string $id           Document ID.
	 * @param string $encoded_data Document data encoded as JSON.
	 *
	 * @return array{version: int, created_gmt: string, modified_gmt: string, is_new: bool}|\WP_Error Write result, or an error.
	 */
	private static function create( string $collection, string $id, string $encoded_data ) {
		global $wpdb;

		$now = current_time( 'mysql', true );

		// Hide the duplicate key error; a failed insert is checked below.
		$suppress = $wpdb->suppress_errors( true );
		$inserted = $wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			Document_Table::name(),
			[
				'collection'   => $collection,
				'owner_id'     => 0,
				'document_id'  => $id,
				'version'      => 1,
				'data'         => $encoded_data,
				'created_gmt'  => $now,
				'modified_gmt' => $now,
				'modified_by'  => get_current_user_id(),
			],
			[ '%s', '%d', '%s', '%d', '%s', '%s', '%s', '%d' ]
		);
		$wpdb->suppress_errors( $suppress );

		if ( 1 === $inserted ) {
			return self::write_result( 1, $now, $now, true );
		}

		$revived = self::revive( $collection, $id, $encoded_data, $now );
		if ( null !== $revived ) {
			return $revived;
		}

		$current = self::get( $collection, $id );

		// If the document exists, another request created it first.
		return null === $current ? self::database_error() : self::conflict_error( $collection, $id, $current );
	}

	/**
	 * Create a document again from its deleted row.
	 *
	 * @param string $collection   Collection name.
	 * @param string $id           Document ID.
	 * @param string $encoded_data Document data encoded as JSON.
	 * @param string $now          Current time in MySQL format, in UTC.
	 *
	 * @return array{version: int, created_gmt: string, modified_gmt: string, is_new: bool}|null Write result, or null if
	 *                                                                                           there's no deleted row to
	 *                                                                                           revive.
	 */
	private static function revive( string $collection, string $id, string $encoded_data, string $now ): ?array {
		global $wpdb;

		$table = Document_Table::name();

		// phpcs:disable WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$deleted_version = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT version FROM {$table} WHERE collection = %s AND owner_id = 0 AND document_id = %s AND deleted_gmt IS NOT NULL",
				$collection,
				$id
			)
		);

		if ( null === $deleted_version ) {
			return null;
		}

		// Only matches if the row is still deleted at that version, so if two
		// requests revive it at once, only one succeeds.
		$revived = $wpdb->query(
			$wpdb->prepare(
				"UPDATE {$table} SET version = version + 1, data = %s, created_gmt = %s, modified_gmt = %s, modified_by = %d, deleted_gmt = NULL WHERE collection = %s AND owner_id = 0 AND document_id = %s AND version = %d AND deleted_gmt IS NOT NULL",
				$encoded_data,
				$now,
				$now,
				get_current_user_id(),
				$collection,
				$id,
				(int) $deleted_version
			)
		);
		// phpcs:enable

		return 1 === $revived ? self::write_result( (int) $deleted_version + 1, $now, $now, true ) : null;
	}

	/**
	 * Build the result of a successful write.
	 *
	 * @param int    $version      The document's new version.
	 * @param string $created_gmt  When the document was created, in MySQL format, in UTC.
	 * @param string $modified_gmt When the document was saved, in MySQL format, in UTC.
	 * @param bool   $is_new       True if the write created the document.
	 *
	 * @return array{version: int, created_gmt: string, modified_gmt: string, is_new: bool} Write result.
	 */
	private static function write_result( int $version, string $created_gmt, string $modified_gmt, bool $is_new ): array {
		return [
			'version'      => $version,
			'created_gmt'  => $created_gmt,
			'modified_gmt' => $modified_gmt,
			'is_new'       => $is_new,
		];
	}

	/**
	 * Build the error for a conditional write that changed no rows.
	 *
	 * @param int|bool $result     Result of $wpdb->query().
	 * @param string   $collection Collection name.
	 * @param string   $id         Document ID.
	 */
	private static function failed_write_error( $result, string $collection, string $id ): WP_Error {
		if ( false === $result ) {
			return self::database_error();
		}

		// Another write changed or deleted the document first.
		$current = self::get( $collection, $id );

		return null === $current ? self::not_found_error() : self::conflict_error( $collection, $id, $current );
	}

	/**
	 * Build the error returned when a write is based on an old version.
	 *
	 * @param string                   $collection Collection name.
	 * @param string                   $id         Document ID.
	 * @param array<string,mixed>|null $current    The current document, or null if it doesn't exist.
	 */
	private static function conflict_error( string $collection, string $id, ?array $current ): WP_Error {
		return new WP_Error(
			self::error_code( 'conflict' ),
			__( 'The document has changed since the version this write was based on.', 'headless-documents' ),
			[
				'status'     => 409,
				'collection' => $collection,
				'id'         => $id,
				'version'    => null === $current ? 0 : $current['version'],
				'document'   => $current,
			]
		);
	}

	/**
	 * Build the error returned when the database rejects a query.
	 */
	private static function database_error(): WP_Error {
		return new WP_Error( self::error_code( 'db_error' ), __( 'The document could not be saved.', 'headless-documents' ), [ 'status' => 500 ] );
	}
}
