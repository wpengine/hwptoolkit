<?php

declare(strict_types=1);

namespace HWP\Documents\Store;

use HWP\Documents\Identifiers;
use WP_Upgrader;

/**
 * Creates, updates and drops the documents table.
 *
 * @package HWP\Documents\Store
 */
final class Document_Table {
	/**
	 * Version of the documents table schema.
	 *
	 * Bump this when the schema in install() changes, so dbDelta() updates the
	 * table on the next request.
	 *
	 * @var int
	 */
	public const DB_VERSION = 1;

	/**
	 * Get the name of the documents table for the current site.
	 */
	public static function name(): string {
		global $wpdb;

		return $wpdb->prefix . Identifiers::TABLE;
	}

	/**
	 * Create or update the documents table if its schema is out of date.
	 *
	 * Runs on activation and on the first request after the schema changes, or
	 * after the plugin is network activated on a site. The upgrader lock keeps
	 * concurrent requests from running dbDelta() at once.
	 */
	public static function maybe_install(): void {
		global $wpdb;

		if ( self::DB_VERSION === (int) get_option( Identifiers::DB_VERSION_OPTION ) ) {
			return;
		}

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

		if ( ! WP_Upgrader::create_lock( Identifiers::INSTALL_LOCK, 60 ) ) {
			return;
		}

		self::install();

		// Leave the version unrecorded if dbDelta() failed, so the next request retries.
		if ( '' === $wpdb->last_error ) {
			update_option( Identifiers::DB_VERSION_OPTION, self::DB_VERSION, true );
		}

		WP_Upgrader::release_lock( Identifiers::INSTALL_LOCK );
	}

	/**
	 * Create or update the documents table.
	 *
	 * Collections and document IDs are ASCII with a binary collation, so the
	 * unique key is case-sensitive and short enough for older MySQL index
	 * limits. The table can also hold per-user documents, but the routes only
	 * use shared documents (owner_id 0) for now.
	 *
	 * A deleted document keeps its row, with deleted_gmt set, so its version
	 * keeps counting up if it's created again. Otherwise a client holding an
	 * old version could overwrite the new document.
	 */
	public static function install(): void {
		global $wpdb;

		$table           = self::name();
		$charset_collate = $wpdb->get_charset_collate();

		dbDelta(
			"CREATE TABLE {$table} (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				collection varchar(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
				owner_id bigint(20) unsigned NOT NULL DEFAULT 0,
				document_id varchar(190) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
				version bigint(20) unsigned NOT NULL,
				data longtext NOT NULL,
				created_gmt datetime NOT NULL,
				modified_gmt datetime NOT NULL,
				modified_by bigint(20) unsigned NOT NULL DEFAULT 0,
				deleted_gmt datetime DEFAULT NULL,
				PRIMARY KEY  (id),
				UNIQUE KEY collection_owner_document (collection,owner_id,document_id)
			) {$charset_collate};"
		);
	}

	/**
	 * Drop the documents table and its schema version for the current site.
	 *
	 * Called from uninstall.php.
	 */
	public static function uninstall(): void {
		global $wpdb;

		$wpdb->query( 'DROP TABLE IF EXISTS ' . self::name() ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.NotPrepared, WordPressVIPMinimum.Functions.RestrictedFunctions.direct_db_query
		delete_option( Identifiers::DB_VERSION_OPTION );
	}

	/**
	 * Callback for the 'wpmu_drop_tables' filter.
	 *
	 * Drops a site's documents table when the site is deleted.
	 *
	 * @param array<string> $tables Tables to drop for the site being deleted.
	 *
	 * @return array<string> Tables to drop.
	 */
	public static function drop_with_site( array $tables ): array {
		$tables[] = self::name();

		return $tables;
	}
}
