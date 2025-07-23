<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Logger\Database;

class DatabaseEntity {
	/**
	 * Gets the name of the logging table.
	 *
	 * @return string The name of the logging table.
	 */
	public static function get_table_name(): string {
		global $wpdb;

		return (string) apply_filters( 'wpgraphql_logging_database_name', $wpdb->prefix . 'wpgraphql_logging' );
	}

	/**
	 * Gets the database schema for the logging table.
	 *
	 * @return string The SQL CREATE an TABLE statement.
	 */
	public static function get_schema(): string {
		global $wpdb;
		$table_name      = self::get_table_name();
		$charset_collate = $wpdb->get_charset_collate();

		return "
       CREATE TABLE {$table_name} (
          id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
          channel VARCHAR(191) NOT NULL,
          level SMALLINT UNSIGNED NOT NULL,
          level_name VARCHAR(50) NOT NULL,
          message LONGTEXT NOT NULL,
          context JSON NULL,
          extra JSON NULL,
          datetime DATETIME NOT NULL,
          INDEX channel_index (channel),
          INDEX level_index (level),
          INDEX datetime_index (datetime)
       ) {$charset_collate};
    ";
	}

	/**
	 * Creates the logging table in the database.
	 */
	public static function create_table(): void {
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$schema = self::get_schema();
		dbDelta( $schema );
	}

	/**
	 * Drops the logging table from the database.
	 */
	public static function drop_table(): void {
		global $wpdb;
		$table_name = self::get_table_name();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
		$wpdb->query( "DROP TABLE IF EXISTS {$table_name}" );
	}
}
