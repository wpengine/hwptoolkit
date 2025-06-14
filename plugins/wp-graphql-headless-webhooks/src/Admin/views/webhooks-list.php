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
	
	<?php if ( empty( $webhooks ) ) : ?>
		<div class="webhooks-empty-state">
			<h2><?php esc_html_e( 'No webhooks yet', 'wp-graphql-headless-webhooks' ); ?></h2>
			<p><?php esc_html_e( 'Create your first webhook to start receiving notifications when events occur.', 'wp-graphql-headless-webhooks' ); ?></p>
			<a href="<?php echo esc_url( $admin->get_admin_url( array( 'action' => 'add' ) ) ); ?>" class="button button-primary">
				<?php esc_html_e( 'Add New Webhook', 'wp-graphql-headless-webhooks' ); ?>
			</a>
		</div>
	<?php else : ?>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="graphql_webhook_bulk_delete" />
			<?php wp_nonce_field( 'bulk_delete_webhooks' ); ?>
			
			<div class="tablenav top">
				<div class="alignleft actions bulkactions">
					<label for="bulk-action-selector-top" class="screen-reader-text"><?php esc_html_e( 'Select bulk action', 'wp-graphql-headless-webhooks' ); ?></label>
					<select name="bulk_action" id="bulk-action-selector-top">
						<option value=""><?php esc_html_e( 'Bulk actions', 'wp-graphql-headless-webhooks' ); ?></option>
						<option value="delete"><?php esc_html_e( 'Delete', 'wp-graphql-headless-webhooks' ); ?></option>
					</select>
					<input type="submit" class="button action" value="<?php esc_attr_e( 'Apply', 'wp-graphql-headless-webhooks' ); ?>">
				</div>
				<br class="clear">
			</div>
			
			<table class="wp-list-table widefat fixed striped webhooks">
				<thead>
					<tr>
						<td class="manage-column column-cb check-column">
							<input type="checkbox" id="cb-select-all-1" />
						</td>
						<th scope="col" class="manage-column column-name"><?php esc_html_e( 'Name', 'wp-graphql-headless-webhooks' ); ?></th>
						<th scope="col" class="manage-column column-event"><?php esc_html_e( 'Event', 'wp-graphql-headless-webhooks' ); ?></th>
						<th scope="col" class="manage-column column-method"><?php esc_html_e( 'Method', 'wp-graphql-headless-webhooks' ); ?></th>
						<th scope="col" class="manage-column column-url"><?php esc_html_e( 'URL', 'wp-graphql-headless-webhooks' ); ?></th>
						<th scope="col" class="manage-column column-headers"><?php esc_html_e( 'Headers', 'wp-graphql-headless-webhooks' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $webhooks as $webhook ) : ?>
						<tr>
							<th scope="row" class="check-column">
								<input type="checkbox" name="webhook_ids[]" value="<?php echo esc_attr( $webhook->id ); ?>" />
							</th>
							<td class="name column-name">
								<strong>
									<a href="<?php echo esc_url( $admin->get_admin_url( array( 'action' => 'edit', 'webhook_id' => $webhook->id ) ) ); ?>" class="row-title">
										<?php echo esc_html( $webhook->name ); ?>
									</a>
								</strong>
								<div class="row-actions">
									<span class="edit">
										<a href="<?php echo esc_url( $admin->get_admin_url( array( 'action' => 'edit', 'webhook_id' => $webhook->id ) ) ); ?>">
											<?php esc_html_e( 'Edit', 'wp-graphql-headless-webhooks' ); ?>
										</a> | 
									</span>
									<span class="test">
										<a href="#" class="test-webhook" data-webhook-id="<?php echo esc_attr( $webhook->id ); ?>">
											<?php esc_html_e( 'Test', 'wp-graphql-headless-webhooks' ); ?>
										</a> | 
									</span>
									<span class="trash">
										<a href="<?php echo esc_url( wp_nonce_url( $admin->get_admin_url( array( 'action' => 'delete', 'webhook_id' => $webhook->id ) ), 'delete_webhook_' . $webhook->id ) ); ?>" class="delete-webhook submitdelete">
											<?php esc_html_e( 'Delete', 'wp-graphql-headless-webhooks' ); ?>
										</a>
									</span>
								</div>
								<button type="button" class="toggle-row">
									<span class="screen-reader-text"><?php esc_html_e( 'Show more details', 'wp-graphql-headless-webhooks' ); ?></span>
								</button>
							</td>
							<td class="event column-event"><?php echo esc_html( $webhook->event ); ?></td>
							<td class="method column-method">
								<strong><?php echo esc_html( strtoupper( $webhook->method ) ); ?></strong>
							</td>
							<td class="url column-url">
								<code title="<?php echo esc_attr( $webhook->url ); ?>"><?php echo esc_html( $webhook->url ); ?></code>
							</td>
							<td class="headers column-headers">
								<?php if ( ! empty( $webhook->headers ) ) : ?>
									<?php foreach ( $webhook->headers as $header => $value ) : ?>
										<code><?php echo esc_html( $header ); ?></code><br>
									<?php endforeach; ?>
								<?php else : ?>
									<span class="no-headers"><?php esc_html_e( 'None', 'wp-graphql-headless-webhooks' ); ?></span>
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
				<tfoot>
					<tr>
						<td class="manage-column column-cb check-column">
							<input type="checkbox" id="cb-select-all-2" />
						</td>
						<th scope="col" class="manage-column column-name"><?php esc_html_e( 'Name', 'wp-graphql-headless-webhooks' ); ?></th>
						<th scope="col" class="manage-column column-event"><?php esc_html_e( 'Event', 'wp-graphql-headless-webhooks' ); ?></th>
						<th scope="col" class="manage-column column-method"><?php esc_html_e( 'Method', 'wp-graphql-headless-webhooks' ); ?></th>
						<th scope="col" class="manage-column column-url"><?php esc_html_e( 'URL', 'wp-graphql-headless-webhooks' ); ?></th>
						<th scope="col" class="manage-column column-headers"><?php esc_html_e( 'Headers', 'wp-graphql-headless-webhooks' ); ?></th>
					</tr>
				</tfoot>
			</table>
			
			<div class="tablenav bottom">
				<div class="alignleft actions bulkactions">
					<label for="bulk-action-selector-bottom" class="screen-reader-text"><?php esc_html_e( 'Select bulk action', 'wp-graphql-headless-webhooks' ); ?></label>
					<select name="bulk_action2" id="bulk-action-selector-bottom">
						<option value=""><?php esc_html_e( 'Bulk actions', 'wp-graphql-headless-webhooks' ); ?></option>
						<option value="delete"><?php esc_html_e( 'Delete', 'wp-graphql-headless-webhooks' ); ?></option>
					</select>
					<input type="submit" class="button action" value="<?php esc_attr_e( 'Apply', 'wp-graphql-headless-webhooks' ); ?>">
				</div>
				<br class="clear">
			</div>
		</form>
	<?php endif; ?>
</div>
