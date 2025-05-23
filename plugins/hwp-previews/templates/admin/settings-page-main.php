<?php

declare(strict_types=1);

$hwp_previews_tabs_config = (array) get_query_var( 'hwp_previews_main_page_config' );
$hwp_previews_current_tab = (string) ( $hwp_previews_tabs_config['current_tab'] ?? '' );
$hwp_previews_tabs        = (array) ( $hwp_previews_tabs_config['tabs'] ?? [] );
$hwp_previews_params      = (array) ( $hwp_previews_tabs_config['params'] ?? [] );

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
								settings_fields( \HWP\Previews\Plugin::SETTINGS_GROUP );
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
								<div class="tag-cloud">
									<?php foreach ( $hwp_previews_params as $hwp_previews_param_name => $hwp_previews_param_desc ) : ?>
										<a href="#" class="button button-secondary"
											title="<?php echo esc_attr( $hwp_previews_param_desc ); ?>"
											style="margin:0 5px 10px 0; font-size: 16px" onclick="
												const input = document.querySelector('.hwp-previews-url');
												const linkText = this.textContent.trim();

												// Get cursor position
												const cursorPos = input.selectionStart;

												// Split text at cursor position
												const textBefore = input.value.substring(0, cursorPos);
												const textAfter = input.value.substring(cursorPos);

												// Insert link text at cursor position
												input.value = textBefore + linkText + textAfter;

												// Set cursor position after inserted text
												const newCursorPos = cursorPos + linkText.length;
												input.setSelectionRange(newCursorPos, newCursorPos);

												// Focus the input
												input.focus();">
											<?php echo esc_html( "{{$hwp_previews_param_name}}" ); ?>
										</a>
									<?php endforeach; ?>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<br class="clear">
		</div>
	</form>

</div>
