=== HWP Previews ===
Contributors: colin-murphy, joefusco, thdespou, ahuseyn, wpengine
Tags: GraphQL, Headless, Previews, WPGraphQL, React, Rest
Requires at least: 6.0
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 0.0.14
License: GPL-2.0
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Headless Previews solution for WordPress: fully configurable preview URLs via the settings page which is framework agnostic.

== Description ==

**HWP Previews** is a robust and extensible WordPress plugin that centralizes all preview configurations into a user-friendly settings interface. It enables seamless preview functionality for headless WordPress applications, allowing content creators to preview their changes in the frontend application before publishing.

In traditional WordPress, previewing content is straightforward: clicking the "Preview" button shows you a draft version of your post on the same WordPress site. However, in headless WordPress architectures, where the front-end is decoupled from WordPress, this simple mechanism breaks down. HWP Previews bridges this gap, providing a centralized, framework-agnostic solution to preview management.

= Key Features =

**Framework Agnostic**
* Works with any front-end framework (Next.js, Nuxt, React, Vue, etc.)
* Supports any data-fetching method (WPGraphQL, REST API, or custom endpoints)
* No vendor lock-in

**Per Post Type Configuration**
* Enable or disable previews for each public post type independently
* Define custom URL templates with dynamic parameters
* Choose between iframe or new tab preview modes
* Allow draft posts as parents for hierarchical types

**Dynamic URL Templates**
* Use placeholder tokens to build context-rich preview URLs
* Available tokens: `{ID}`, `{author_ID}`, `{status}`, `{slug}`, `{parent_ID}`, `{type}`, `{template}`
* Build flexible preview URLs that pass exactly the data your front-end needs

**Extensible Architecture**
* Extend through WordPress hooks and filters
* Add custom parameters and modify settings
* Integrate with other plugins seamlessly

**Faust.js Integration**
* Automatic integration with Faust.js that pre-configures preview URLs
* Removes conflicts while maintaining existing workflows
* Works alongside FaustWP plugin

= Use Cases =

* **Headless CMS**: Preview content in your decoupled front-end before publishing
* **Multi-site Management**: Configure different preview URLs for different post types
* **Custom Post Types**: Set up previews for any custom post type
* **Development Workflow**: Test content changes in staging environments
* **Client Presentations**: Show draft content to clients before going live

= Requirements =

* WordPress 6.0 or higher
* PHP 7.4 or higher

= Documentation =

For detailed documentation, guides, and examples, visit the [GitHub repository](https://github.com/wpengine/hwptoolkit/tree/main/plugins/hwp-previews).

== Installation ==

1. Upload the plugin files to `/wp-content/plugins/hwp-previews/`, or install the plugin through the WordPress plugins screen directly
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Navigate to Settings -> HWP Previews in the WordPress admin to configure settings
4. Enable previews for your desired post types and set preview URL templates

== Frequently Asked Questions ==

= Is this plugin production-ready? =

Yes. We recommend thorough testing on a local or staging before deploying to production.

= Does this work with my front-end framework? =

Yes! HWP Previews is framework-agnostic and works with Next.js, Nuxt, React, Vue, or any other front-end framework. You just need to set up a preview endpoint in your application.

= Can I use this with WPGraphQL? =

Absolutely! The plugin works with both WPGraphQL and REST API, or any custom data-fetching method you prefer.

= Does this work with Faust.js? =

Yes, HWP Previews automatically integrates with Faust.js and pre-configures settings to match Faust's preview system.

= How do I set up the preview URL? =

Go to Settings -> HWP Previews, select the post type tab, enable previews, and enter your front-end preview endpoint URL using dynamic parameters like `{ID}` and `{slug}`.

= Can I customize the preview behavior? =

Yes, the plugin provides extensive hooks and filters to customize preview behavior, add custom parameters, and integrate with other systems.

= Where can I find documentation? =

For detailed usage instructions, developer references, and examples, please visit the [Documentation](https://github.com/wpengine/hwptoolkit/blob/main/docs/plugins/hwp-previews/index.md) on GitHub.

== Support ==

For support, feature requests, or bug reports, please visit our [GitHub issues page](https://github.com/wpengine/hwptoolkit/issues).

== Changelog ==
