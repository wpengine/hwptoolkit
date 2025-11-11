---
title: "Introduction"
description: "Introduction to the Headless WordPress Toolkit, a modern, framework-agnostic collection of plugins and packages for building headless WordPress applications."
---

## What is the Headless WordPress Toolkit?

The Headless WordPress Toolkit is a modern, framework-agnostic toolkit for building headless WordPress applications. It provides a collection of plugins, packages, and examples to help developers make WordPress a better headless CMS.

Our goal is to provide developers with the tools they need to build fast, scalable, and secure headless applications with WordPress, without being tied to a specific frontend framework.

## Plugins

The toolkit includes several WordPress plugins to enhance the headless experience.

| Plugin | Description |
|--------|-------------|
| [hwp-previews](../plugins/hwp-previews/) | Headless Previews solution for WordPress: fully configurable preview URLs via the settings page which is framework agnostic. |
| [wpgraphql-webhooks](../plugins/wpgraphql-webhooks/) | Extends WPGraphQL to support webhook subscriptions and dispatching for headless WordPress environments. |
| [wpgraphql-logging](../plugins/wpgraphql-logging/) | Logging for WPGraphQL requests with granular lifecycle events and Monolog integration. |
| [wpgraphql-debug-extensions](../plugins/wpgraphql-debug-extensions/) | Advanced debugging, performance analysis, and metric collection for WPGraphQL. |

You can find more information about installation in the [plugins documentation](../plugins/README.md).

## Packages

We provide NPM packages that can be used in your frontend applications. All packages use vanilla ES Modules with no build step.

- [@wpengine/hwp-toolbar](../packages/toolbar/) — in active development (not yet published)

> [!NOTE]
> No packages are published to npm yet. These are pre-release and subject to change.

## Examples

This project contains a wide variety of examples demonstrating how to use the Headless WordPress Toolkit with different frameworks like Next.js, Astro, SvelteKit, and Nuxt.

Most examples include a `wp-env` setup, allowing you to fully configure a headless application with a single command.

For a full list of examples and how to run them, please see the [examples documentation](../examples/README.md).

## Contributing

If you feel like something is missing or you want to add documentation, we encourage you to contribute! Please check out our [Contributing Guide](https://github.com/wpengine/hwptoolkit/blob/main/CONTRIBUTING.md) for more details.
