/**
 * Admin JavaScript for WPGraphQL Webhooks
 */

(function ($) {
	'use strict';

	$( document ).ready(
		function () {
			// Handle adding new header fields
			$( '#add-header' ).on(
				'click',
				function () {
					var headerRow = $( wpGraphQLWebhooks.headerTemplate );
					$( '#webhook-headers' ).append( headerRow );
				}
			);

			// Handle removing header fields
			$( document ).on(
				'click',
				'.remove-header',
				function () {
					var headerRows = $( '.webhook-header-row' );

					// Keep at least one header row
					if (headerRows.length > 1) {
						$( this ).closest( '.webhook-header-row' ).remove();
					} else {
						// Clear the values instead of removing the last row
						$( this ).closest( '.webhook-header-row' ).find( 'input' ).val( '' );
					}
				}
			);

			// Confirm webhook deletion
			$( '.delete-webhook' ).on(
				'click',
				function (e) {
					if ( ! confirm( wpGraphQLWebhooks.confirmDelete )) {
						e.preventDefault();
					}
				}
			);
		}
	);

})( jQuery );
