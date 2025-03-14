<?php
/**
 * Custom Preview Template
 *
 * This template will be used for all post/page previews
 */

use HWP\Previews\Preview\Template\Preview_Template_Resolver;

$header_name = apply_filters( 'hwp_previews_header_name', '' ) ?: null;

$preview_url = (string) get_query_var( Preview_Template_Resolver::HWP_PREVIEWS_IFRAME_PREVIEW_URL );

var_dump($preview_url);

do_action( 'hwp_previews_before_get_header' );

get_header( $header_name, apply_filters( 'hwp_previews_header_args', [] ) );

do_action( 'hwp_previews_after_get_header' );

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

$footer_name = apply_filters( 'hwp_previews_footer_name', '' ) ?: null;

do_action( 'hwp_previews_before_get_footer' );

get_footer( $footer_name, apply_filters( 'hwp_previews_footer_args', [] ) );

do_action( 'hwp_previews_after_get_footer' );
