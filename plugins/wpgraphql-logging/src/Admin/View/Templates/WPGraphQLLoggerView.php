<?php

declare(strict_types=1);

use WPGraphQL\Logging\Admin\ViewLogsPage;

/**
 * Log detail view template.
 *
 * @package WPGraphQL\Logging
 *
 * @var \WPGraphQL\Logging\Logger\Database\WordPressDatabaseEntity $log
 *
 * @since 0.0.1
 */
?>
<div class="wrap">
	<div class="wpgraphql-logging-view-header">
		<h1><?php esc_html_e( 'Log Entry', 'wpgraphql-logging' ); ?></h1>
		<a href="
		<?php
		$wpgraphql_logging_download_nonce = wp_create_nonce( ViewLogsPage::ADMIN_PAGE_DOWNLOAD_NONCE . '_' . $log->get_id() );
		echo esc_url(
			admin_url(
				sprintf(
					'admin.php?page=%s&action=%s&log=%d&_wpnonce=%s',
					\WPGraphQL\Logging\Admin\ViewLogsPage::ADMIN_PAGE_SLUG,
					'download',
					$log->get_id(),
					$wpgraphql_logging_download_nonce
				)
			)
		);
		?>
		" class="button">
			<?php esc_html_e( 'Download Log', 'wpgraphql-logging' ); ?>
		</a>
	</div>

	<table class="widefat striped wpgraphql-logging-details">
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
				<td><pre class="wpgraphql-logging-query" tabindex="0"><?php echo esc_html( (string) $log->get_query() ); ?></pre></td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Context', 'wpgraphql-logging' ); ?></th>
				<td><pre class="wpgraphql-logging-context" tabindex="0"><?php echo esc_html( (string) wp_json_encode( $log->get_context(), JSON_PRETTY_PRINT ) ); ?></pre></td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Extra', 'wpgraphql-logging' ); ?></th>
				<td><pre class="wpgraphql-logging-extra" tabindex="0"><?php echo esc_html( (string) wp_json_encode( $log->get_extra(), JSON_PRETTY_PRINT ) ); ?></pre></td>
			</tr>
		</tbody>
	</table>

	<p>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . \WPGraphQL\Logging\Admin\ViewLogsPage::ADMIN_PAGE_SLUG ) ); ?>" class="button">
			<?php esc_html_e( 'Back to Logs', 'wpgraphql-logging' ); ?>
		</a>
	</p>
</div>
