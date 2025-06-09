<?php
/**
 * Webhook form view template (used for both add and edit)
 *
 * @package WPGraphQL\Webhooks/Admin
 *
 * @var int    $webhook_id The webhook ID (0 for new webhook)
 * @var string $name The webhook name
 * @var string $event The webhook event
 * @var string $url The webhook URL
 * @var string $method The webhook method
 * @var array  $headers The webhook headers
 * @var array  $events Array of allowed events
 * @var array  $methods Array of allowed methods
 * @var string $form_title Form title
 * @var string $submit_text Submit button text
 * @var WebhooksAdmin $admin Admin instance
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap">
	<h1><?php echo esc_html( $form_title ); ?></h1>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="webhook-form">
		<input type="hidden" name="action" value="graphql_webhook_save">
		<input type="hidden" name="webhook_id" value="<?php echo esc_attr( $webhook_id ); ?>">
		<?php wp_nonce_field( 'graphql_webhook_save', 'graphql_webhook_nonce' ); ?>

		<table class="form-table">
			<tr>
				<th scope="row">
					<label for="webhook_name"><?php esc_html_e( 'Name', 'wp-graphql-headless-webhooks' ); ?></label>
				</th>
				<td>
					<input type="text" id="webhook_name" name="webhook_name" value="<?php echo esc_attr( $name ); ?>" class="regular-text" required>
					<p class="description"><?php esc_html_e( 'A descriptive name to help you identify this webhook (e.g., "Notify Slack on Post Publish").', 'wp-graphql-headless-webhooks' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="webhook_event"><?php esc_html_e( 'Event', 'wp-graphql-headless-webhooks' ); ?></label>
				</th>
				<td>
					<select id="webhook_event" name="webhook_event" required>
						<option value=""><?php esc_html_e( 'Select an event', 'wp-graphql-headless-webhooks' ); ?></option>
						<?php foreach ( $events as $event_key => $event_label ) : ?>
							<option value="<?php echo esc_attr( $event_key ); ?>" <?php selected( $event, $event_key ); ?>>
								<?php echo esc_html( $event_label ); ?>
							</option>
						<?php endforeach; ?>
					</select>
					<p class="description"><?php esc_html_e( 'Choose which WordPress event will trigger this webhook.', 'wp-graphql-headless-webhooks' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="webhook_url"><?php esc_html_e( 'URL', 'wp-graphql-headless-webhooks' ); ?></label>
				</th>
				<td>
					<input type="url" id="webhook_url" name="webhook_url" value="<?php echo esc_attr( $url ); ?>" class="large-text" required>
					<p class="description"><?php esc_html_e( 'The endpoint URL where the webhook payload will be sent.', 'wp-graphql-headless-webhooks' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="webhook_method"><?php esc_html_e( 'Method', 'wp-graphql-headless-webhooks' ); ?></label>
				</th>
				<td>
					<select id="webhook_method" name="webhook_method">
						<?php foreach ( $methods as $method_value => $method_label ) : ?>
							<option value="<?php echo esc_attr( $method_value ); ?>" <?php selected( $method, $method_value ); ?>>
								<?php echo esc_html( $method_label ); ?>
							</option>
						<?php endforeach; ?>
					</select>
					<p class="description"><?php esc_html_e( 'POST is the standard method for webhooks. GET should only be used for simple notifications without payload data.', 'wp-graphql-headless-webhooks' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label><?php esc_html_e( 'Headers', 'wp-graphql-headless-webhooks' ); ?></label>
				</th>
				<td>
					<div id="webhook-headers">
						<?php if ( ! empty( $headers ) ) : ?>
							<?php foreach ( $headers as $key => $value ) : ?>
								<?php include __DIR__ . '/partials/webhook-header-row.php'; ?>
							<?php endforeach; ?>
						<?php else : ?>
							<?php
							$key   = '';
							$value = '';
							include __DIR__ . '/partials/webhook-header-row.php';
							?>
						<?php endif; ?>
					</div>
					<button type="button" id="add-header" class="button"><?php esc_html_e( 'Add Header', 'wp-graphql-headless-webhooks' ); ?></button>
					<p class="description"><?php esc_html_e( 'Optional custom headers to include with the webhook request.', 'wp-graphql-headless-webhooks' ); ?></p>
				</td>
			</tr>
		</table>

		<p class="submit">
			<button type="submit" class="button button-primary"><?php echo esc_html( $submit_text ); ?></button>
			<a href="<?php echo esc_url( $admin->get_admin_url() ); ?>" class="button">
				<?php esc_html_e( 'Cancel', 'wp-graphql-headless-webhooks' ); ?>
			</a>
		</p>
	</form>
</div>
