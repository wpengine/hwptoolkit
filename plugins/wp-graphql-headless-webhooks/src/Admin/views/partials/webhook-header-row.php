<?php
/**
 * Webhook header row partial template
 *
 * @package WPGraphQL\Webhooks\Admin
 *
 * @var string $key Header key value (optional)
 * @var string $value Header value (optional)
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$key   = isset( $key ) ? $key : '';
$value = isset( $value ) ? $value : '';
?>
<div class="webhook-header-row">
	<input type="text" name="webhook_header_key[]" value="<?php echo esc_attr( $key ); ?>" placeholder="<?php esc_attr_e( 'Header name', 'wp-graphql-headless-webhooks' ); ?>" class="regular-text">
	<input type="text" name="webhook_header_value[]" value="<?php echo esc_attr( $value ); ?>" placeholder="<?php esc_attr_e( 'Header value', 'wp-graphql-headless-webhooks' ); ?>" class="regular-text">
	<button type="button" class="button remove-header"><?php esc_html_e( 'Remove', 'wp-graphql-headless-webhooks' ); ?></button>
</div>
