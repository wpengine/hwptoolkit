<?php

declare( strict_types=1 );

namespace HWP\PostPreviews\Admin;

// Note: this is a POC and needs to be tidied up
class Settings {

    public const OPTION_NAME = 'hwp_post_previews';
    public const SETTINGS_NAME = 'hwp_post_previews_settings';

	public function init() {
		add_action( 'admin_menu', [ $this, 'add_settings_page' ] );
		add_action( 'admin_init', [ $this, 'register_settings' ] );
	}

	public function add_settings_page() {
		add_options_page(
			'HWP Post Previews',
			'HWP Post Previews',
			'manage_options',
			self::OPTION_NAME,
			[ $this, 'settings_page_html' ]
		);
	}

	public function register_settings() {
		register_setting(
			self::OPTION_NAME,
			self::SETTINGS_NAME
		);

		add_settings_section(
			'hwp_post_previews_section',
			__( 'Settings', 'hwp-post-previews' ),
			null,
			'hwp_post_previews'
		);

		add_settings_field(
			'hwp_post_previews_enabled',
			__( 'Enabled', 'hwp-post-previews' ),
			[ $this, 'render_enabled_input' ],
			'hwp_post_previews',
			'hwp_post_previews_section'
		);

		add_settings_field(
			'hwp_post_previews_frontend_url',
			__( 'Frontend URL', 'hwp-post-previews' ),
			[ $this, 'render_frontend_url_input' ],
			'hwp_post_previews',
			'hwp_post_previews_section'
		);
	}

	public function render_enabled_input() {
		$options = get_option( self::SETTINGS_NAME ) ?: [];
		$value   = $options['hwp_post_previews_enabled'] ?? 0;
		?>
        <input type='checkbox'
               name='hwp_post_previews_settings[hwp_post_previews_enabled]' <?php checked( $value, 1 ); ?> value='1'>
		<?php
	}

	public function render_frontend_url_input() {
		$options = get_option( self::SETTINGS_NAME ) ?: [];
		$value   = $options['hwp_post_previews_frontend_url'] ?? 'http://localhost:3000/api/draft';
		?>
        <input type='text' name='hwp_post_previews_settings[hwp_post_previews_frontend_url]'
               value='<?php echo esc_attr( $value ); ?>'>
		<?php
	}

	public function settings_page_html() {
		?>
        <form action='options.php' method='post'>
            <h2>HWP Post Previews</h2>
			<?php
			settings_fields( self::OPTION_NAME );
			do_settings_sections( self::OPTION_NAME );
			submit_button();
			?>
        </form>
		<?php
	}
}