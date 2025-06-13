<?php
/**
 * Admin notice view template
 *
 * @package WPGraphQL\Webhooks\Admin
 *
 * @var string $message The notice message
 * @var string $type The notice type (success or error)
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$class = $type === 'success' ? 'notice-success' : 'notice-error';
?>
<div class="notice <?php echo esc_attr( $class ); ?> is-dismissible">
	<p><?php echo esc_html( $message ); ?></p>
</div>
