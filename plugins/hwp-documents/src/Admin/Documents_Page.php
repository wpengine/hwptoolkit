<?php

declare(strict_types=1);

namespace HWP\Documents\Admin;

use HWP\Documents\Identifiers;
use HWP\Documents\Store\Document_Store;

/**
 * Read-only Tools screen that lists documents and shows each document's data.
 *
 * Nothing here can change a document.
 *
 * @package HWP\Documents\Admin
 */
final class Documents_Page {
	/**
	 * Documents listed per page.
	 *
	 * @var int
	 */
	public const PER_PAGE = 20;

	/**
	 * Callback for the 'admin_menu' action.
	 */
	public static function register(): void {
		add_management_page(
			__( 'HWP Documents', 'hwp-documents' ),
			__( 'HWP Documents', 'hwp-documents' ),
			Identifiers::READ_CAPABILITY, // phpcs:ignore WordPress.WP.Capabilities.Undetermined -- Custom capability.
			Identifiers::ADMIN_PAGE_SLUG,
			[ self::class, 'render' ]
		);
	}

	/**
	 * Render the screen.
	 */
	public static function render(): void {
		if ( ! current_user_can( Identifiers::READ_CAPABILITY ) ) { // phpcs:ignore WordPress.WP.Capabilities.Undetermined -- Custom capability.
			wp_die( esc_html__( 'Sorry, you are not allowed to view documents.', 'hwp-documents' ), 403 );
		}

		$collection = self::query_arg( 'collection' );
		$id         = self::query_arg( 'document' );
		$paged      = max( 1, absint( self::query_arg( 'paged' ) ) );

		echo '<div class="wrap">';
		if ( '' !== $id ) {
			self::render_detail( $collection, $id );
		}
		if ( '' === $id ) {
			self::render_list( $paged );
		}
		echo '</div>';
	}

	/**
	 * Get a query argument from the current URL.
	 *
	 * @param string $name Query argument name.
	 *
	 * @return string The sanitized value, or an empty string if it's missing or not a string.
	 */
	private static function query_arg( string $name ): string {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Read-only view.
		if ( ! isset( $_GET[ $name ] ) || ! is_string( $_GET[ $name ] ) ) {
			return '';
		}

		return sanitize_text_field( wp_unslash( $_GET[ $name ] ) );
		// phpcs:enable
	}

	/**
	 * URL of the screen.
	 *
	 * @param array<string,string> $args Extra query arguments.
	 */
	private static function url( array $args = [] ): string {
		return add_query_arg( array_merge( [ 'page' => Identifiers::ADMIN_PAGE_SLUG ], $args ), admin_url( 'tools.php' ) );
	}

	/**
	 * When a document was last saved, in the site's time zone.
	 *
	 * @param array<string,mixed> $summary Document summary from Document_Store::get_summary().
	 */
	private static function format_modified( array $summary ): string {
		return (string) wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), (int) strtotime( (string) $summary['modified'] ) );
	}

	/**
	 * Display name of the user who last saved a document.
	 *
	 * @param array<string,mixed> $summary Document summary from Document_Store::get_summary().
	 *
	 * @return string Display name, or an em dash if unknown.
	 */
	private static function last_author( array $summary ): string {
		$user = get_userdata( (int) $summary['modified_by'] );

		return false === $user ? '—' : $user->display_name;
	}

	/**
	 * Render the list of documents.
	 *
	 * @param int $paged Page number, starting at 1.
	 */
	private static function render_list( int $paged ): void {
		$list = Document_Store::list_all( $paged, self::PER_PAGE );

		echo '<h1>' . esc_html__( 'HWP Documents', 'hwp-documents' ) . '</h1>';
		echo '<p>' . esc_html__( 'Documents your app has saved through the document REST API. This screen is read-only.', 'hwp-documents' ) . '</p>';

		if ( [] === $list['documents'] ) {
			echo '<p>' . esc_html__( 'No documents yet. Your app creates a document the first time it saves one.', 'hwp-documents' ) . '</p>';
			return;
		}

		echo '<table class="widefat striped"><thead><tr>';
		echo '<th scope="col">' . esc_html__( 'Collection', 'hwp-documents' ) . '</th>';
		echo '<th scope="col">' . esc_html__( 'ID', 'hwp-documents' ) . '</th>';
		echo '<th scope="col">' . esc_html__( 'Version', 'hwp-documents' ) . '</th>';
		echo '<th scope="col">' . esc_html__( 'Last saved', 'hwp-documents' ) . '</th>';
		echo '<th scope="col">' . esc_html__( 'Saved by', 'hwp-documents' ) . '</th>';
		echo '</tr></thead><tbody>';

		foreach ( $list['documents'] as $summary ) {
			self::render_row( $summary );
		}

		echo '</tbody></table>';

		$pagination = paginate_links(
			[
				'base'    => add_query_arg( 'paged', '%#%', self::url() ),
				'format'  => '',
				'current' => $paged,
				'total'   => $list['total_pages'],
			]
		);
		if ( is_string( $pagination ) && '' !== $pagination ) {
			echo '<div class="tablenav"><div class="tablenav-pages">' . wp_kses_post( $pagination ) . '</div></div>';
		}
	}

	/**
	 * Render one row of the list.
	 *
	 * @param array<string,mixed> $summary Document summary from Document_Store::list_all().
	 */
	private static function render_row( array $summary ): void {
		$collection = (string) $summary['collection'];
		$id         = (string) $summary['id'];

		printf(
			'<tr><td><code>%s</code></td><td><a href="%s"><code>%s</code></a></td><td>%s</td><td>%s</td><td>%s</td></tr>',
			esc_html( $collection ),
			esc_url(
				self::url(
					[
						'collection' => $collection,
						'document'   => $id,
					]
				)
			),
			esc_html( $id ),
			esc_html( (string) $summary['version'] ),
			esc_html( self::format_modified( $summary ) ),
			esc_html( self::last_author( $summary ) )
		);
	}

	/**
	 * Render one document.
	 *
	 * @param string $collection Collection name from the request.
	 * @param string $id         Document ID from the request.
	 */
	private static function render_detail( string $collection, string $id ): void {
		echo '<h1>' . esc_html__( 'Document', 'hwp-documents' ) . '</h1>';
		printf( '<p><a href="%s">%s</a></p>', esc_url( self::url() ), esc_html__( '← All documents', 'hwp-documents' ) );

		$valid    = Document_Store::is_valid_key( $collection, $id );
		$document = $valid ? Document_Store::get( $collection, $id ) : null;
		$summary  = $valid ? Document_Store::get_summary( $collection, $id ) : null;

		if ( null === $document || null === $summary ) {
			echo '<div class="notice notice-error inline"><p>' . esc_html__( 'Document not found.', 'hwp-documents' ) . '</p></div>';
			return;
		}

		$json = wp_json_encode( $document['data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );

		echo '<table class="form-table" role="presentation"><tbody>';
		printf( '<tr><th scope="row">%s</th><td><code>%s</code></td></tr>', esc_html__( 'Collection', 'hwp-documents' ), esc_html( (string) $document['collection'] ) );
		printf( '<tr><th scope="row">%s</th><td><code>%s</code></td></tr>', esc_html__( 'ID', 'hwp-documents' ), esc_html( (string) $document['id'] ) );
		printf( '<tr><th scope="row">%s</th><td>%s</td></tr>', esc_html__( 'Version', 'hwp-documents' ), esc_html( (string) $document['version'] ) );
		printf( '<tr><th scope="row">%s</th><td>%s</td></tr>', esc_html__( 'Last saved', 'hwp-documents' ), esc_html( self::format_modified( $summary ) ) );
		printf( '<tr><th scope="row">%s</th><td>%s</td></tr>', esc_html__( 'Saved by', 'hwp-documents' ), esc_html( self::last_author( $summary ) ) );
		echo '</tbody></table>';

		echo '<h2>' . esc_html__( 'Data', 'hwp-documents' ) . '</h2>';
		echo '<pre style="max-height:40em;overflow:auto;padding:1em;background:#fff;border:1px solid #c3c4c7;"><code style="background:none;padding:0;">' . esc_html( (string) $json ) . '</code></pre>';
	}
}
