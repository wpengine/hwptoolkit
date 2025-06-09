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
		<div class="webhooks-empty-state">
			<p><?php esc_html_e( 'No webhooks found. Create your first webhook to get started.', 'wp-graphql-headless-webhooks' ); ?></p>
			<a href="<?php echo esc_url( $admin->get_admin_url( array( 'action' => 'add' ) ) ); ?>" class="button button-primary">
				<?php esc_html_e( 'Add New Webhook', 'wp-graphql-headless-webhooks' ); ?>
			</a>
		</div>
	<?php else : ?>
		<table class="wp-list-table widefat fixed striped webhooks-table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Name', 'wp-graphql-headless-webhooks' ); ?></th>
					<th><?php esc_html_e( 'Event', 'wp-graphql-headless-webhooks' ); ?></th>
					<th><?php esc_html_e( 'URL', 'wp-graphql-headless-webhooks' ); ?></th>
					<th><?php esc_html_e( 'Method', 'wp-graphql-headless-webhooks' ); ?></th>
					<th><?php esc_html_e( 'Actions', 'wp-graphql-headless-webhooks' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $webhooks as $webhook ) : ?>
					<tr>
						<td><?php echo esc_html( $webhook->name ); ?></td>
						<td>
							<?php
							$event_label = isset( $events[ $webhook->event ] ) ? $events[ $webhook->event ] : $webhook->event;
							echo esc_html( $event_label );
							?>
						</td>
						<td><?php echo esc_html( $webhook->url ); ?></td>
						<td><?php echo esc_html( $webhook->method ); ?></td>
						<td>
							<a href="
							<?php
							echo esc_url(
								$admin->get_admin_url(
									array(
										'action'     => 'edit',
										'webhook_id' => $webhook->id,
									)
								)
							);
							?>
										" class="button button-small">
								<?php esc_html_e( 'Edit', 'wp-graphql-headless-webhooks' ); ?>
							</a>
							<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=graphql_webhook_delete&webhook_id=' . $webhook->id ), 'delete_webhook_' . $webhook->id ) ); ?>" class="button button-small button-link-delete delete-webhook">
								<?php esc_html_e( 'Delete', 'wp-graphql-headless-webhooks' ); ?>
							</a>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>
</div>
