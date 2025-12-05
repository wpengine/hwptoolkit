---
title: "Headless WordPress Previews with Astro"
description: "In this example, we show how to implement **Headless WordPress Previews in Astro** using the **`hwp-previews`** plugin and WPGraphQL. This setup allows content creators to preview draft posts directly in the Astro frontend from the WordPress admin panel. We use **URQL** for all routing and fetching page content."
---

# Example: Headless WordPress Previews with Astro

In this example, we show how to implement **Headless WordPress Previews in Astro** using the **`hwp-previews`** plugin and WPGraphQL. This setup allows content creators to preview draft posts directly in the Astro frontend from the WordPress admin panel. We use **URQL** for all routing and fetching page content.

## Getting Started

> [!IMPORTANT] > **Docker Desktop** needs to be installed to run WordPress locally.

1. Run `npm run example:setup` to install dependencies and configure the local WP server.
2. Run `npm run example:start` to start the WP server and Astro development server.

> [!NOTE]
> When you kill the long running process this will not shutdown the local WP instance, only Astro. You must run `npm run example:stop` to kill the local WP server.

## How to Test Previews

1.  After running `npm run example:start`, navigate to the WordPress Admin at **http://localhost:8888/wp-admin**.
2.  Log in using the credentials specified in the example's `wp-env.json`.
3.  Edit any draft post.
4.  In the editor, click the **"Preview"** button. The post should open in the Astro frontend, displaying the draft content.

## Trouble Shooting

To reset the WP server and re-run setup you can run `npm run example:prune` and confirm "Yes" at any prompts.
