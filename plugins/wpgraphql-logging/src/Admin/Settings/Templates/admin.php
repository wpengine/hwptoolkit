<?php
/**
 * The admin settings page for the WPGraphQL Logging plugin.
 *
 * @package WPGraphQL\Logging
 *
 * @since 0.0.1
 */

declare(strict_types=1);

use WPGraphQL\Logging\Admin\Settings\Fields\Tab\BasicConfigurationTab;
use WPGraphQL\Logging\Admin\Settings\Fields\Tab\DataManagementTab;
use WPGraphQL\Logging\Admin\Settings\LoggingSettingsService;

$wpgraphql_logging_tabs_config = (array) get_query_var( 'wpgraphql_logging_main_page_config' );
$wpgraphql_logging_current_tab = (string) ( $wpgraphql_logging_tabs_config['current_tab'] ?? '' );
$wpgraphql_logging_tabs        = (array) ( $wpgraphql_logging_tabs_config['tabs'] ?? [] );
?>

<div class="wrap">
	<h1><?php esc_html_e( 'WPGraphQL Logging Settings', 'wpgraphql-logging' ); ?></h1>
	<form method="post" action="options.php">
		<nav class="nav-tab-wrapper">
			<?php
			foreach ( $wpgraphql_logging_tabs as $wpgraphql_logging_tab_key => $wpgraphql_logging_tab_label ) {
				$wpgraphql_logging_tab_url   = admin_url( 'admin.php?page=wpgraphql-logging&tab=' . $wpgraphql_logging_tab_key );
				$wpgraphql_logging_tab_class = add_cssclass( $wpgraphql_logging_current_tab === $wpgraphql_logging_tab_key ? 'nav-tab-active' : '', 'nav-tab' );
				echo '<a href="' . esc_url( $wpgraphql_logging_tab_url ) . '" class="' . esc_attr( $wpgraphql_logging_tab_class ) . '">' . esc_html( $wpgraphql_logging_tab_label ) . '</a>';
			}
			?>
		</nav>

		<div id="poststuff">
			<div id="post-body" class="metabox-holder columns-2">
				<div id="post-body-content">
					<div class="meta-box-sortables ui-sortable">
						<div class="postbox">
							<div class="inside">
								<?php
								// Settings sections.
								settings_fields( LoggingSettingsService::get_settings_group() );
								do_settings_sections( 'wpgraphql-logging-' . $wpgraphql_logging_current_tab );
								submit_button();
								?>
							</div>
						</div>
					</div>
				</div>

				<!-- sidebar -->
				<div id="postbox-container-1" class="postbox-container">
					<div class="meta-box-sortables">
						<div class="postbox">
							<h3 class="wpgraphql-logging-docs-title">
								<?php esc_html_e( 'About WPGraphQL Logging', 'wpgraphql-logging' ); ?>
							</h3>
							<div class="inside">
								<p><?php esc_html_e( 'WPGraphQL Logging provides visibility into GraphQL request lifecycle to help quickly identify and resolve bottlenecks in your headless WordPress application.', 'wpgraphql-logging' ); ?></p>

								<h4><?php esc_html_e( 'Current Tab: ', 'wpgraphql-logging' ); ?><?php echo esc_html( $wpgraphql_logging_tabs[ $wpgraphql_logging_current_tab ] ?? '' ); ?></h4>

								<?php
								switch ( $wpgraphql_logging_current_tab ) {
									case BasicConfigurationTab::get_name():
										?>
										<p><?php esc_html_e( 'Configure the fundamental logging settings to control when and how WPGraphQL requests are logged.', 'wpgraphql-logging' ); ?></p>
										<ul class="wpgraphql-logging-feature-list">
											<li><?php esc_html_e( 'Enable/disable logging', 'wpgraphql-logging' ); ?></li>
											<li><?php esc_html_e( 'Set IP restrictions', 'wpgraphql-logging' ); ?></li>
											<li><?php esc_html_e( 'Log only for admin users', 'wpgraphql-logging' ); ?></li>
											<li><?php esc_html_e( 'Data sampling', 'wpgraphql-logging' ); ?></li>
											<li><?php esc_html_e( 'Log specific queries', 'wpgraphql-logging' ); ?></li>
											<li><?php esc_html_e( 'Exclude queries', 'wpgraphql-logging' ); ?></li>
											<li><?php esc_html_e( 'Log response', 'wpgraphql-logging' ); ?></li>
										</ul>
										<?php
										break;

									case DataManagementTab::get_name():
										?>
										<p><?php esc_html_e( 'Manage how logging data is sanitized and deleted.', 'wpgraphql-logging' ); ?></p>
										<ul class="wpgraphql-logging-feature-list">
											<li><?php esc_html_e( 'Enable/disable data deletion', 'wpgraphql-logging' ); ?></li>
											<li><?php esc_html_e( 'Number of days logs are stored', 'wpgraphql-logging' ); ?></li>
											<li><?php esc_html_e( 'Enable/disable data sanitization', 'wpgraphql-logging' ); ?></li>
											<li><?php esc_html_e( 'List fields for sanitization', 'wpgraphql-logging' ); ?></li>
										</ul>
										<?php
										break;

									default:
										?>
										<p><?php esc_html_e( 'Select a tab above to configure your logging settings.', 'wpgraphql-logging' ); ?></p>
										<?php
										break;
								}
								?>
																		<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . \WPGraphQL\Logging\Admin\ViewLogsPage::ADMIN_PAGE_SLUG ) ); ?>" class="button">
			<?php esc_html_e( 'View Logs', 'wpgraphql-logging' ); ?>
		</a>
							</div>
						</div>

						<div class="postbox">
							<h3><?php esc_html_e( 'Get Started', 'wpgraphql-logging' ); ?></h3>
							<div class="inside wpgraphql-logging-docs">
								<ul>
									<li><a href="https://github.com/wpengine/hwptoolkit/tree/main/plugins/wpgraphql-logging#getting-started" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Getting Started', 'wpgraphql-logging' ); ?></a></li>
									<li><a href="https://github.com/wpengine/hwptoolkit/blob/main/README.md" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Documentation', 'wpgraphql-logging' ); ?></a></li>
									<li><a href="https://github.com/wpengine/hwptoolkit" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'HWP Toolkit', 'wpgraphql-logging' ); ?></a></li>
								</ul>
								<p><?php esc_html_e( 'WPGraphQL Logging is part of the HWP Toolkit, our comprehensive suite of tools and examples for headless WordPress.', 'wpgraphql-logging' ); ?></p>
								<p><a class="button-secondary" href="https://github.com/wpengine/hwptoolkit" target="_blank" rel="noopener noreferrer">HWP Toolkit on GitHub</a></p>
							</div>
						</div>
					</div>
				</div>
			</div>
			<br class="clear">
		</div>
	</form>

</div>
