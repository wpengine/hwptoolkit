<?php
/**
 * Custom Preview Template
 *
 * This template will be used for all post/page previews
 */

use HWP\Previews\Preview\Template\Preview_Template_Resolver;

$preview_url = (string) get_query_var( Preview_Template_Resolver::HWP_PREVIEWS_IFRAME_PREVIEW_URL );

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
                        src="<?php echo esc_url( $preview_url ); ?>"
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