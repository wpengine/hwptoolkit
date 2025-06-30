<?php
/**
 * Webhooks list view template using WP_List_Table.
 *
 * @package WPGraphQL\Webhooks\Admin
 *
 * @var WebhooksListTable $list_table List table instance.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="wrap">
	<h1 class="wp-heading-inline"><?php esc_html_e( 'Webhooks', 'wp-graphql-webhooks' ); ?></h1>
	<a href="<?php echo esc_url( add_query_arg( 'action', 'add', remove_query_arg( [ 'id', 'deleted' ] ) ) ); ?>" class="page-title-action">
		<?php esc_html_e( 'Add New', 'wp-graphql-webhooks' ); ?>
	</a>
	<hr class="wp-header-end">

	<?php
	// Display admin notices
	if ( isset( $_GET['deleted'] ) ) {
		$deleted = intval( $_GET['deleted'] );
		?>
		<div class="notice notice-success is-dismissible">
			<p>
				<?php
				printf(
					/* translators: %d: number of webhooks deleted */
					_n( '%d webhook deleted.', '%d webhooks deleted.', $deleted, 'wp-graphql-webhooks' ),
					$deleted
				);
				?>
			</p>
		</div>
		<?php
	}

	if ( isset( $_GET['added'] ) ) {
		?>
		<div class="notice notice-success is-dismissible">
			<p><?php esc_html_e( 'Webhook added successfully.', 'wp-graphql-webhooks' ); ?></p>
		</div>
		<?php
	}

	if ( isset( $_GET['updated'] ) ) {
		?>
		<div class="notice notice-success is-dismissible">
			<p><?php esc_html_e( 'Webhook updated successfully.', 'wp-graphql-webhooks' ); ?></p>
		</div>
		<?php
	}
	?>

	<form method="post">
		<?php
		$list_table->prepare_items();
		$list_table->display();
		?>
	</form>
</div>
