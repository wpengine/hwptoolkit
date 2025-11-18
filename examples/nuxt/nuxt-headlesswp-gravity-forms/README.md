---
title: "Example: Gravity Forms in Headless WordPress with Nuxt/Vue"
description: "This example shows you how to wire up a full headless WordPress backend—complete with Gravity Forms, WPGraphQL, and a pre-built \"Questionnaire\" form—alongside a Nuxt 3 front end that dynamically renders your forms using the GraphQL interfaces feature of WPGraphQL for Gravity Forms."
---

# Example: Gravity Forms in Headless WordPress with Nuxt/Vue

This example shows you how to wire up a full headless WordPress backend—complete with Gravity Forms, WPGraphQL, and a pre-built "Questionnaire" form—alongside a Nuxt 3 front end that dynamically renders your forms using the GraphQL interfaces feature of WPGraphQL for Gravity Forms.

## Features

- **Headless WordPress with Gravity Forms**

  - Stand up WP + WPGraphQL + Gravity Forms (and your Questionnaire JSON) in one command.

- **Dynamic form rendering**

  - Use GraphQL interfaces (`GfFieldWithLabelSetting`, `GfFieldWithChoicesSetting`, etc.) plus a single `useFormFields` composable to automatically map any new field type to the correct Vue component.

- **Composable Nuxt 3 front end**

  - Fetch your form schema with `useGravityForm` and render all fields via a dynamic `<component :is="resolveFieldComponent(field)" … />` pattern.

- **Out-of-the-box data**
  - Includes a SQL dump of WP + Gravity Forms data so you can dive right in.

## Project Structure

```

example-app/
├── components/ # Vue form-field components & barrel file
├── composables/ # useGravityForm.js & useFormFields.js
├── pages/ # Nuxt page (index.vue) that renders the form
└── nuxt.config.ts # Nuxt application config
wp-env/
│ ├── db/
│ │ └── database.sql # WordPress + Gravity Forms schema & data
│ └── setup/
│   └── .htaccess # CORS + pretty-permalinks for wp-env
├── .wp-env.json # @wordpress/env configuration
└── package.json # wp-env + Nuxt dev scripts

````

## Running the Example with wp-env

### Prerequisites

- Node.js (v18+ recommended)
- npm
- Docker (so that @wordpress/env can spin up the WP container)

### Setup Repository and Install

```bash
git clone https://github.com/wpengine/hwptoolkit.git
cd examples/nuxt/nuxt-headlesswp-gravity-forms
npm install

echo "NUXT_PUBLIC_WORDPRESS_URL=http://localhost:8888" > example-app/.env
````

### Quick Start

1. Start WP, import DB, then launch Nuxt:

   ```bash
   npm run example:setup
   ```

2. Or run steps separately:

   ```bash
   # Start WP
   npm run wp:start

   # Import DB
   npm run wp:db:import

   # Start Nuxt dev server
   npm run dev
   ```

By the end, you will have:

- WordPress Admin: http://localhost:8888/wp-admin/
  - user: admin / pass: password
- Nuxt Front End: http://localhost:3000/

## Scripts

| Command                   | Description                                                      |
| ------------------------- | ---------------------------------------------------------------- |
| `npm run example:setup`   | Start WP env → import DB → launch Nuxt dev server  |
| `npm run example:start`   | Start WP env, then Nuxt dev server                               |
| `npm run example:stop`    | Stop the WordPress environment (wp-env stop)                     |
| `npm run example:prune`   | Destroy & rebuild the WP environment, then restart—all in one go |
| `npm run wp:start`        | @wordpress/env start (launches PHP/MySQL container with WP)      |
| `npm run wp:stop`         | @wordpress/env stop                                              |
| `npm run wp:db:import`    | Import the SQL dump into the running WP container                |
| `npm run wp:db:export`    | Export the current WP database back to wp-env/db/database.sql    |
| `npm run dev`             | Start the Nuxt 3 development server on port 3000                 |

> **Tip:** You can also run any arbitrary WP-CLI command inside the container via `npm run wp:cli -- <wp-cli-command>`

## Database Access

If you need direct database access (phpMyAdmin), add a `"phpmyadminPort": 11111` entry to your `.wp-env.json` and then navigate to http://localhost:11111.
