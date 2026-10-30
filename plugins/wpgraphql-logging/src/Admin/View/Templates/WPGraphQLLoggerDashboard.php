<?php

declare(strict_types=1);

use WPGraphQL\Logging\Admin\DashboardPage;

/**
 * Dashboard template.
 *
 * @package WPGraphQL\Logging
 *
 * @var array<string, array{label: string, bucket_seconds: int, bucket_count: int}> $ranges
 * @var string $current_range
 * @var array{range: string, start: int, end: int, bucket_starts: array<int, int>, summary: array{total: int, errors: int, operations: int}, series: array<int, array{total: int, errors: int}>, levels: array<int, array{level: int, level_name: string, total: int}>, operations: array<int, array{operation: string, total: int, errors: int}>}|null $data
 * @var array<int, array{label: string, full_label: string, total: int, errors: int}> $series
 * @var \WPGraphQL\Logging\Admin\Dashboard\TimeSeriesChart $chart
 * @var string $base_url
 * @var string $logs_url
 *
 * @since 1.1.0
 *
 * @phpcs:disable HWPStandard.ControlStructures.ElseKeyword
 */


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$wpgraphql_logging_range_label = $ranges[ $current_range ]['label'] ?? '';
?>
<div class="wrap wpgraphql-logging-dashboard">
	<h1 class="wp-heading-inline"><?php esc_html_e( 'WPGraphQL Logging Dashboard', 'wpgraphql-logging' ); ?></h1>
	<a href="<?php echo esc_url( $logs_url ); ?>" class="page-title-action"><?php esc_html_e( 'View All Logs', 'wpgraphql-logging' ); ?></a>
	<hr class="wp-header-end">

	<nav class="wpgraphql-logging-dashboard__ranges" aria-label="<?php esc_attr_e( 'Time range', 'wpgraphql-logging' ); ?>">
		<ul class="subsubsub">
			<?php
			$wpgraphql_logging_range_keys = array_keys( $ranges );
			foreach ( $wpgraphql_logging_range_keys as $wpgraphql_logging_index => $wpgraphql_logging_range_key ) :
				$wpgraphql_logging_is_current = $wpgraphql_logging_range_key === $current_range;
				?>
				<li>
					<a
						href="<?php echo esc_url( add_query_arg( 'range', $wpgraphql_logging_range_key, $base_url ) ); ?>"
						class="<?php echo $wpgraphql_logging_is_current ? 'current' : ''; ?>"
						<?php echo $wpgraphql_logging_is_current ? 'aria-current="page"' : ''; ?>
					><?php echo esc_html( $ranges[ $wpgraphql_logging_range_key ]['label'] ); ?></a><?php echo $wpgraphql_logging_index < count( $wpgraphql_logging_range_keys ) - 1 ? ' |' : ''; ?>
				</li>
			<?php endforeach; ?>
		</ul>
	</nav>
	<div class="clear"></div>

	<?php if ( null === $data ) : ?>
		<div class="notice notice-warning inline">
			<p><?php esc_html_e( 'The dashboard is not available for the current log store. Provide a stats service with the wpgraphql_logging_dashboard_stats_service filter to enable it.', 'wpgraphql-logging' ); ?></p>
		</div>
	<?php else : ?>
		<?php
		$wpgraphql_logging_summary = $data['summary'];
		$wpgraphql_logging_tiles   = [
			__( 'Log entries', 'wpgraphql-logging' ) => number_format_i18n( $wpgraphql_logging_summary['total'] ),
			__( 'Errors', 'wpgraphql-logging' )      => number_format_i18n( $wpgraphql_logging_summary['errors'] ),
			/* translators: %s: error rate as a percentage. */
			__( 'Error rate', 'wpgraphql-logging' )  => sprintf( __( '%s%%', 'wpgraphql-logging' ), number_format_i18n( DashboardPage::percent( $wpgraphql_logging_summary['errors'], $wpgraphql_logging_summary['total'] ), 1 ) ),
			__( 'Distinct operations', 'wpgraphql-logging' ) => number_format_i18n( $wpgraphql_logging_summary['operations'] ),
		];

		// Outputs a table cell with a proportional bar and its value.
		$wpgraphql_logging_bar_cell = static function ( int $value, int $max, string $modifier ): void {
			?>
			<td>
				<span class="wpgraphql-logging-dashboard__bar-cell">
					<span class="wpgraphql-logging-dashboard__bar-track" aria-hidden="true">
						<span class="wpgraphql-logging-dashboard__bar wpgraphql-logging-dashboard__bar--<?php echo esc_attr( $modifier ); ?>" style="width: <?php echo esc_attr( number_format( DashboardPage::percent( $value, $max ), 2, '.', '' ) ); ?>%;"></span>
					</span>
					<span class="wpgraphql-logging-dashboard__bar-value"><?php echo esc_html( number_format_i18n( $value ) ); ?></span>
				</span>
			</td>
			<?php
		};
	?>
		<ul class="wpgraphql-logging-dashboard__tiles">
			<?php foreach ( $wpgraphql_logging_tiles as $wpgraphql_logging_tile_label => $wpgraphql_logging_tile_value ) : ?>
				<li class="wpgraphql-logging-dashboard__tile">
					<span class="wpgraphql-logging-dashboard__tile-label"><?php echo esc_html( $wpgraphql_logging_tile_label ); ?></span>
					<span class="wpgraphql-logging-dashboard__tile-value"><?php echo esc_html( $wpgraphql_logging_tile_value ); ?></span>
				</li>
			<?php endforeach; ?>
		</ul>

		<?php if ( 0 === $wpgraphql_logging_summary['total'] ) : ?>
			<div class="notice notice-info inline wpgraphql-logging-dashboard__empty">
				<p><?php esc_html_e( 'No log entries were recorded in this time range.', 'wpgraphql-logging' ); ?></p>
			</div>
		<?php endif; ?>

		<section class="wpgraphql-logging-dashboard__panel" aria-labelledby="wpgraphql-logging-entries-heading">
			<h2 id="wpgraphql-logging-entries-heading"><?php esc_html_e( 'Log entries over time', 'wpgraphql-logging' ); ?></h2>
			<ul class="wpgraphql-logging-dashboard__legend">
				<li><span class="wpgraphql-logging-dashboard__swatch wpgraphql-logging-dashboard__swatch--entries" aria-hidden="true"></span><?php esc_html_e( 'Non-error entries', 'wpgraphql-logging' ); ?></li>
				<li><span class="wpgraphql-logging-dashboard__swatch wpgraphql-logging-dashboard__swatch--errors" aria-hidden="true"></span><?php esc_html_e( 'Errors', 'wpgraphql-logging' ); ?></li>
			</ul>
			<figure class="wpgraphql-logging-chart">
				<?php
				$chart->render(
					$series,
					'wpgraphql-logging-entries-chart',
					/* translators: %s: time range label, e.g. "Last 7 days". */
					sprintf( __( 'Log entries over time, %s', 'wpgraphql-logging' ), $wpgraphql_logging_range_label ),
					sprintf(
						/* translators: 1: total number of log entries, 2: number of error entries. */
						__( 'Stacked column chart. %1$s entries in total, of which %2$s are errors. A data table follows.', 'wpgraphql-logging' ),
						number_format_i18n( $wpgraphql_logging_summary['total'] ),
						number_format_i18n( $wpgraphql_logging_summary['errors'] )
					)
				);
				?>
			</figure>
			<details class="wpgraphql-logging-dashboard__data">
				<summary><?php esc_html_e( 'Show data table', 'wpgraphql-logging' ); ?></summary>
				<table class="widefat striped">
					<caption class="screen-reader-text"><?php esc_html_e( 'Log entries over time', 'wpgraphql-logging' ); ?></caption>
					<thead>
						<tr>
							<th scope="col"><?php esc_html_e( 'Period starting', 'wpgraphql-logging' ); ?></th>
							<th scope="col" class="num"><?php esc_html_e( 'Entries', 'wpgraphql-logging' ); ?></th>
							<th scope="col" class="num"><?php esc_html_e( 'Errors', 'wpgraphql-logging' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $series as $wpgraphql_logging_point ) : ?>
							<tr>
								<th scope="row"><?php echo esc_html( $wpgraphql_logging_point['full_label'] ); ?></th>
								<td class="num"><?php echo esc_html( number_format_i18n( $wpgraphql_logging_point['total'] ) ); ?></td>
								<td class="num"><?php echo esc_html( number_format_i18n( $wpgraphql_logging_point['errors'] ) ); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</details>
		</section>

		<div class="wpgraphql-logging-dashboard__columns">
			<section class="wpgraphql-logging-dashboard__panel" aria-labelledby="wpgraphql-logging-levels-heading">
				<h2 id="wpgraphql-logging-levels-heading"><?php esc_html_e( 'Entries by level', 'wpgraphql-logging' ); ?></h2>
				<?php if ( empty( $data['levels'] ) ) : ?>
					<p class="description"><?php esc_html_e( 'No data for this time range.', 'wpgraphql-logging' ); ?></p>
				<?php else : ?>
					<?php
					$wpgraphql_logging_level_max = max( 1, ...array_column( $data['levels'], 'total' ) );
					?>
					<table class="widefat striped wpgraphql-logging-dashboard__bars">
						<thead>
							<tr>
								<th scope="col"><?php esc_html_e( 'Level', 'wpgraphql-logging' ); ?></th>
								<th scope="col"><?php esc_html_e( 'Entries', 'wpgraphql-logging' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $data['levels'] as $wpgraphql_logging_level ) : ?>
								<tr>
									<th scope="row"><?php echo esc_html( $wpgraphql_logging_level['level_name'] ); ?></th>
									<?php $wpgraphql_logging_bar_cell( $wpgraphql_logging_level['total'], $wpgraphql_logging_level_max, DashboardPage::get_level_modifier( $wpgraphql_logging_level['level'] ) ); ?>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php endif; ?>
			</section>

			<section class="wpgraphql-logging-dashboard__panel" aria-labelledby="wpgraphql-logging-operations-heading">
				<h2 id="wpgraphql-logging-operations-heading"><?php esc_html_e( 'Top operations', 'wpgraphql-logging' ); ?></h2>
				<?php if ( empty( $data['operations'] ) ) : ?>
					<p class="description"><?php esc_html_e( 'No data for this time range.', 'wpgraphql-logging' ); ?></p>
				<?php else : ?>
					<?php
					$wpgraphql_logging_operation_max = max( 1, ...array_column( $data['operations'], 'total' ) );
					?>
					<table class="widefat striped wpgraphql-logging-dashboard__bars">
						<thead>
							<tr>
								<th scope="col"><?php esc_html_e( 'Operation', 'wpgraphql-logging' ); ?></th>
								<th scope="col"><?php esc_html_e( 'Entries', 'wpgraphql-logging' ); ?></th>
								<th scope="col" class="num"><?php esc_html_e( 'Errors', 'wpgraphql-logging' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $data['operations'] as $wpgraphql_logging_operation ) : ?>
								<tr>
									<th scope="row">
										<?php if ( '' === $wpgraphql_logging_operation['operation'] ) : ?>
											<em><?php esc_html_e( '(unnamed)', 'wpgraphql-logging' ); ?></em>
										<?php else : ?>
											<code title="<?php echo esc_attr( $wpgraphql_logging_operation['operation'] ); ?>"><?php echo esc_html( DashboardPage::truncate( $wpgraphql_logging_operation['operation'] ) ); ?></code>
										<?php endif; ?>
									</th>
									<?php $wpgraphql_logging_bar_cell( $wpgraphql_logging_operation['total'], $wpgraphql_logging_operation_max, 'entries' ); ?>
									<td class="num"><?php echo esc_html( number_format_i18n( $wpgraphql_logging_operation['errors'] ) ); ?></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php endif; ?>
			</section>
		</div>

		<p class="description wpgraphql-logging-dashboard__note">
			<?php esc_html_e( 'Counts are log entries, not requests: one GraphQL request can produce an entry for each enabled log point, and only sampled requests are logged. Times use the site time zone. Figures are cached for a short time.', 'wpgraphql-logging' ); ?>
		</p>
	<?php endif; ?>
</div>
