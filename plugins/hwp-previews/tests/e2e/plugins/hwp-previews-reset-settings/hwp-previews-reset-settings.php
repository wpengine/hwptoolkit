<?php
/**
 * Plugin Name: Reset HWP Previews settings
 * Description: This plugin resets HWP Previews settings on activation. It's only intended to be used for e2e testing purposes.
 */

add_action('init', function() {
  if ($_SERVER['REQUEST_URI'] === '/wp-admin/options-general.php?page=hwp-previews&reset=true') {
    update_option( 'hwp_previews_settings', array() );
  }
});