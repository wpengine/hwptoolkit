<?php

declare( strict_types = 1 );

$tabs_config = (array) get_query_var( 'hwp_previews_main_page_config' );
$current_tab = (string) ( $tabs_config['current_tab'] ?? '' );
$tabs        = (array) ( $tabs_config['tabs'] ?? [] );
$params	     = (array) ( $tabs_config['params'] ?? [] );

?>

<div class="wrap">
	<h1><?php esc_html_e( 'HWP Previews Settings', 'hwp-previews' ) ?></h1>
	<form method="post" action="options.php">
		<h2 class="nav-tab-wrapper">
			<?php foreach ( $tabs as $tab_key => $tab_label ) {
				$url = admin_url( 'admin.php?page=hwp-previews&tab=' . $tab_key );
				$class = add_cssclass( $current_tab === $tab_key ? 'nav-tab-active' : '', 'nav-tab' );
				echo '<a href="' . esc_url( $url ) . '" class="' . esc_attr( $class ) . '">' . esc_html( $tab_label ) . '</a>';
			} ?>
		</h2>

		<div id="poststuff">
			<div id="post-body" class="metabox-holder columns-2">
				<div id="post-body-content">
					<div class="meta-box-sortables ui-sortable">
						<div class="postbox">
							<div class="inside">
								<?php // Post Types.
									settings_fields( \HWP\Previews\Plugin::SETTINGS_GROUP );
									do_settings_sections( 'hwp-previews-' . $current_tab );
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
							<h2><?php esc_attr_e( 'Available Preview URL Parameters', 'hwp-previews' ); ?></h2>
							<div class="inside">
								<div class="tag-cloud">
									<?php foreach ( $params as $name => $desc ) : ?>
										<a href="#" class="button button-secondary" title="<?php echo esc_attr( $desc ); ?>" style="margin:0 5px 10px 0; font-size: 16px" onclick="
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
											<?php echo esc_html( "{{$name}}" ); ?>
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
