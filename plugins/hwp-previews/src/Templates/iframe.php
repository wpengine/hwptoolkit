<?php
/**
 * Custom Preview Template
 *
 * This template will be used for all post/page previews
 *
 * @package HWP\Previews
 */

declare(strict_types=1);

$hwp_previews_url_template = \HWP\Previews\Preview\Template_Resolver::get_query_variable();

?>

<!DOCTYPE html>
<html <?php language_attributes(); ?>>
	<head>
		<?php wp_head(); ?>
	</head>

	<body style="overflow: hidden;">
		<?php wp_body_open(); ?>

			<div style="position: relative;">
				<iframe
						src="<?php echo esc_url( $hwp_previews_url_template ); ?>"
						class="headless-preview-frame"
						sandbox="allow-scripts allow-same-origin allow-forms"
						referrerpolicy="no-referrer-when-downgrade"
						title="Content Preview"
						style="width: 100%; height: calc(100vh - var(--wp-admin--admin-bar--height)); border: none;">
				</iframe>
			</div>

		<?php wp_footer(); ?>
	</body>
</html>
