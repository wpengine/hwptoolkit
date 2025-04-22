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
  - Includes a SQL dump of WP + Gravity Forms data and a zipped uploads folder so you can dive right in.

## Project Structure

```
├── components/           # Vue form-field components & barrel file
├── composables/         # useGravityForm.js & useFormFields.js
├── pages/              # Nuxt page (index.vue) that renders the form
├── wp-env/
│   ├── db/
│   │   └── database.sql # WordPress + Gravity Forms schema & data
│   ├── setup/
│   │   └── .htaccess   # CORS + pretty-permalinks for wp-env
│   └── uploads.zip     # wp-content/uploads media files
├── .wp-env.json        # @wordpress/env configuration
├── package.json        # wp-env + Nuxt dev scripts
└── nuxt.config.ts      # Nuxt application config
```

## Running the Example with wp-env

### Prerequisites

- Node.js (v18+ recommended)
- pnpm or npm/yarn
- Docker (so that @wordpress/env can spin up the WP container)

### Setup Repository and Install

```bash
git clone https://github.com/your-org/nuxt-gravityforms-example.git
cd nuxt-gravityforms-example
pnpm install

echo "NUXT_PUBLIC_WORDPRESS_URL=http://localhost:8888" > .env
```

### Quick Start

1. Unzip uploads, start WP, import DB, then launch Nuxt:

   ```bash
   pnpm example:build
   ```

2. Or run steps separately:

   ```bash
   # Start WP
   pnpm wp:start

   # Import DB
   pnpm wp:db:import

   # Unzip uploads
   pnpm wp:images:unzip

   # Start Nuxt dev server
   pnpm dev
   ```

By the end, you will have:

- WordPress Admin: http://localhost:8888/wp-admin/
  - user: admin / pass: password
- Nuxt Front End: http://localhost:3000/

## Scripts

| Command                | Description                                                      |
| ---------------------- | ---------------------------------------------------------------- |
| `pnpm example:build`   | Unzip media → start WP env → import DB → launch Nuxt dev server  |
| `pnpm example:start`   | Start WP env, then Nuxt dev server                               |
| `pnpm example:stop`    | Stop the WordPress environment (wp-env stop)                     |
| `pnpm example:prune`   | Destroy & rebuild the WP environment, then restart—all in one go |
| `pnpm wp:start`        | @wordpress/env start (launches PHP/MySQL container with WP)      |
| `pnpm wp:stop`         | @wordpress/env stop                                              |
| `pnpm wp:db:import`    | Import the SQL dump into the running WP container                |
| `pnpm wp:db:export`    | Export the current WP database back to wp-env/db/database.sql    |
| `pnpm wp:images:unzip` | Clear & unzip wp-env/uploads.zip → populates wp-content/uploads  |
| `pnpm dev`             | Start the Nuxt 3 development server on port 3000                 |

> **Tip:** You can also run any arbitrary WP-CLI command inside the container via `pnpm wp:cli -- <wp-cli-command>`

## Database Access

If you need direct database access (phpMyAdmin), add a `"phpmyadminPort": 11111` entry to your `.wp-env.json` and then navigate to http://localhost:11111.
