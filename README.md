# HWP Toolkit

Headless WordPress Toolkit

> A modern toolkit for building headless WordPress applications. Provides plugins, packages, examples, and best practices for decoupling WordPress into a powerful headless CMS.

## Plugins

| Plugin | Description | Status |
|---|---|---|
| [HWP Previews](./plugins/hwp-previews) | Configure headless previews per post type from one settings screen. | Stable |
| [WPGraphQL Logging](./plugins/wpgraphql-logging) | Log the WPGraphQL request lifecycle to find and fix bottlenecks. | Stable |
| [HWP Documents](./plugins/hwp-documents) | Store your app's JSON documents in WordPress over REST, without silently losing writes. | Stable |
| [WPGraphQL Webhooks](./plugins/wpgraphql-webhooks) | Register webhooks that fire on WordPress events, such as revalidating a front end. | Pre-release |
| [WPGraphQL Debug Extensions](./plugins/wpgraphql-debug-extensions) | Analyse WPGraphQL queries for performance issues such as deep nesting and over-fetching. | Pre-release |

Download plugin zips from [GitHub Releases](https://github.com/wpengine/hwptoolkit/releases), or [install them with Composer](./docs/how-to/install-toolkit-plugins/index.md).

## Packages

| Package | Description | Status |
|---|---|---|
| [@wpengine/hwp-toolbar](./packages/toolbar) | Framework-agnostic toolbar for headless WordPress front ends. | Experimental |

## Examples

[`examples`](./examples) has runnable headless WordPress projects for Next.js, Nuxt, SvelteKit, Astro, Angular, and vanilla JavaScript, each with its own local WordPress environment. Plugin-specific examples live in each plugin's `examples` directory.

## Documentation

[`docs`](./docs) has tutorials, how-to guides, and explanations for headless WordPress and for each plugin.

## Community

To chat with other headless WordPress users and the headless community as a whole, you can join the [WP Engine Developers Discord](https://discord.gg/J2khkF9XYK).

Additionally, if you have questions or ideas, please share them on [GitHub Discussions](https://github.com/wpengine/hwptoolkit/discussions).

## Contributing

There are many ways to [contribute](/CONTRIBUTING.md) to this project.

- [Discuss open issues](https://github.com/wpengine/hwptoolkit/issues) to help define the future of the project.
- [Submit bugs](https://github.com/wpengine/hwptoolkit/issues) and help us verify fixes as they are checked in.
- Review and discuss the [source code changes](https://github.com/wpengine/hwptoolkit/pulls).
- [Contribute bug fixes](/CONTRIBUTING.md)
