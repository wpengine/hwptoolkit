# Headless WordPress Preview Plugin

## Overview

The Headless WordPress Preview Plugin (HWP Previews) enables seamless preview functionality for headless WordPress implementations. It solves the common challenge of previewing unpublished content when using WordPress as a headless CMS with a separate frontend.
**THE PLUGIN AIMS TO BE VERY MUCH CONFIGURABLE APPROACH FOR THE PREVIEW FUNCTIONALITY, SO IT CAN BE USED WITH ANY FE Framework.**

## Features

- **Preview in iFrame**: Display previews within WordPress admin in an iFrame, with JWT token authentication that bypasses typical authentication requirements. This allows you to see your frontend preview directly within WordPress.
- **Filtered Preview URLs**: Alternatively, you can choose to only filter the preview URLs in the default WordPress places and view previews in new tabs without the iFrame approach.
- **Unique Post Slug Management**: Forces unique slugs for specifically configured post types and post statuses, ensuring consistency across all content states (published, draft, pending, etc.).
- **Post Status Parent Support**: Enables configured post statuses of specified post types to be available as parent options in the WordPress admin, allowing for proper hierarchy construction with unpublished content.
- **Draft Route Support**: Provides special handling for Next.js or Nuxt draft mode by allowing configuration of a draft API path that gets concatenated to the preview URL.
- **Token-based Authentication**: Secures preview links with JWT tokens to ensure only authorized users can access unpublished content.
- **REST API Token Verification**: Validates token authenticity for preview REST requests.
- **Configurable Parameters**: Customize URL parameters used for previews.

## Requirements

- WordPress 5.7+
- PHP 7.4+

## Configuration

### Settings

- **Preview URL**: Set the base URL of your headless frontend
- **Token Secret**: Set a secure string to use for JWT token generation
- **Post Types**: Select which post types should have preview functionality
- **Post Statuses**: Select which post statuses should be previewable
- **Enable Unique Post Slug** (`ENABLE_UNIQUE_POST_SLUG`): Forces unique slug generation for the configured post types and statuses (default: enabled)
- **Enable Post Statuses as Parent** (`ENABLE_POST_STATUSES_AS_PARENT`): Allows selecting unpublished content as parent posts in WordPress admin for the configured post types (default: enabled)
- **Generate Preview Links** (`GENERATE_PREVIEW_LINKS`): Replaces WordPress preview links with headless preview URLs (default: disabled)
- **Token Auth Enabled** (`TOKEN_AUTH_ENABLED`): Secures previews with JWT token-based authentication (default: enabled)
- **Preview in iFrame** (`PREVIEW_IN_IFRAME`): Displays previews in an iFrame within WordPress admin (default: enabled)
- **REST Token Verification** (`REST_TOKEN_VERIFICATION`): Enables token verification for REST API endpoints (default: enabled)
- **Draft Route** (`DRAFT_ROUTE`): Specify a custom route for draft content that gets concatenated to the preview URL. Useful for Next.js or Nuxt draft mode APIs (e.g., `/api/preview` or `/api/draft`)
- **Generate Preview Token** (`GENERATE_PREVIEW_TOKEN`): Controls whether JWT tokens are generated for preview URLs (default: enabled)
- **Preview Parameter Names** (`PREVIEW_PARAMETER_NAMES`): Customize the URL parameter names if needed

## How It Works

### iFrame Preview Mode
1. When enabled, this feature intercepts the standard WordPress preview functionality
2. Instead of the default WordPress preview, the plugin renders an iFrame containing your frontend
3. The iFrame URL includes a JWT token that authenticates the user, bypassing normal frontend authentication
4. This allows viewing the frontend preview directly within WordPress admin

### Alternative: URL Filtering Approach
1. If you prefer not to use the iFrame approach, you can enable just the URL filtering functionality
2. This replaces the standard WordPress preview links with links to your headless frontend
3. Clicking preview will open your frontend in a new tab instead of within an iFrame

### Unique Slug Management
1. When enabled, the plugin ensures that slugs are unique across all configured post types and statuses
2. This prevents conflicts that can occur when drafts and published content share the same slug
3. Only applies to the post types and statuses you specify in settings

### Post Status Parent Support
1. By default, WordPress only allows published content to be selected as parent content
2. This feature enables drafts and other unpublished content to appear in parent selection dropdowns
3. Only applies to the post types and statuses configured in settings

### Draft Route Support
1. For frameworks like Next.js or Nuxt that have special draft mode APIs
2. The plugin can append a configured draft route to the preview URL
3. This activates the draft mode in your frontend framework

### Token Authentication
1. The plugin generates JWT tokens for secure preview access
2. These tokens can be verified by your frontend to ensure only authorized users can see unpublished content
3. Includes a verification endpoint at `/wp-json/hwp-previews/v1/verify-preview-token?token=<string>` - something that can help FE to validate preview requester

## Customizing Templates

You can customize the preview templates by filtering `hwp_previews_template_dir_path`:

```php
add_filter('hwp_previews_template_dir_path', function($path) {
  return get_stylesheet_directory() . '/templates';
});
```

## Filters

- `hwp_preview_args`: Allows modifying preview URL parameters
- `hwp_previews_template_dir_path`: Change the template directory for preview templates - completely 
- `hwp_previews_header_name`
- `hwp_previews_header_args`

We can provide more filters if needed.

## Actions:
- `hwp_previews_before_get_header`
- `hwp_previews_after_get_header`
- `hwp_previews_before_get_footer`
- `hwp_previews_after_get_footer`

We can provide more actions if needed.
