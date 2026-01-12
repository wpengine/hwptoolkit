---
title: "Nuxt Template Hierarchy and Data Fetching"
description: "In this example we show how to implement the WP Template Hierarchy and Data Fetching in Nuxt for use with a Headless WordPress backend using WPGraphQL."
---

# Nuxt Data fetching

In this example we show how to implement the WP Template Hierarchy and Data Fetching in Nuxt for use with a Headless WordPress backend using WPGraphQL.

## Getting Started

> [!IMPORTANT]
> Docker Desktop needs to be installed to run WordPress locally.

1. Run `npm run example:setup` to install dependencies and configure the local WP server.
2. Run `npm run example:start` to start the WP server and Nuxt development server.

> [!NOTE]
> When you kill the long running process this will not shutdown the local WP instance, only Nuxt. You must run `npm run example:stop` to kill the local WP server.

## Trouble Shooting

To reset the WP server and re-run setup you can run `npm run example:prune` and confirm "Yes" at any prompts.
