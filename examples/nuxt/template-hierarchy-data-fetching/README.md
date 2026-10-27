---
title: "Nuxt Template Hierarchy and Data Fetching"
description: "In this example we show how to implement the WP Template Hierarchy and Data Fetching in Nuxt for use with a Headless WordPress backend using WPGraphQL."
---

# Nuxt Data fetching

In this example we show how to implement the WP Template Hierarchy and Data Fetching in Nuxt for use with a Headless WordPress backend using WPGraphQL.

## Getting Started

> [!IMPORTANT]
> Docker Desktop needs to be installed to run WordPress locally.

1. Run `npm run setup` to install dependencies and configure the local WP server.
2. Run `npm run dev` to start the WP server and Nuxt development server.

> [!NOTE]
> When you kill the long running process this will not shutdown the local WP instance, only Nuxt. Run `npm run app:dev` to restart only the front end, or `npm run stop` to stop the local WP server.

## Trouble Shooting

To reset the WP server, run `npm run clean` and confirm "Yes" at the prompt, then run `npm run setup` again.
