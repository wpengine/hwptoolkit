<?php
/**
 * Webhook header row partial template.
 *
 * @package WPGraphQL\Webhooks\Admin
 *
 * @var string $header_name Header name.
 * @var string $header_value Header value.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$header_name  = $header_name ?? '';
$header_value = $header_value ?? '';
?>

<div class="webhook-header-row">
	<input type="text" 
		   name="webhook_headers[name][]" 
		   value="<?php echo esc_attr( $header_name ); ?>" 
		   placeholder="<?php esc_attr_e( 'Header Name', 'wp-graphql-webhooks' ); ?>" 
		   class="regular-text">
	<input type="text" 
		   name="webhook_headers[value][]" 
		   value="<?php echo esc_attr( $header_value ); ?>" 
		   placeholder="<?php esc_attr_e( 'Header Value', 'wp-graphql-webhooks' ); ?>" 
		   class="regular-text">
	<button type="button" class="button remove-header">
		<?php esc_html_e( 'Remove', 'wp-graphql-webhooks' ); ?>
	</button>
</div>
