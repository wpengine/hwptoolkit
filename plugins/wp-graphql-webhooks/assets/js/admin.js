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
					var headerRow = $( wpGraphQLWebhooks.headerTemplate || wpGraphQLWebhooks.headerRowTemplate );
					$( '#webhook-headers-container' ).append( headerRow );
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
					$link.text( 'Testing...' ).addClass( 'testing' );
					
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
								$link.text( originalText ).removeClass( 'testing' ).addClass( 'success' );
								if (response.data) {
									var $row = $link.closest( 'tr' );
									var colspan = $row.find( 'td, th' ).length;
									
									// Build detailed result HTML
									var resultHtml = '<div class="webhook-test-details">';
									resultHtml += '<button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>';
									resultHtml += '<p><strong>' + response.data.message + '</strong></p>';
									
									// Response details
									if (response.data.response_code) {
										var statusClass = response.data.success ? 'success' : 'error';
										resultHtml += '<p>Response Status: <span class="status-' + statusClass + '">' + response.data.response_code + '</span></p>';
									}
									
									// Show payload sent
									if (response.data.test_payload) {
										resultHtml += '<details>';
										resultHtml += '<summary>Test Payload Sent</summary>';
										resultHtml += '<pre class="webhook-test-payload">' + JSON.stringify(response.data.test_payload, null, 2) + '</pre>';
										resultHtml += '</details>';
									}
									
									// Show response body if available
									if (response.data.response_body) {
										resultHtml += '<details>';
										resultHtml += '<summary>Response Body</summary>';
										resultHtml += '<pre class="webhook-response-body">' + response.data.response_body + '</pre>';
										resultHtml += '</details>';
									}
									
									resultHtml += '</div>';
									
									var noticeClass = response.data.success ? 'notice-success' : 'notice-warning';
									var $resultRow = $( '<tr id="' + resultId + '" class="webhook-test-result"><td colspan="' + colspan + '"><div class="notice ' + noticeClass + ' is-dismissible inline">' + resultHtml + '</div></td></tr>' );
									$row.after( $resultRow );
									
									// Handle dismiss button
									$( '#' + resultId + ' .notice-dismiss' ).on( 'click', function() {
										$( '#' + resultId ).fadeOut(function() {
											$( this ).remove();
										});
									});
								}
							} else {
								$link.text( originalText ).removeClass( 'testing' ).addClass( 'error' );
								var errorData = response.data || {};
								var $row = $link.closest( 'tr' );
								var colspan = $row.find( 'td, th' ).length;
								
								// Build error HTML
								var errorHtml = '<div class="webhook-test-details">';
								errorHtml += '<button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>';
								errorHtml += '<p><strong>Test failed: ' + (errorData.message || 'Unknown error') + '</strong></p>';
								
								if (errorData.error_code) {
									errorHtml += '<p>Error Code: ' + errorData.error_code + '</p>';
								}
								
								if (errorData.error_data) {
									errorHtml += '<details>';
									errorHtml += '<summary>Error Details</summary>';
									errorHtml += '<pre>' + JSON.stringify(errorData.error_data, null, 2) + '</pre>';
									errorHtml += '</details>';
								}
								
								errorHtml += '</div>';
								
								var $resultRow = $( '<tr id="' + resultId + '" class="webhook-test-result"><td colspan="' + colspan + '"><div class="notice notice-error is-dismissible inline">' + errorHtml + '</div></td></tr>' );
								$row.after( $resultRow );
								
								// Handle dismiss button
								$( '#' + resultId + ' .notice-dismiss' ).on( 'click', function() {
									$( '#' + resultId ).fadeOut(function() {
										$( this ).remove();
									});
								});
							}
						},
						error: function(xhr, status, error) {
							$link.text( originalText ).removeClass( 'testing' ).addClass( 'error' );
							var $row = $link.closest( 'tr' );
							var colspan = $row.find( 'td, th' ).length;
							
							var errorHtml = '<div class="webhook-test-details">';
							errorHtml += '<button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>';
							errorHtml += '<p><strong>Test error: ' + error + '</strong></p>';
							errorHtml += '</div>';
							
							var $resultRow = $( '<tr id="' + resultId + '" class="webhook-test-result"><td colspan="' + colspan + '"><div class="notice notice-error is-dismissible inline">' + errorHtml + '</div></td></tr>' );
							$row.after( $resultRow );
							
							// Handle dismiss button
							$( '#' + resultId + ' .notice-dismiss' ).on( 'click', function() {
								$( '#' + resultId ).fadeOut(function() {
									$( this ).remove();
								});
							});
						}
					});
				}
			);

			// Handle bulk actions if using WP_List_Table
			$('#doaction, #doaction2').on('click', function(e) {
				var action = $(this).prev('select').val();
				
				if (action === 'delete') {
					var checked = $('input[name="webhook[]"]:checked');
					
					if (checked.length === 0) {
						alert('Please select at least one webhook to delete.');
						e.preventDefault();
						return false;
					}
					
					if (!confirm(wpGraphQLWebhooks.confirmDelete)) {
						e.preventDefault();
						return false;
					}
				}
			});
		}
	);

})( jQuery );
