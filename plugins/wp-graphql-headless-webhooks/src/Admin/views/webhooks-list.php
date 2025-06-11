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
	
	<?php if ( empty( $webhooks ) ) : ?>
		<div class="notice notice-info inline">
			<p>
				<strong><?php esc_html_e( 'No webhooks found.', 'wp-graphql-headless-webhooks' ); ?></strong>
				<?php esc_html_e( 'Create your first webhook to get started.', 'wp-graphql-headless-webhooks' ); ?>
			</p>
			<p>
				<a href="<?php echo esc_url( $admin->get_admin_url( array( 'action' => 'add' ) ) ); ?>" class="button button-primary">
					<?php esc_html_e( 'Add New Webhook', 'wp-graphql-headless-webhooks' ); ?>
				</a>
			</p>
		</div>
	<?php else : ?>
		<div class="tablenav top">
			<div class="tablenav-pages one-page">
				<span class="displaying-num">
					<?php
					printf(
						esc_html( _n( '%s webhook', '%s webhooks', count( $webhooks ), 'wp-graphql-headless-webhooks' ) ),
						number_format_i18n( count( $webhooks ) )
					);
					?>
				</span>
			</div>
			<br class="clear">
		</div>

		<table class="wp-list-table widefat fixed striped table-view-list webhooks-table">
			<thead>
				<tr>
					<th scope="col" class="manage-column column-title column-primary">
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
						<td class="title column-title has-row-actions column-primary">
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
									<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=graphql_webhook_delete&webhook_id=' . $webhook->id ), 'delete_webhook_' . $webhook->id ) ); ?>" class="delete-webhook submitdelete">
										<?php esc_html_e( 'Delete', 'wp-graphql-headless-webhooks' ); ?>
									</a>
								</span>
							</div>
							<button type="button" class="toggle-row">
								<span class="screen-reader-text"><?php esc_html_e( 'Show more details', 'wp-graphql-headless-webhooks' ); ?></span>
							</button>
						</td>
						<td>
							<?php
							$event_label = isset( $events[ $webhook->event ] ) ? $events[ $webhook->event ] : $webhook->event;
							echo esc_html( $event_label );
							?>
						</td>
						<td>
							<strong><?php echo esc_html( strtoupper( $webhook->method ) ); ?></strong>
						</td>
						<td>
							<?php
							$url = $webhook->url;
							$truncated_url = strlen($url) > 50 ? substr($url, 0, 50) . '...' : $url;
							?>
							<code title="<?php echo esc_attr( $url ); ?>" style="cursor: help;"><?php echo esc_html( $truncated_url ); ?></code>
						</td>
						<td>
							<?php 
							$header_count = is_array( $webhook->headers ) ? count( $webhook->headers ) : 0;
							if ( $header_count > 0 ) {
								$header_names = array_keys( $webhook->headers );
								foreach ( $header_names as $header_name ) {
									echo esc_html( $header_name ) . '<br>';
								}
							} else {
								echo '<span class="no-headers">—</span>';
							}
							?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
			<tfoot>
				<tr>
					<th scope="col" class="manage-column column-title column-primary">
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
			<div class="tablenav-pages one-page">
				<span class="displaying-num">
					<?php
					printf(
						esc_html( _n( '%s webhook', '%s webhooks', count( $webhooks ), 'wp-graphql-headless-webhooks' ) ),
						number_format_i18n( count( $webhooks ) )
					);
					?>
				</span>
			</div>
			<br class="clear">
		</div>
	<?php endif; ?>
</div>
