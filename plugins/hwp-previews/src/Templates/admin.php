<?php

declare(strict_types=1);

use HWP\Previews\Integration\Faust_Integration;
use HWP\Previews\Preview\Post\Post_Settings_Service;

$hwp_previews_tabs_config       = (array) get_query_var( 'hwp_previews_main_page_config' );
$hwp_previews_current_tab       = (string) ( $hwp_previews_tabs_config['current_tab'] ?? '' );
$hwp_previews_tabs              = (array) ( $hwp_previews_tabs_config['tabs'] ?? [] );
$hwp_previews_params            = (array) ( $hwp_previews_tabs_config['params'] ?? [] );
$hwp_previews_faust_integration = Faust_Integration::init();

?>

<div class="wrap">
	<h1><?php esc_html_e( 'HWP Previews Settings', 'hwp-previews' ); ?></h1>
	<form method="post" action="options.php">
		<h2 class="nav-tab-wrapper">
			<?php
			foreach ( $hwp_previews_tabs as $hwp_previews_tab_key => $hwp_previews_tab_label ) {
				$hwp_previews_tab_url   = admin_url( 'admin.php?page=hwp-previews&tab=' . $hwp_previews_tab_key );
				$hwp_previews_tab_class = add_cssclass( $hwp_previews_current_tab === $hwp_previews_tab_key ? 'nav-tab-active' : '', 'nav-tab' );
				echo '<a href="' . esc_url( $hwp_previews_tab_url ) . '" class="' . esc_attr( $hwp_previews_tab_class ) . '">' . esc_html( $hwp_previews_tab_label ) . '</a>';
			}
			?>
		</h2>

		<div id="poststuff">
			<div id="post-body" class="metabox-holder columns-2">
				<div id="post-body-content">
					<div class="meta-box-sortables ui-sortable">
						<div class="postbox">
							<div class="inside">
								<?php
								// Post Types.
								settings_fields( Post_Settings_Service::get_settings_group() );
								do_settings_sections( 'hwp-previews-' . $hwp_previews_current_tab );
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
							<h2><?php esc_html_e( 'Available Preview URL Parameters', 'hwp-previews' ); ?></h2>
							<div class="inside">
								<div class="hwp-previews-tag-cloud">
									<?php foreach ( $hwp_previews_params as $hwp_previews_param_name => $hwp_previews_param_desc ) : ?>
										<button type="button" class="button button-secondary hwp-previews-insert-tag"
											title="<?php echo esc_attr( $hwp_previews_param_desc ); ?>">
											<?php echo esc_html( "{{$hwp_previews_param_name}}" ); ?>
										</button>
									<?php endforeach; ?>
								</div>
							</div>
						</div>

						<div class="postbox">
							<h2><?php esc_html_e( 'Get Started With HWP Previews', 'hwp-previews' ); ?></h2>
							<div class="inside hwp-previews-docs">

								<ul>
									<li><a href="https://github.com/wpengine/hwptoolkit/tree/main/plugins/hwp-previews#getting-started" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Getting Started', 'hwp-previews' ); ?></a></li>
									<li><a href="https://github.com/wpengine/hwptoolkit/tree/main/plugins/hwp-previews#front-end-integration" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Front-End Integration', 'hwp-previews' ); ?></a></li>
									<?php if ( $hwp_previews_faust_integration->get_faust_enabled() ) : ?>
										<li><a href="https://github.com/wpengine/hwptoolkit/tree/main/plugins/hwp-previews#using-with-faustjs" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Using With Faust.js', 'hwp-previews' ); ?></a></li>
									<?php endif; ?>
									<li><a href="https://github.com/wpengine/hwptoolkit/tree/main/plugins/hwp-previews#extending-the-functionality" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Extending the Functionality', 'hwp-previews' ); ?></a></li>
								</ul>
								<p><?php esc_html_e( 'HWP Previews is part of the HWP Toolkit, our comprehensive suite of tools and examples for headless WordPress.', 'hwp-previews' ); ?></p>
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
