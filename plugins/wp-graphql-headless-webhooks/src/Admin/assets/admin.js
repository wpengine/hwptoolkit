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

			// Handle test webhook clicks using event delegation
			$( document ).on(
				'click',
				'.test-webhook',
				function (e) {
					e.preventDefault();
					
					var $link = $( this );
					var webhookId = $link.data( 'webhook-id' );
					var originalText = $link.text();
					
					// Prevent multiple clicks
					if ($link.hasClass( 'testing' )) {
						return false;
					}
					
					// Generate unique ID for this test result
					var resultId = 'webhook-test-result-' + webhookId + '-' + Date.now();
					
					// Update UI to show testing
					$link.text( 'Testing...' ).addClass( 'testing' ).css( 'pointer-events', 'none' );
					
					// Send test request
					$.ajax({
						url: wpGraphQLWebhooks.ajaxUrl,
						type: 'POST',
						data: {
							action: 'test_webhook',
							webhook_id: webhookId,
							nonce: wpGraphQLWebhooks.nonce
						},
						success: function(response) {
							if (response.success) {
								$link.text( 'Success' );
								if (response.data && response.data.message) {
									var $row = $link.closest( 'tr' );
									var colspan = $row.find( 'td' ).length;
									var message = response.data.message;
									if (response.data.response_code) {
										message += ' (Response: ' + response.data.response_code + ')';
									}
									var $resultRow = $( '<tr id="' + resultId + '" class="webhook-test-result"><td colspan="' + colspan + '"><div class="notice notice-success inline"><p>' + message + '</p></div></td></tr>' );
									$row.after( $resultRow );
									
									// Remove this specific message after 7 seconds
									setTimeout(function() {
										$( '#' + resultId ).fadeOut(function() {
											$( this ).remove();
										});
									}, 7000);
								}
							} else {
								$link.text( 'Failed' );
								var error = response.data || 'Unknown error';
								var $row = $link.closest( 'tr' );
								var colspan = $row.find( 'td' ).length;
								var $resultRow = $( '<tr id="' + resultId + '" class="webhook-test-result"><td colspan="' + colspan + '"><div class="notice notice-error inline"><p>Test failed: ' + error + '</p></div></td></tr>' );
								$row.after( $resultRow );
								
								// Remove this specific message after 7 seconds
								setTimeout(function() {
									$( '#' + resultId ).fadeOut(function() {
										$( this ).remove();
									});
								}, 7000);
							}
							
							// Reset button after 3 seconds
							setTimeout(function() {
								$link.text( originalText ).removeClass( 'testing' ).css( 'pointer-events', 'auto' );
							}, 3000);
						},
						error: function(xhr, status, error) {
							$link.text( 'Error' );
							var $row = $link.closest( 'tr' );
							var colspan = $row.find( 'td' ).length;
							var $resultRow = $( '<tr id="' + resultId + '" class="webhook-test-result"><td colspan="' + colspan + '"><div class="notice notice-error inline"><p>Test error: ' + error + '</p></div></td></tr>' );
							$row.after( $resultRow );
							
							// Remove this specific message after 7 seconds
							setTimeout(function() {
								$( '#' + resultId ).fadeOut(function() {
									$( this ).remove();
								});
							}, 7000);
							
							// Reset button after 3 seconds
							setTimeout(function() {
								$link.text( originalText ).removeClass( 'testing' ).css( 'pointer-events', 'auto' );
							}, 3000);
						}
					});
				}
			);
		}
	);

})( jQuery );
