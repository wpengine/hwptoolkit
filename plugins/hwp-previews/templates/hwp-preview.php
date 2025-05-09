<?php
/**
 * Custom Preview Template
 *
 * This template will be used for all post/page previews
 */

use HWP\Previews\Preview\Template\Preview_Template_Resolver;

$preview_url = (string) get_query_var( Preview_Template_Resolver::HWP_PREVIEWS_IFRAME_PREVIEW_URL );

get_header();

?>

    <div style="position: relative; min-height: 100vh;">
        <iframe
                src="<?php echo esc_url( $preview_url ); ?>"
                class="headless-preview-frame"
                sandbox="allow-scripts allow-same-origin allow-forms"
                referrerpolicy="no-referrer-when-downgrade"
                title="Content Preview"
                style="width: 100%; height: 100vh; border: none;">
        </iframe>
    </div>

<?php

get_footer();
