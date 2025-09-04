<?php

declare(strict_types=1);

/**
 * Webhooks list view template using WP_List_Table.
 *
 * @package WPGraphQL\Logger\Admin\View\List\Templates
 *
 * @var \WPGraphQL\Logging\Admin\View\List\List_Table $list_table List table instance.
 *
 * @since 0.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
	
<div class="wrap">
	<h1 class="wp-heading-inline"><?php esc_html_e( 'WPGraphQL Logs', 'wpgraphql-logging' ); ?></h1>
	<hr class="wp-header-end">

	<form method="post">
		<?php
		$list_table->prepare_items();
		$list_table->display_bulk_actions_top();
		$list_table->display();
		$list_table->display_bulk_actions_bottom();
		?>
	</form>
</div>
