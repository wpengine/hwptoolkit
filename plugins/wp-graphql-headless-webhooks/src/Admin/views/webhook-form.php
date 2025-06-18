<?php
/**
 * Webhook form view template.
 *
 * @package WPGraphQL\Webhooks\Admin
 *
 * @var Webhook|null $webhook The webhook being edited (null for new).
 * @var string $form_title Form title.
 * @var string $submit_text Submit button text.
 * @var string $name Webhook name.
 * @var string $event Webhook event.
 * @var string $url Webhook URL.
 * @var string $method HTTP method.
 * @var array $headers Webhook headers.
 * @var array $events Available events.
 * @var array $methods Available methods.
 * @var WebhooksAdmin $admin Admin instance.
 */

use WPGraphQL\Webhooks\Entity\Webhook;
use WPGraphQL\Webhooks\Admin\WebhooksAdmin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="wrap">
	<h1><?php echo esc_html( $form_title ); ?></h1>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<?php wp_nonce_field( 'webhook_save', 'webhook_nonce' ); ?>
		<input type="hidden" name="action" value="graphql_webhook_save">
		<?php if ( $webhook ) : ?>
			<input type="hidden" name="webhook_id" value="<?php echo esc_attr( $webhook->id ); ?>">
		<?php endif; ?>

		<table class="form-table" role="presentation">
			<tbody>
				<tr>
					<th scope="row">
						<label for="webhook_name"><?php esc_html_e( 'Name', 'wp-graphql-webhooks' ); ?></label>
					</th>
					<td>
						<input type="text" id="webhook_name" name="webhook_name" value="<?php echo esc_attr( $name ); ?>" class="regular-text" required>
						<p class="description"><?php esc_html_e( 'A descriptive name for this webhook (e.g., "Notify Slack on Post Publish")', 'wp-graphql-webhooks' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label for="webhook_event"><?php esc_html_e( 'Event', 'wp-graphql-webhooks' ); ?></label>
					</th>
					<td>
						<select id="webhook_event" name="webhook_event" required>
							<option value=""><?php esc_html_e( '— Select Event —', 'wp-graphql-webhooks' ); ?></option>
							<?php foreach ( $events as $event_key => $event_label ) : ?>
								<option value="<?php echo esc_attr( $event_key ); ?>" <?php selected( $event, $event_key ); ?>>
									<?php echo esc_html( $event_label ); ?>
								</option>
							<?php endforeach; ?>
						</select>
						<p class="description"><?php esc_html_e( 'Choose which WordPress event will trigger this webhook', 'wp-graphql-webhooks' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label for="webhook_url"><?php esc_html_e( 'URL', 'wp-graphql-webhooks' ); ?></label>
					</th>
					<td>
						<input type="url" id="webhook_url" name="webhook_url" value="<?php echo esc_attr( $url ); ?>" class="large-text" required>
						<p class="description"><?php esc_html_e( 'The endpoint URL where the webhook payload will be sent', 'wp-graphql-webhooks' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label for="webhook_method"><?php esc_html_e( 'HTTP Method', 'wp-graphql-webhooks' ); ?></label>
					</th>
					<td>
						<select id="webhook_method" name="webhook_method" required>
							<?php foreach ( $methods as $method_option ) : ?>
								<option value="<?php echo esc_attr( $method_option ); ?>" <?php selected( $method, $method_option ); ?>>
									<?php echo esc_html( $method_option ); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</td>
				</tr>

				<tr>
					<th scope="row">
						<?php esc_html_e( 'Headers', 'wp-graphql-webhooks' ); ?>
					</th>
					<td>
						<div class="webhook-headers">
							<div id="webhook-headers-container">
								<?php
								if ( ! empty( $headers ) ) {
									foreach ( $headers as $header_name => $header_value ) {
										include __DIR__ . '/partials/webhook-header-row.php';
									}
								}
								?>
							</div>
							<button type="button" class="button" id="add-header">
								<?php esc_html_e( 'Add Header', 'wp-graphql-webhooks' ); ?>
							</button>
							<p class="description"><?php esc_html_e( 'Optional HTTP headers to send with the webhook request', 'wp-graphql-webhooks' ); ?></p>
						</div>
					</td>
				</tr>
			</tbody>
		</table>

		<p class="submit">
			<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php echo esc_attr( $submit_text ); ?>">
			<a href="<?php echo esc_url( $admin->get_admin_url() ); ?>" class="button">
				<?php esc_html_e( 'Cancel', 'wp-graphql-webhooks' ); ?>
			</a>
		</p>
	</form>
</div>
