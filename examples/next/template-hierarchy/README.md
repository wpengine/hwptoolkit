---
title: "Next.js Template Hierarchy with URQL"
description: "In this example we show how to implement the WP Template Hierarchy in Next.js for use with a headless WordPress backend using WPGraphQL. We use URQL for all routing and fetching page content."
---

# Next.js Template Hierarchy and Data fetching w/URQL Example

In this example we show how to implement the WP Template Hierarchy in Next.js for use with a headless WordPress backend using WPGraphQL. We use URQL for all routing and fetching page content.

## Getting Started

> [!IMPORTANT]
> Docker Desktop needs to be installed to run WordPress locally.

1. Run `npm run setup` to install dependencies and configure the local WP server.
2. Run `npm run dev` to start the WP server and Astro development server.

> [!NOTE]
> When you kill the long running process this will not shutdown the local WP instance, only Next.js. Run `npm run app:dev` to restart only the front end, or `npm run stop` to stop the local WP server.

## Trouble Shooting

To reset the WP server, run `npm run clean` and confirm "Yes" at the prompt, then run `npm run setup` again.
