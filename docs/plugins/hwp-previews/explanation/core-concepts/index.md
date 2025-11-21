---
title: "Core Concepts"
description: "Deep dive explanations of the HWP Previews plugin's core concepts, architecture, and systems."
---

This article provides an in-depth exploration of the fundamental concepts that power the HWP Previews plugin, covering the template-based URL system that enables framework-agnostic preview generation, the parameter registry that makes URLs dynamic and context-aware, the flexible post type configuration system, solutions for WordPress hierarchical content challenges, and the different preview modes available to content creators.

## Core Architectural Concepts

### 1. Preview URL Templates with Dynamic Parameters

At the heart of HWP Previews is the concept of URL templates. Instead of hardcoding preview URLs, administrators define templates using placeholder tokens that get dynamically replaced with actual post data.

For example, a template like:
```
https://mysite.com/preview?p={ID}&type={type}&status={status}
```

When previewing a draft post with ID 42 of type "page", this becomes:
```
https://mysite.com/preview?p=42&type=page&status=draft
```

This template approach ensures framework agnosticity, whether you're using Next.js with `/api/preview?slug={slug}`, a custom React app with `/preview/{ID}`, or any other front-end architecture. The system generates context-rich URLs that can pass any information your front-end needs to handle previews intelligently, from basic post data to complex hierarchical relationships.

#### The Parameter Registry System

HWP Previews maintains a parameter registry, a centralized collection of available dynamic parameters. Out of the box, it provides:

- `{ID}` – Post ID
- `{author_ID}` – Post author's user ID
- `{status}` – Post status (draft, pending, etc.)
- `{slug}` – Post slug
- `{parent_ID}` – Parent post ID (for hierarchical types)
- `{type}` – Post type slug
- `{template}` – Template filename

This registry is extensible through WordPress filters, allowing developers to add custom parameters (e.g., `{category}`, `{custom_field}`, or `{locale}` for multilingual sites).

### 2. Post Type-Specific Configuration

WordPress sites often have diverse content types (posts, pages, custom post types) each potentially requiring different preview handling. To address this challenge, HWP Previews provides per-post-type configuration.

For each public post type, you can independently configure:
- Whether previews are enabled at all
- The preview URL template
- Whether to display previews in an iframe within the editor
- Whether to allow non-published posts as parents (for hierarchical types)

### 3. Hierarchical Post Types and Parent Status

WordPress supports hierarchical post types (like pages) where posts can have parent-child relationships. By default, WordPress only allows published posts to be parents. This creates a workflow problem: when building a site, you often want to establish page hierarchies while everything is still in draft.

HWP Previews solves this with the "Allow All Statuses as Parent" option. When enabled for a post type, draft, pending, or private posts can serve as parents.

### 4. Iframe vs. New Tab Preview Modes

HWP Previews offers two preview rendering modes, each suited to different workflows:

#### Iframe Mode
When enabled, previews load directly within the WordPress editor in a sandboxed `<iframe>`. This provides an integrated experience, so you can see your changes alongside the preview, similar to the WordPress theme customizer.

#### New Tab Mode
When disabled, clicking "Preview" opens the preview URL in a new browser tab.

### 5. Faust Integration

When Faust.js is detected, HWP Previews automatically configures itself to work seamlessly with Faust's preview system. This provides Faust users with an upgrade path that maintains their existing workflows while gaining access to additional features like iframe mode, custom parameters, and per-post-type control.

The integration removes potential conflicts by disabling Faust's native preview handling and pre-configuring preview URLs to match Faust's expected structure. This **compatibility without compromise** approach minimizes adoption barriers for users with existing Faust investments.

## The Hook System and Extensibility

HWP Previews provides a comprehensive system of WordPress actions and filters for extending functionality.

### Key Extension Points

`hwp_previews_register_parameters`: Add custom dynamic parameters
`hwp_previews_filter_available_post_types`: Control which post types appear in settings
`hwp_previews_filter_available_post_statuses`: Modify available post statuses
`hwp_previews_template_path`: Replace the iframe preview template

## Security Considerations

While HWP Previews focuses on preview URL generation rather than authentication, its design acknowledges security concerns:

1. **No token generation**: The plugin intentionally doesn't handle authentication tokens, that's the front-end's responsibility
2. **URL encoding**: All parameter values are properly encoded to prevent injection attacks
3. **Capability checks**: Admin settings pages require appropriate WordPress capabilities
4. **Nonce verification**: AJAX requests (like dismissing notices) include nonce verification

## Performance Characteristics

HWP Previews is designed for minimal performance impact:

- **Settings caching**: Configuration is cached and only loaded when needed
- **Lazy initialization**: Services instantiate only when actually used
- **Selective hooks**: Post-type-specific hooks only register for enabled post types
- **No frontend overhead**: The plugin only activates for authenticated users in admin/preview contexts

These choices reflect an understanding that preview functionality should never degrade the public site's performance.
