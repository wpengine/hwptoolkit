<?php

namespace HWP\Previews\Settings;

/**
 * TODO: The class is not final version and should be improved: both code and logic!
 */
class Headless_Preview_Settings {

	/**
	 * Option name in the database
	 */
	const OPTION_NAME = 'headless_preview_settings';

	public const GENERATE_PREVIEW_LINKS = 'generate_preview_links';
	public const ENABLE_UNIQUE_POST_SLUG = 'enable_unique_post_slug';
	public const ENABLE_POST_STATUSES_AS_PARENT = 'enable_post_statuses_as_parent';
	public const PREVIEW_URL = 'preview_url';
	public const GENERATE_PREVIEW_TOKEN = 'generate_preview_token';
	public const TOKEN_AUTH_ENABLED = 'token_auth_enabled';
	public const PREVIEW_IN_IFRAME = 'preview_in_iframe';
	public const TOKEN_SECRET = 'token_secret';
	public const PREVIEW_PARAMETER_NAMES = 'preview_parameter_names';
	public const POST_TYPES = 'post_types';
	public const POST_STATUSES = 'post_statuses';
	public const DRAFT_ROUTE = 'draft_route';
	public const REST_TOKEN_VERIFICATION = 'rest_token_verification';

	/**
	 * Default settings
	 */
	private $defaults = [
		self::GENERATE_PREVIEW_LINKS         => false,
		self::PREVIEW_URL                    => 'http://localhost:3000',
		self::ENABLE_UNIQUE_POST_SLUG        => true,
		self::GENERATE_PREVIEW_TOKEN         => true,
		self::ENABLE_POST_STATUSES_AS_PARENT => true,
		self::TOKEN_AUTH_ENABLED             => true,
		self::PREVIEW_IN_IFRAME              => true,
		self::TOKEN_SECRET                   => '',
		self::PREVIEW_PARAMETER_NAMES        => [
			'preview'        => 'preview',
			'token'          => 'token',
			'post_slug'      => 'slug',
			'post_id'        => 'p',
			'post_type'      => 'type',
			'post_uri'       => 'uri',
			'graphql_single' => 'gql'
		],
		self::POST_TYPES                     => [ 'post', 'page' ],
		self::POST_STATUSES                  => [ 'future', 'draft', 'pending', 'private', 'publish' ],
		self::DRAFT_ROUTE                    => '',
		self::REST_TOKEN_VERIFICATION        => true
	];

	/**
	 * Settings
	 */
	private $settings;

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'admin_menu', [ $this, 'add_settings_page' ] );
		add_action( 'admin_init', [ $this, 'register_settings' ] );

		$this->settings = get_option( self::OPTION_NAME, $this->defaults );
	}

	/**
	 * Add settings page to WP Admin menu
	 */
	public function add_settings_page() {
		add_options_page(
			'Headless Preview Settings',
			'Headless Preview',
			'manage_options',
			'headless-preview',
			[ $this, 'render_settings_page' ]
		);
	}

	/**
	 * Register the settings
	 */
	public function register_settings() {
		register_setting(
			'headless-preview',
			self::OPTION_NAME,
			[ $this, 'sanitize_settings' ]
		);

		// General Settings Section
		add_settings_section(
			'general_settings',
			'General Settings',
			[ $this, 'render_general_section' ],
			'headless-preview'
		);

		// Preview Settings Section
		add_settings_section(
			'preview_settings',
			'Preview Settings',
			[ $this, 'render_preview_section' ],
			'headless-preview'
		);

		// Authentication Settings Section
		add_settings_section(
			'auth_settings',
			'Authentication Settings',
			[ $this, 'render_auth_section' ],
			'headless-preview'
		);

		// URL Parameters Settings Section
		add_settings_section(
			'url_params_settings',
			'URL Parameters',
			[ $this, 'render_url_params_section' ],
			'headless-preview'
		);

		// Post Types and Statuses Section
		add_settings_section(
			'post_settings',
			'Post Types and Statuses',
			[ $this, 'render_post_section' ],
			'headless-preview'
		);

		// REST API Settings Section
		add_settings_section(
			'rest_settings',
			'REST API Settings',
			[ $this, 'render_rest_section' ],
			'headless-preview'
		);

		// General Settings Fields
		add_settings_field(
			'preview_url',
			'Headless Frontend URL',
			[ $this, 'render_preview_url_field' ],
			'headless-preview',
			'general_settings'
		);

		add_settings_field(
			'draft_route',
			'Draft Route',
			[ $this, 'render_draft_route_field' ],
			'headless-preview',
			'general_settings'
		);

		// Preview Settings Fields
		add_settings_field(
			'generate_preview_links',
			'Generate Preview Links',
			[ $this, 'render_generate_preview_links_field' ],
			'headless-preview',
			'preview_settings'
		);

		add_settings_field(
			'preview_in_iframe',
			'Preview in iFrame',
			[ $this, 'render_preview_in_iframe_field' ],
			'headless-preview',
			'preview_settings'
		);

		// Authentication Settings Fields
		add_settings_field(
			'generate_preview_token',
			'Generate Preview Token',
			[ $this, 'render_generate_preview_token_field' ],
			'headless-preview',
			'auth_settings'
		);

		add_settings_field(
			'token_auth_enabled',
			'Enable Token Authentication',
			[ $this, 'render_token_auth_enabled_field' ],
			'headless-preview',
			'auth_settings'
		);

		add_settings_field(
			'token_secret',
			'Token Secret',
			[ $this, 'render_token_secret_field' ],
			'headless-preview',
			'auth_settings'
		);

		// URL Parameters Fields
		add_settings_field(
			'preview_parameter_names',
			'Parameter Names',
			[ $this, 'render_preview_parameter_names_field' ],
			'headless-preview',
			'url_params_settings'
		);

		// Post Types and Statuses Fields
		add_settings_field(
			'post_types',
			'Post Types',
			[ $this, 'render_post_types_field' ],
			'headless-preview',
			'post_settings'
		);

		add_settings_field(
			'post_statuses',
			'Post Statuses',
			[ $this, 'render_post_statuses_field' ],
			'headless-preview',
			'post_settings'
		);

		add_settings_field(
			'enable_unique_post_slug',
			'Enable Unique Post Slug',
			[ $this, 'render_enable_unique_post_slug_field' ],
			'headless-preview',
			'post_settings'
		);

		add_settings_field(
			'enable_post_statuses_as_parent',
			'Enable Post Statuses as Parent',
			[ $this, 'render_enable_post_statuses_as_parent_field' ],
			'headless-preview',
			'post_settings'
		);

		// REST API Fields
		add_settings_field(
			'rest_token_verification',
			'Enable REST Token Verification',
			[ $this, 'render_rest_token_verification_field' ],
			'headless-preview',
			'rest_settings'
		);
	}

	/**
	 * Render the General section description
	 */
	public function render_general_section() {
		echo '<p>Basic settings for your headless frontend configuration.</p>';
	}

	/**
	 * Render the Preview section description
	 */
	public function render_preview_section() {
		echo '<p>Configure how content previews are generated and displayed.</p>';
	}

	/**
	 * Render the Authentication section description
	 */
	public function render_auth_section() {
		echo '<p>Settings related to token generation and authentication for previews.</p>';
	}

	/**
	 * Render the URL Parameters section description
	 */
	public function render_url_params_section() {
		echo '<p>Customize the parameters used in preview URLs. <strong>Leave a field empty to omit that parameter.</strong></p>';
	}

	/**
	 * Render the Post Types and Statuses section description
	 */
	public function render_post_section() {
		echo '<p>Configure which post types and statuses should use the headless preview functionality.</p>';
	}

	/**
	 * Render the REST API section description
	 */
	public function render_rest_section() {
		echo '<p>Settings for REST API integration for token verification.</p>';
	}

	/**
	 * Render the Generate Preview Links field
	 */
	public function render_generate_preview_links_field() {
		$value = isset( $this->settings['generate_preview_links'] ) ? $this->settings['generate_preview_links'] : false;
		?>
        <label>
            <input type="checkbox"
                   name="<?php echo self::OPTION_NAME; ?>[generate_preview_links]" <?php checked( $value, true ); ?> />
            Enable this to replace default WordPress preview links with generated headless frontend preview links
        </label>
        <p class="description">
            When enabled, WordPress's default preview links will be replaced with links to your headless frontend.
            Should be disabled by default for compatibility with standard WordPress workflows.
        </p>
		<?php
	}

	/**
	 * Render the Preview URL field
	 */
	public function render_preview_url_field() {
		$value = isset( $this->settings['preview_url'] ) ? $this->settings['preview_url'] : 'http://localhost:3000';
		?>
        <input type="url" name="<?php echo self::OPTION_NAME; ?>[preview_url]" value="<?php echo esc_url( $value ); ?>"
               class="regular-text"/>
        <p class="description">
            The base URL of your headless frontend (e.g., Next.js or Nuxt application).
            For local development, use something like 'http://localhost:3000'.
            For production, use your actual domain like 'https://example.com'.
        </p>
		<?php
	}

	/**
	 * Render the Generate Preview Token field
	 */
	public function render_generate_preview_token_field() {
		$value = isset( $this->settings['generate_preview_token'] ) ? $this->settings['generate_preview_token'] : true;
		?>
        <label>
            <input type="checkbox"
                   name="<?php echo self::OPTION_NAME; ?>[generate_preview_token]" <?php checked( $value, true ); ?> />
            Generate security tokens for preview URLs
        </label>
        <p class="description">
            When enabled, a JWT token will be included in preview URLs for security purposes.
	        If AUTH via TOKEN is enabled - it might be used for auth.
	        Also it might be used for the FE validation of the preview requester.
            This helps secure your preview content from unauthorized access.
        </p>
		<?php
	}

	/**
	 * Render the Token Auth Enabled field
	 */
	public function render_token_auth_enabled_field() {
		$value = isset( $this->settings['token_auth_enabled'] ) ? $this->settings['token_auth_enabled'] : true;
		?>
        <label>
            <input type="checkbox"
                   name="<?php echo self::OPTION_NAME; ?>[token_auth_enabled]" <?php checked( $value, true ); ?> />
            Bypass authentication for iframe previews
        </label>
        <p class="description">
            When enabled, authentication is bypassed for iframe previews.
            This setting only works when Preview in iFrame is enabled.
            Useful for streamlining the preview experience within the WordPress admin.
        </p>
		<?php
	}

	/**
	 * Render the Preview in iFrame field
	 */
	public function render_preview_in_iframe_field() {
		$value = isset( $this->settings['preview_in_iframe'] ) ? $this->settings['preview_in_iframe'] : true;
		?>
        <label>
            <input type="checkbox"
                   name="<?php echo self::OPTION_NAME; ?>[preview_in_iframe]" <?php checked( $value, true ); ?> />
            Display previews within an iframe in the WordPress admin
        </label>
        <p class="description">
            When enabled, previews will be displayed in an iframe within the WordPress admin interface,
            rather than opening in a new tab. This disables preview link generation as it uses a custom template.
            Provides a more seamless editing experience within WordPress.
        </p>
		<?php
	}

	/**
	 * Render the Token Secret field
	 */
	public function render_token_secret_field() {
		$value = isset( $this->settings['token_secret'] ) ? $this->settings['token_secret'] : '';
		?>
        <input type="password" name="<?php echo self::OPTION_NAME; ?>[token_secret]"
               value="<?php echo esc_attr( $value ); ?>" class="regular-text"/>
        <p class="description">
            A secret key used to sign JWT tokens. Should be kept secure and not shared.
            Changing this will invalidate all existing preview tokens.
            For maximum security, use a long, random string of characters.
        </p>
        <button type="button" class="button button-secondary generate-secret">Generate Secure Secret</button>
        <script>
            jQuery(document).ready(function ($) {
                $('.generate-secret').on('click', function () {
                    // Generate a random 64-character string
                    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*()-_=+';
                    let secret = '';
                    for (let i = 0; i < 64; i++) {
                        secret += chars.charAt(Math.floor(Math.random() * chars.length));
                    }
                    $('input[name="<?php echo self::OPTION_NAME; ?>[token_secret]"]').val(secret);
                });
            });
        </script>
		<?php
	}

	/**
	 * Render the Preview Parameter Names field
	 */
	public function render_preview_parameter_names_field() {
		$parameter_names = isset( $this->settings['preview_parameter_names'] ) ? $this->settings['preview_parameter_names'] : [
			'preview'        => 'preview',
			'token'          => 'token',
			'post_slug'      => 'slug',
			'post_id'        => 'p',
			'post_type'      => 'type',
			'post_uri'       => 'uri',
			'graphql_single' => 'gql'
		];
		?>
        <table class="form-table" style="max-width: 600px;">
            <tr>
                <th>Parameter</th>
                <th>Name in URL</th>
                <th>Description</th>
            </tr>
            <tr>
                <td>Preview</td>
                <td>
                    <input type="text" name="<?php echo self::OPTION_NAME; ?>[preview_parameter_names][preview]"
                           value="<?php echo esc_attr( $parameter_names['preview'] ?? '' ); ?>"/>
                </td>
                <td>Indicates this is a preview request</td>
            </tr>
            <tr>
                <td>Token</td>
                <td>
                    <input type="text" name="<?php echo self::OPTION_NAME; ?>[preview_parameter_names][token]"
                           value="<?php echo esc_attr( $parameter_names['token'] ?? '' ); ?>"/>
                </td>
                <td>Authentication token parameter</td>
            </tr>
            <tr>
                <td>Post Slug</td>
                <td>
                    <input type="text" name="<?php echo self::OPTION_NAME; ?>[preview_parameter_names][post_slug]"
                           value="<?php echo esc_attr( $parameter_names['post_slug'] ?? '' ); ?>"/>
                </td>
                <td>The post's slug identifier</td>
            </tr>
            <tr>
                <td>Post ID</td>
                <td>
                    <input type="text" name="<?php echo self::OPTION_NAME; ?>[preview_parameter_names][post_id]"
                           value="<?php echo esc_attr( $parameter_names['post_id'] ?? '' ); ?>"/>
                </td>
                <td>The post's numeric ID</td>
            </tr>
            <tr>
                <td>Post Type</td>
                <td>
                    <input type="text" name="<?php echo self::OPTION_NAME; ?>[preview_parameter_names][post_type]"
                           value="<?php echo esc_attr( $parameter_names['post_type'] ?? '' ); ?>"/>
                </td>
                <td>The post's content type</td>
            </tr>
            <tr>
                <td>Post URI</td>
                <td>
                    <input type="text" name="<?php echo self::OPTION_NAME; ?>[preview_parameter_names][post_uri]"
                           value="<?php echo esc_attr( $parameter_names['post_uri'] ?? '' ); ?>"/>
                </td>
                <td>The post's full URI path</td>
            </tr>
            <tr>
                <td>GraphQL Single</td>
                <td>
                    <input type="text" name="<?php echo self::OPTION_NAME; ?>[preview_parameter_names][graphql_single]"
                           value="<?php echo esc_attr( $parameter_names['graphql_single'] ?? '' ); ?>"/>
                </td>
                <td>GraphQL single identifier (if using GraphQL)</td>
            </tr>
        </table>
        <p class="description">
	        Customize the parameter names used in preview URLs. <strong>Leave a field empty to omit that parameter from the URL.</strong>
            Changes will affect how your frontend receives and processes preview requests.
        </p>
		<?php
	}

	/**
	 * Render the Draft Route field
	 */
	public function render_draft_route_field() {
		$value = isset( $this->settings['draft_route'] ) ? $this->settings['draft_route'] : '';
		?>
        <input type="text" name="<?php echo self::OPTION_NAME; ?>[draft_route]"
               value="<?php echo esc_attr( $value ); ?>" class="regular-text"/>
        <p class="description">
            Optional path to append to the frontend URL for draft previews (e.g., 'api/draft' for Next.js).
            This route is appended to your frontend URL for handling draft content.
            For Next.js Draft Mode, use something like 'api/draft' or 'api/preview'.
            For Nuxt, it depends on your preview setup. Leave empty if not using a special draft route.
        </p>
		<?php
	}

	/**
	 * Render the Post Types field
	 */
	public function render_post_types_field() {
		$selected_post_types = isset( $this->settings['post_types'] ) ? $this->settings['post_types'] : [
			'post',
			'page'
		];
		$post_types          = get_post_types( [ 'public' => true ], 'objects' );
		?>
        <div style="max-height: 200px; overflow-y: auto; padding: 10px; border: 1px solid #ccc;">
			<?php foreach ( $post_types as $post_type ) :
				if ( $post_type->name === 'attachment' ) {
					continue;
				}
				?>
                <label style="display: block; margin-bottom: 5px;">
                    <input type="checkbox" name="<?php echo self::OPTION_NAME; ?>[post_types][]"
                           value="<?php echo esc_attr( $post_type->name ); ?>"
						<?php checked( in_array( $post_type->name, $selected_post_types ), true ); ?> />
					<?php echo esc_html( $post_type->label ); ?> (<?php echo esc_html( $post_type->name ); ?>)
                </label>
			<?php endforeach; ?>
        </div>
        <p class="description">
            Select which post types should have the headless preview functionality enabled.
            For custom post types, make sure they support the 'custom-fields' feature to store preview data.
        </p>
		<?php
	}

	/**
	 * Render the Post Statuses field
	 */
	public function render_post_statuses_field() {
		$selected_statuses = isset( $this->settings['post_statuses'] ) ? $this->settings['post_statuses'] : [
			'future',
			'draft',
			'pending',
			'private',
			'publish'
		];
		$statuses          = [
			'publish' => 'Published',
			'future'  => 'Scheduled',
			'draft'   => 'Draft',
			'pending' => 'Pending Review',
			'private' => 'Private'
		];
		?>
        <div>
			<?php foreach ( $statuses as $status => $label ) : ?>
                <label style="display: block; margin-bottom: 5px;">
                    <input type="checkbox" name="<?php echo self::OPTION_NAME; ?>[post_statuses][]"
                           value="<?php echo esc_attr( $status ); ?>"
						<?php checked( in_array( $status, $selected_statuses ), true ); ?> />
					<?php echo esc_html( $label ); ?> (<?php echo esc_html( $status ); ?>)
                </label>
			<?php endforeach; ?>
        </div>
        <p class="description">
            Select which post statuses should be supported for previews.
            This determines which types of content can be viewed in preview mode.
            For example, 'draft' allows previewing unpublished content, while 'private' allows previewing
            content that's only visible to logged-in users on the WordPress side.
        </p>
		<?php
	}

	/**
	 * Render the REST Token Verification field
	 */
	public function render_rest_token_verification_field() {
		$value = isset( $this->settings['rest_token_verification'] ) ? $this->settings['rest_token_verification'] : false;
		?>
        <label>
            <input type="checkbox"
                   name="<?php echo self::OPTION_NAME; ?>[rest_token_verification]" <?php checked( $value, true ); ?> />
            Enable REST API endpoint for token verification
        </label>
        <p class="description">
            When enabled, a custom REST API endpoint will be created at:<br>
            <code>/wp-json/hwp-previews/v1/verify-preview-token?token=&lt;string&gt;</code><br>
            This can be used by your frontend to verify that preview tokens are valid before showing preview content.
            This helps implement secure previewing in your headless frontend application.
        </p>
		<?php
	}

	/**
	 * Render the Enable Unique Post Slug field
	 */
	public function render_enable_unique_post_slug_field() {
		$value = isset( $this->settings['enable_unique_post_slug'] ) ? $this->settings['enable_unique_post_slug'] : true;
		?>
        <label>
            <input type="checkbox"
                   name="<?php echo self::OPTION_NAME; ?>[enable_unique_post_slug]" <?php checked( $value, true ); ?> />
            Enable unique post slugs for preview content
        </label>
        <p class="description">
            When enabled, the plugin ensures that post slugs remain unique across all post types and statuses in the
            preview context.
            This is important for proper URL generation and navigation in your headless frontend, especially when
            dealing with
            draft content that might have duplicate slugs. Enabling this setting helps prevent URL conflicts and ensures
            each piece of content has a unique identifier in your frontend application.
        </p>
		<?php
	}

	/**
	 * Render the Enable Post Statuses as Parent field
	 */
	public function render_enable_post_statuses_as_parent_field() {
		$value = isset( $this->settings['enable_post_statuses_as_parent'] ) ? $this->settings['enable_post_statuses_as_parent'] : true;
		?>
        <label>
            <input type="checkbox"
                   name="<?php echo self::OPTION_NAME; ?>[enable_post_statuses_as_parent]" <?php checked( $value, true ); ?> />
            Enable posts with different statuses as parent posts
        </label>
        <p class="description">
            When enabled, posts with non-published statuses (like drafts or pending) can be used as parent posts in
            hierarchical structures.
            This is particularly useful for previewing complex content structures where parent-child relationships
            exist, and you need to preview
            changes to the hierarchy before publishing. This setting allows you to create and preview nested page
            structures where parent pages
            may still be in draft or other non-published states.
        </p>
		<?php
	}

	/**
	 * Sanitize the settings
	 */
	public function sanitize_settings( $input ) {
		$sanitized = [];

		// Boolean fields
		$boolean_fields = [
			'generate_preview_links',
			'generate_preview_token',
			'token_auth_enabled',
			'preview_in_iframe',
			'enable_unique_post_slug',
			'enable_post_statuses_as_parent',
			'rest_token_verification'
		];

		foreach ( $boolean_fields as $field ) {
			$sanitized[ $field ] = isset( $input[ $field ] ) ? (bool) $input[ $field ] : false;
		}

		// String fields
		$sanitized['preview_url']  = isset( $input['preview_url'] ) ? esc_url_raw( $input['preview_url'] ) : 'http://localhost:3000';
		$sanitized['token_secret'] = isset( $input['token_secret'] ) ? sanitize_text_field( $input['token_secret'] ) : '';
		$sanitized['draft_route']  = isset( $input['draft_route'] ) ? sanitize_text_field( $input['draft_route'] ) : '';

		// Parameter names
		$sanitized['preview_parameter_names'] = [];
		if ( isset( $input['preview_parameter_names'] ) && is_array( $input['preview_parameter_names'] ) ) {
			foreach ( $input['preview_parameter_names'] as $key => $value ) {
				$sanitized['preview_parameter_names'][ $key ] = sanitize_text_field( $value );
			}
		}

		// Post types
		$sanitized['post_types'] = [];
		if ( isset( $input['post_types'] ) && is_array( $input['post_types'] ) ) {
			$valid_post_types = array_keys( get_post_types( [ 'public' => true ] ) );
			foreach ( $input['post_types'] as $post_type ) {
				if ( in_array( $post_type, $valid_post_types ) ) {
					$sanitized['post_types'][] = $post_type;
				}
			}
		}

		// Post statuses
		$sanitized['post_statuses'] = [];
		if ( isset( $input['post_statuses'] ) && is_array( $input['post_statuses'] ) ) {
			$valid_statuses = [ 'publish', 'future', 'draft', 'pending', 'private' ];
			foreach ( $input['post_statuses'] as $status ) {
				if ( in_array( $status, $valid_statuses ) ) {
					$sanitized['post_statuses'][] = $status;
				}
			}
		}

		return $sanitized;
	}

	/**
	 * Render the settings page
	 */
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            <div class="notice notice-info">
	            <p>
		            <strong>The settings code/UX should be improved!</strong>
	            </p>
                <p>
                    <strong>About Headless Preview:</strong> These settings configure how WordPress previews content
                    on your headless frontend. If you're using WordPress as a headless CMS with a separate frontend
                    (like Next.js, Nuxt, etc.), these options allow you to customize how preview links are generated
                    and authenticated.
                </p>
            </div>
            <form action="options.php" method="post">
				<?php
				settings_fields( 'headless-preview' );
				do_settings_sections( 'headless-preview' );
				submit_button( 'Save Settings' );
				?>
            </form>

            <div style="margin-top: 30px; padding: 20px; background: #f8f8f8; border: 1px solid #ddd;">
                <h2>Usage Information</h2>
                <p>After saving these settings, you may need to:</p>
                <ol>
                    <li>Configure your frontend application to handle preview URLs with the parameters defined above
                    </li>
                    <li>Set up authentication handling in your frontend to validate preview tokens</li>
                    <li>If using iFrame previews, ensure your frontend allows being displayed in an iframe (check
                        X-Frame-Options headers)
                    </li>
                </ol>
            </div>
        </div>
		<?php
	}

	/**
	 * Get a specific setting value
	 */
	public function get_setting( $key, $default = null ) {
		return isset( $this->settings[ $key ] ) ? $this->settings[ $key ] : $default;
	}
}
