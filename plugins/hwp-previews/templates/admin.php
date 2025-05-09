<?php

declare(strict_types=1);

$title = (string) ( $args['title'] ?? '' );
$description = (string) ( $args['description'] ?? '' );

$general_setting_fields_option_group = (string) ( $args['hwp_previews_general_group'] ?? '' );
$general_setting_page = (string) ( $args['hwp-previews-general'] ?? '' );

$tabs        = [];
$current_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : key( $tabs );
$post_types_setting_fields_option_group = (string) ( $args['hwp_previews_cpt_group'] ?? '' );
$post_types_setting_page = (string) ( $args['hwp-previews-general'] ?? '' );

?>

<div class="wrap">
	<h1><?php echo esc_html($title); ?></h1>
	<h2><?php echo esc_html($description); ?></h2>

	<form method="post" action="options.php">
		<?php
		// General
		settings_fields( $general_setting_fields_option_group );
		do_settings_sections( $general_setting_page );
		?>
		<hr/>
		<h2 class="nav-tab-wrapper">
			<?php foreach ( $tabs as $tab_key => $tab_label ) : ?>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=hwp-previews&tab=' . $tab_key ) ); ?>"
				   class="nav-tab <?php echo esc_attr( $current_tab === $tab_key ? 'nav-tab-active' : '' ); ?>">
					<?php echo esc_html( $tab_label ); ?>
				</a>
			<?php endforeach; ?>
		</h2>

</div>
