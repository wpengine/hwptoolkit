<?php
/**
 * Webhooks list view template
 *
 * @package WPGraphQL\Webhooks\Admin
 *
 * @var array $webhooks Array of webhook objects
 * @var array $events Array of allowed events
 * @var WebhooksAdmin $admin Admin instance
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="wrap">
	<h1 class="wp-heading-inline"><?php esc_html_e( 'Webhooks', 'wp-graphql-headless-webhooks' ); ?></h1>
	
	<a href="<?php echo esc_url( $admin->get_admin_url( array( 'action' => 'add' ) ) ); ?>" class="page-title-action">
		<?php esc_html_e( 'Add New', 'wp-graphql-headless-webhooks' ); ?>
	</a>
	
	<hr class="wp-header-end">
	
	<?php if ( ! empty( $_GET['message'] ) ) : ?>
		<?php
		$message = '';
		switch ( $_GET['message'] ) {
			case 'created':
				$message = __( 'Webhook created successfully.', 'wp-graphql-headless-webhooks' );
				$type = 'success';
				break;
			case 'updated':
				$message = __( 'Webhook updated successfully.', 'wp-graphql-headless-webhooks' );
				$type = 'success';
				break;
			case 'deleted':
				$message = __( 'Webhook deleted successfully.', 'wp-graphql-headless-webhooks' );
				$type = 'success';
				break;
			case 'error':
				$message = __( 'An error occurred. Please try again.', 'wp-graphql-headless-webhooks' );
				$type = 'error';
				break;
		}
		?>
		<?php if ( $message ) : ?>
			<div class="notice notice-<?php echo esc_attr( $type ); ?> is-dismissible">
				<p><?php echo esc_html( $message ); ?></p>
			</div>
		<?php endif; ?>
	<?php endif; ?>
	
	<?php if ( ! empty( $webhooks ) ) : ?>
		<table class="wp-list-table widefat fixed striped table-view-list">
			<thead>
				<tr>
					<th scope="col" class="manage-column column-name column-primary">
						<?php esc_html_e( 'Name', 'wp-graphql-headless-webhooks' ); ?>
					</th>
					<th scope="col" class="manage-column column-event">
						<?php esc_html_e( 'Event', 'wp-graphql-headless-webhooks' ); ?>
					</th>
					<th scope="col" class="manage-column column-method">
						<?php esc_html_e( 'Method', 'wp-graphql-headless-webhooks' ); ?>
					</th>
					<th scope="col" class="manage-column column-url">
						<?php esc_html_e( 'URL', 'wp-graphql-headless-webhooks' ); ?>
					</th>
					<th scope="col" class="manage-column column-headers">
						<?php esc_html_e( 'Headers', 'wp-graphql-headless-webhooks' ); ?>
					</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $webhooks as $webhook ) : ?>
					<tr>
						<td class="column-name column-primary" data-colname="<?php esc_attr_e( 'Name', 'wp-graphql-headless-webhooks' ); ?>">
							<strong>
								<a href="<?php echo esc_url( $admin->get_admin_url( array( 'action' => 'edit', 'webhook' => $webhook->id ) ) ); ?>" class="row-title">
									<?php echo esc_html( $webhook->name ); ?>
								</a>
							</strong>
							<div class="row-actions">
								<span class="edit">
									<a href="<?php echo esc_url( $admin->get_admin_url( array( 'action' => 'edit', 'webhook' => $webhook->id ) ) ); ?>">
										<?php esc_html_e( 'Edit', 'wp-graphql-headless-webhooks' ); ?>
									</a> | 
								</span>
								<span class="test">
									<a href="#" class="test-webhook" data-webhook-id="<?php echo esc_attr( $webhook->id ); ?>">
										<?php esc_html_e( 'Test', 'wp-graphql-headless-webhooks' ); ?>
									</a> | 
								</span>
								<span class="trash">
									<a href="<?php echo esc_url( wp_nonce_url( $admin->get_admin_url( array( 'action' => 'delete', 'webhook' => $webhook->id ) ), 'delete_webhook_' . $webhook->id ) ); ?>" class="delete-webhook submitdelete">
										<?php esc_html_e( 'Delete', 'wp-graphql-headless-webhooks' ); ?>
									</a>
								</span>
							</div>
							<button type="button" class="toggle-row">
								<span class="screen-reader-text"><?php esc_html_e( 'Show more details', 'wp-graphql-headless-webhooks' ); ?></span>
							</button>
						</td>
						<td class="column-event" data-colname="<?php esc_attr_e( 'Event', 'wp-graphql-headless-webhooks' ); ?>">
							<code><?php echo esc_html( $webhook->event ); ?></code>
						</td>
						<td class="column-method" data-colname="<?php esc_attr_e( 'Method', 'wp-graphql-headless-webhooks' ); ?>">
							<strong><?php echo esc_html( strtoupper( $webhook->method ) ); ?></strong>
						</td>
						<td class="column-url" data-colname="<?php esc_attr_e( 'URL', 'wp-graphql-headless-webhooks' ); ?>">
							<code><?php echo esc_html( $webhook->url ); ?></code>
						</td>
						<td class="column-headers" data-colname="<?php esc_attr_e( 'Headers', 'wp-graphql-headless-webhooks' ); ?>">
							<?php if ( ! empty( $webhook->headers ) ) : ?>
								<?php foreach ( $webhook->headers as $key => $value ) : ?>
									<div><code><?php echo esc_html( $key ); ?></code></div>
								<?php endforeach; ?>
							<?php else : ?>
								<span class="description"><?php esc_html_e( 'None', 'wp-graphql-headless-webhooks' ); ?></span>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
			<tfoot>
				<tr>
					<th scope="col" class="manage-column column-name column-primary">
						<?php esc_html_e( 'Name', 'wp-graphql-headless-webhooks' ); ?>
					</th>
					<th scope="col" class="manage-column column-event">
						<?php esc_html_e( 'Event', 'wp-graphql-headless-webhooks' ); ?>
					</th>
					<th scope="col" class="manage-column column-method">
						<?php esc_html_e( 'Method', 'wp-graphql-headless-webhooks' ); ?>
					</th>
					<th scope="col" class="manage-column column-url">
						<?php esc_html_e( 'URL', 'wp-graphql-headless-webhooks' ); ?>
					</th>
					<th scope="col" class="manage-column column-headers">
						<?php esc_html_e( 'Headers', 'wp-graphql-headless-webhooks' ); ?>
					</th>
				</tr>
			</tfoot>
		</table>
		
		<div class="tablenav bottom">
			<div class="alignleft actions">
				<p class="description">
					<?php 
					printf(
						esc_html__( '%d webhooks configured', 'wp-graphql-headless-webhooks' ),
						count( $webhooks )
					);
					?>
				</p>
			</div>
		</div>
	<?php else : ?>
		<div class="webhooks-empty-state">
			<p><?php esc_html_e( 'No webhooks configured yet.', 'wp-graphql-headless-webhooks' ); ?></p>
			<p>
				<a href="<?php echo esc_url( $admin->get_admin_url( array( 'action' => 'add' ) ) ); ?>" class="button button-primary">
					<?php esc_html_e( 'Add Your First Webhook', 'wp-graphql-headless-webhooks' ); ?>
				</a>
			</p>
		</div>
	<?php endif; ?>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
	// Handle test webhook clicks
	$('.test-webhook').off('click').on('click', function(e) {
		e.preventDefault();
		
		var $link = $(this);
		var webhookId = $link.data('webhook-id');
		var originalText = $link.text();
		
		// Prevent multiple clicks
		if ($link.hasClass('testing')) {
			return false;
		}
		
		// Update UI to show testing
		$link.text('Testing...').addClass('testing').css('pointer-events', 'none');
		
		// Send test request
		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'test_webhook',
				webhook_id: webhookId,
				nonce: '<?php echo wp_create_nonce( 'wp_rest' ); ?>'
			},
			success: function(response) {
				if (response.success) {
					$link.text('✓ Success');
					if (response.data && response.data.message) {
						// Show inline message using WordPress admin notice
						var message = response.data.message.replace(/\n/g, '<br>');
						var $row = $link.closest('tr');
						var colspan = $row.find('td').length;
						$row.after('<tr class="webhook-test-result"><td colspan="' + colspan + '"><div class="notice notice-success inline"><p>' + message + '</p></div></td></tr>');
						
						// Remove message after 5 seconds
						setTimeout(function() {
							$('.webhook-test-result').fadeOut(function() {
								$(this).remove();
							});
						}, 5000);
					}
				} else {
					$link.text('✗ Failed');
					var error = response.data || 'Unknown error';
					var $row = $link.closest('tr');
					var colspan = $row.find('td').length;
					$row.after('<tr class="webhook-test-result"><td colspan="' + colspan + '"><div class="notice notice-error inline"><p><strong>Test failed:</strong> ' + error + '</p></div></td></tr>');
					
					// Remove message after 5 seconds
					setTimeout(function() {
						$('.webhook-test-result').fadeOut(function() {
							$(this).remove();
						});
					}, 5000);
				}
				
				// Reset button after 3 seconds
				setTimeout(function() {
					$link.text(originalText).removeClass('testing').css('pointer-events', 'auto');
				}, 3000);
			},
			error: function(xhr, status, error) {
				$link.text('✗ Error');
				var $row = $link.closest('tr');
				var colspan = $row.find('td').length;
				$row.after('<tr class="webhook-test-result"><td colspan="' + colspan + '"><div class="notice notice-error inline"><p><strong>Test error:</strong> ' + error + '</p></div></td></tr>');
				
				// Remove message after 5 seconds
				setTimeout(function() {
					$('.webhook-test-result').fadeOut(function() {
						$(this).remove();
					});
				}, 5000);
				
				// Reset button after 3 seconds
				setTimeout(function() {
					$link.text(originalText).removeClass('testing').css('pointer-events', 'auto');
				}, 3000);
			}
		});
	});
	
	// Handle delete confirmation
	$('.delete-webhook').on('click', function(e) {
		if (!confirm('<?php echo esc_js( __( 'Are you sure you want to delete this webhook?', 'wp-graphql-headless-webhooks' ) ); ?>')) {
			e.preventDefault();
		}
	});
});
</script>
