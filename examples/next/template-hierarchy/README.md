# Next.js Template Hierarchy and Data fetching w/URQL Example

In this example we show how to implement the WP Template Hierarchy in Next.js for use with a headless WordPress backend using WPGraphQL. We use URQL for all routing and fetching page content.

## Getting Started

> [!IMPORTANT]
> Docker Desktop needs to be installed to run WordPress locally.

1. Run `npm run example:setup` to install dependencies and configure the local WP server.
2. Run `npm run example:start` to start the WP server and Astro development server.

> [!NOTE]
> When you kill the long running process this will not shutdown the local WP instance, only Next.js. You must run `npm run example:stop` to kill the local WP server.

## Trouble Shooting

To reset the WP server and re-run setup you can run `npm run example:prune` and confirm "Yes" at any prompts.
