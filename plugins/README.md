# HWP Toolkit Plugins

WordPress plugins for the Headless WordPress Toolkit. Each plugin is paired with an NPM package.

## Plugins

| Plugin | Description |
|--------|-------------|
| [`hwp-previews`](./hwp-previews/README.md) | Headless Previews solution for WordPress: fully configurable preview URLs via the settings page which is framework agnostic. |
| [`wpgraphql-webhooks`](./wpgraphql-webhooks/README.md) | A WordPress plugin that extends [WPGraphQL](https://www.wpgraphql.com/) to support webhook subscriptions and dispatching for headless WordPress environments. This allows external applications to listen and respond to WordPress content changes through GraphQL-driven webhook events. |


## Install

You can install HWP Toolkit plugins in two ways:

- **Using Composer:** Follow the [step-by-step guide](../docs/how-to/install-toolkit-plugins/index.md) to install and activate plugins in your WordPress environment.
- **Manual Download:** Visit the [Releases](https://github.com/wpengine/hwptoolkit/releases) page to manually download the plugin files and upload them to your WordPress site.


>[!NOTE]
> The main composer packages file [composer-packages.json](composer-packages.json) is automatically updated as part our release workflow.

## Contributing

If you feel like something is missing or you want to add documentation, we encourage you to contribute! Please check out our [Contributing Guide](https://github.com/wpengine/hwptoolkit/blob/main/CONTRIBUTING.md) for more details.
