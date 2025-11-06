<?php
/**
 * Admin notice template.
 *
 * @package WPGraphQL\Logging
 *
 * @since 0.0.1
 */

declare(strict_types=1);

use WPGraphQL\Logging\Admin\AdminNotice;

$wpgraphql_logging_key        = AdminNotice::ADMIN_NOTICE_KEY;
$wpgraphql_logging_ajax_nonce = wp_create_nonce( $wpgraphql_logging_key );
$wpgraphql_logging_notice     = __( "Heads up! While very useful for debugging, the WPGraphQL Logging Plugin can impact your site's performance under heavy usage, so please use it judiciously.", 'wpgraphql-logging' );
?>

<div id="<?php echo esc_attr( $wpgraphql_logging_key ); ?>" class="notice wpgraphql-logging-admin-notice notice-warning is-dismissible">
	<p><?php echo esc_html( $wpgraphql_logging_notice ); ?></p>
</div>

<script>
	window.addEventListener('load', function () {
		const dismissBtn = document.querySelector('#<?php echo esc_attr( $wpgraphql_logging_key ); ?>.wpgraphql-logging-admin-notice');
		dismissBtn?.addEventListener('click', function (event) {
			let postData = new FormData();
			postData.append('action', '<?php echo esc_attr( $wpgraphql_logging_key ); ?>');
			postData.append('_ajax_nonce', '<?php echo esc_html( $wpgraphql_logging_ajax_nonce ); ?>');

			window.fetch('<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>', {
				method: 'POST',
				body: postData,
			})
		});
	});
</script>

<?php
