<?php
namespace WPGraphQL\Webhooks\Events;

class SmartCacheEventMapper {

	/**
	 * Map WPGraphQL Smart Cache event names to internal webhook event keys.
	 *
	 * @var array<string, string>
	 */
	private static $event_map = [ 
		// Post Events (lowercase and uppercase variants)
		'post_create' => 'post_published',
		'post_update' => 'post_updated',
		'post_delete' => 'post_deleted',
		'post_updated' => 'post_updated',
		'post_deleted' => 'post_deleted',
		'post_CREATE' => 'post_published',
		'post_UPDATE' => 'post_updated',
		'post_DELETE' => 'post_deleted',
		'post_reassigned_to_user' => 'post_updated',
		'postmeta_changed (meta_key' => 'post_meta_change', // This will match partial string

		// Term Events  
		'term_created' => 'term_created',
		'term_updated' => 'term_updated',
		'term_saved' => 'term_updated',
		'term_deleted' => 'term_deleted',
		'term_CREATE' => 'term_created',
		'term_UPDATE' => 'term_updated',
		'term_DELETE' => 'term_deleted',
		'term_relationship_added' => 'term_assigned',
		'term_relationship_deleted' => 'term_unassigned',

		// User Events
		'user_profile_updated' => 'user_updated',
		'user_meta_updated' => 'user_updated',
		'user_deleted' => 'user_deleted',
		'user_reassigned' => 'user_reassigned',
		'user_UPDATE' => 'user_updated',
		'user_DELETE' => 'user_deleted',

		// Menu Events
		'updated_nav_menu' => 'menu_updated',
		'nav_menu_created' => 'menu_created',
		'set_nav_menu_location' => 'menu_updated',
		'menu_meta_updated' => 'menu_updated',
		'nav_menu_item_added' => 'menu_item_created',
		'update_menu_item' => 'menu_item_updated',
		'nav_menu_item_deleted' => 'menu_item_deleted',
		'menu_item_meta_changed' => 'menu_item_updated',

		// Media Events
		'add_attachment' => 'media_uploaded',
		'attachment_edited' => 'media_updated',
		'attachment_deleted' => 'media_deleted',
		'media_UPDATE' => 'media_updated',
		'media_DELETE' => 'media_deleted',

		// Comment Events
		'comment_transition' => 'comment_status',
		'comment_approved' => 'comment_inserted',
		'comment_UPDATE' => 'comment_status',
		'comment_DELETE' => 'comment_status',

		// Cache Events
		'purge all' => 'cache_purged',

		// Node type mappings (for handle_purge_nodes)
		'post' => 'post_updated',
		'term' => 'term_updated',
		'user' => 'user_updated',
		'comment' => 'comment_status',
		'mediaitem' => 'media_updated',
		'menu' => 'menu_updated',
		'menuitem' => 'menu_item_updated',
	];

	/**
	 * Get the mapped webhook event key for a given Smart Cache event.
	 *
	 * @param string $smart_cache_event
	 * @return string|null Returns mapped event key or null if no mapping found.
	 */
	public static function mapEvent( string $smart_cache_event ): ?string {
		// First try direct lookup
		if ( isset( self::$event_map[ $smart_cache_event ] ) ) {
			return self::$event_map[ $smart_cache_event ];
		}

		// Try lowercase version
		$lowercase_event = strtolower( $smart_cache_event );
		if ( isset( self::$event_map[ $lowercase_event ] ) ) {
			return self::$event_map[ $lowercase_event ];
		}

		// Handle postmeta_changed partial match
		if ( strpos( $smart_cache_event, 'postmeta_changed (meta_key' ) === 0 ) {
			return 'post_meta_change';
		}

		// Handle list: prefixed events (from purge method calls)
		if ( strpos( $smart_cache_event, 'list:' ) === 0 ) {
			$type = substr( $smart_cache_event, 5 );
			return self::mapEvent( $type ); // Recursive call to handle the type
		}

		// Handle skipped: prefixed events  
		if ( strpos( $smart_cache_event, 'skipped:' ) === 0 ) {
			$type = substr( $smart_cache_event, 8 );
			return self::mapEvent( $type ); // Recursive call to handle the type
		}

		return null;
	}

	/**
	 * Get all mapped webhook events.
	 *
	 * @return array<string, string>
	 */
	public static function getMappedEvents(): array {
		return self::$event_map;
	}
}