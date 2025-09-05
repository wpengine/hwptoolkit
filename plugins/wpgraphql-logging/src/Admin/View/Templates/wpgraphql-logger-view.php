<?php

declare(strict_types=1);

/**
 * Log detail view template.
 *
 * @var \WPGraphQL\Logging\Logger\Database\DatabaseEntity $log
 */
?>
<div class="wrap">
	<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
		<h1><?php esc_html_e( 'Log Entry', 'wpgraphql-logging' ); ?></h1>
		<a href="
		<?php
		echo esc_url(
			admin_url(
				sprintf(
					'admin.php?page=%s&action=%s&log=%d',
					\WPGraphQL\Logging\Admin\View_Logs_Page::ADMIN_PAGE_SLUG,
					'download',
					$log->get_id()
				)
			)
		);
		?>
		" class="button">
			<?php esc_html_e( 'Download Log', 'wpgraphql-logging' ); ?>
		</a>
	</div>

	<table class="widefat striped">
		<tbody>
			<tr>
				<th><?php esc_html_e( 'ID', 'wpgraphql-logging' ); ?></th>
				<td><?php echo intval( $log->get_id() ); ?></td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Datetime', 'wpgraphql-logging' ); ?></th>
				<td><?php echo esc_html( (string) $log->get_datetime() ); ?></td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Level', 'wpgraphql-logging' ); ?></th>
				<td><?php echo (int) $log->get_level(); ?></td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Level Name', 'wpgraphql-logging' ); ?></th>
				<td><?php echo esc_html( (string) $log->get_level_name() ); ?></td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Message', 'wpgraphql-logging' ); ?></th>
				<td><?php echo esc_html( (string) $log->get_message() ); ?></td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Channel', 'wpgraphql-logging' ); ?></th>
				<td><?php echo esc_html( (string) $log->get_channel() ); ?></td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Query', 'wpgraphql-logging' ); ?></th>
				<td><pre style="overflow-x: auto; background: #f4f4f4; padding: 15px; border: 1px solid #ddd; border-radius: 4px;"><?php echo esc_html( (string) $log->get_query() ); ?></pre></td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Context', 'wpgraphql-logging' ); ?></th>
				<td><pre><?php echo esc_html( (string) wp_json_encode( $log->get_context(), JSON_PRETTY_PRINT ) ); ?></pre></td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Extra', 'wpgraphql-logging' ); ?></th>
				<td><pre><?php echo esc_html( (string) wp_json_encode( $log->get_extra(), JSON_PRETTY_PRINT ) ); ?></pre></td>
			</tr>
		</tbody>
	</table>

	<p>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . \WPGraphQL\Logging\Admin\View_Logs_Page::ADMIN_PAGE_SLUG ) ); ?>" class="button">
			<?php esc_html_e( 'Back to Logs', 'wpgraphql-logging' ); ?>
		</a>
	</p>
</div>
