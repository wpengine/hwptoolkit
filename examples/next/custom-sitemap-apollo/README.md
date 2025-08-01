# Example: Create a custom WordPress sitemap with WPGraphQL and Apollo Client

## Overview

This example demonstrates how to generate a custom sitemap in a headless WordPress application using the Next.js framework. The example app fetches data from WordPress using Apollo Client and WPGraphQL. Since WPGraphQL doesn't support sitemaps natively, we are extending it with a custom plugin, which is included in this example as well. This plugin exposes new fields to fetch the sitemap index, with data identical to what's rendered on the native WordPress sitemap. Another field exposed by this plugin allows you to request sitemap subpages by specifying the types and pages. The plugin also adds featured image data, enabling you to create [Image Sitemaps](https://developers.google.com/search/docs/crawling-indexing/sitemaps/image-sitemaps).

The example includes a wp-env setup, which will allow you to build and start this example quickly. With this wp-env setup, you don't need to have a separate WordPress instance or demo data to inspect the example.

## Features

1. Fetching sitemap data with the API allows maximum customizability
2. Custom plugin to extend WPGraphQL with the sitemap feature
3. Plugin uses native WordPress sitemap hooks and methods for security and performance
4. An identical WordPress sitemap structure in the headless setup
5. [Image Sitemaps](https://developers.google.com/search/docs/crawling-indexing/sitemaps/image-sitemaps) implementation
6. Configured WordPress instance with demo data and required plugins, using wp-env
7. Sitemaps for custom post and taxonomy types
8. Permanent redirect of `/sitemap` requests to `/sitemap.xml`, in the `next.config.mjs`

## Screenshots

After following the installation steps, you should have the example sitemap pages as shown in the screenshots below:

|                                                                              |                                                                                      |
| :--------------------------------------------------------------------------: | :----------------------------------------------------------------------------------: |
|  ![index](./screenshots/sitemap-index.png "Sitemap index")<br>Sitemap index  |              ![posts](./screenshots/sitemap-post.png "Posts")<br>Posts               |
| ![categories](./screenshots/sitemap-category.png "Categories")<br>Categories |                ![tags](./screenshots/sitemap-tag.png "Tags")<br>Tags                 |
|          ![users](./screenshots/sitemap-user.png "Users")<br>Users           |               ![page](./screenshots/sitemap-page.png "Pages")<br>Pages               |
| ![cpt](./screenshots/sitemap-cpt.png "Custom post type")<br>Custom post type | ![ctt](./screenshots/sitemap-ctt.png "Custom taxonomy type")<br>Custom taxonomy type |

## Project Structure

```
├── example-app                                # Next.js application root
│   ├── public
│   │   └── sitemap.xsl                        # XSLT style file for the sitemap
│   └── src
│       ├── components
│       ├── lib
│       │   ├── client.js                      # Apollo Client instance
│       │   └── generateSiteMap.js             # Helper function that generates the XML content
│       └── pages
│           ├── sitemap                        # Base path for sitemap subpages
│           │   └── [...type]                  # Catch-all route for sitemap subpages
│           └── sitemap.xml.js                 # Index sitemap.xml page
├── .wp-env.json                               # wp-env configuration file
└── wp-env
    ├── db
    │   └── database.sql                       # WordPress database including all demo data for the example
    ├── plugins
    │   └── hwpt-wpgraphql-sitemap             # Custom plugin to extend WPGraphQL to support WordPress sitemap
    ├── setup
    └── uploads                                # WordPress content to be used by wp-env
```

## Important notes

- If you're intending to use this example with your own WordPress instance, make sure to uncheck the `Discourage search engines from indexing this site` checkbox under `Settings -> Reading` in the WordPress admin.
- If the XML sitemap feature in Yoast SEO is enabled, it will disable the native WordPress sitemap. To run this example, you must disable Yoast SEO's XML sitemap feature.

## Running the example with wp-env

### Prerequisites

- Node.js (v18+ recommended)
- [Docker](https://www.docker.com/) (if you plan on running the example see details below)

**Note** Please make sure you have all prerequisites installed as mentioned above and Docker running (`docker ps`)

### Setup Repository and Packages

- Clone the repo `git clone https://github.com/wpengine/hwptoolkit.git`
- Install packages `cd hwptoolkit && npm install`
- Setup a .env file under `examples/next/custom-sitemap-apollo/example-app` and add these values inside:

```
NEXT_PUBLIC_WORDPRESS_URL=http://localhost:8888
NEXT_PUBLIC_URL=http://localhost:3000
```

or run the command below:

```bash
echo "NEXT_PUBLIC_WORDPRESS_URL=http://localhost:8888\\nNEXT_PUBLIC_URL=http://localhost:3000" > examples/next/custom-sitemap-apollo/example-app/.env
```

### Build and start the application

- `cd examples/next/custom-sitemap-apollo`
- Then run `npm run example:build` will build and start your application.
- This does the following:
  - Starts up [wp-env](https://developer.wordpress.org/block-editor/getting-started/devenv/get-started-with-wp-env/)
  - Imports the database from [wp-env/db/database.sql](wp-env/db/database.sql)
  - Install Next.js dependencies for `example-app`
  - Runs the Next.js dev script

Congratulations, WordPress should now be fully set up.

| Frontend                                         | Admin                                                              |
| ------------------------------------------------ | ------------------------------------------------------------------ |
| [http://localhost:3000/](http://localhost:3000/) | [http://localhost:8888/wp-admin/](http://localhost:8888/wp-admin/) |

> **Note:** The login details for the admin is username "admin" and password "password"

### Command Reference

| Command               | Description                                                                                                             |
| --------------------- | ----------------------------------------------------------------------------------------------------------------------- |
| `example:build`       | Prepares the environment by starting WordPress, importing the database, and starting the application. |
| `example:dev`         | Runs the Next.js development server.                                                                                    |
| `example:dev:install` | Installs the required Next.js packages.                                                                                 |
| `example:start`       | Starts WordPress and the Next.js development server.                                                                    |
| `example:stop`        | Stops the WordPress environment.                                                                                        |
| `example:prune`       | Rebuilds and restarts the application by destroying and recreating the WordPress environment.                           |
| `wp:start`            | Starts the WordPress environment.                                                                                       |
| `wp:stop`             | Stops the WordPress environment.                                                                                        |
| `wp:destroy`          | Completely removes the WordPress environment.                                                                           |
| `wp:db:query`         | Executes a database query within the WordPress environment.                                                             |
| `wp:db:export`        | Exports the WordPress database to `wp-env/db/database.sql`.                                                             |
| `wp:db:import`        | Imports the WordPress database from `wp-env/db/database.sql`.                                                           |

> **Note** You can run `npm run wp-env` and use any other wp-env command. You can also see <https://www.npmjs.com/package/@wordpress/env> for more details on how to use or configure `wp-env`.

### Database access

If you need database access add the following to your wp-env `"phpmyadminPort": 11111,` (where port 11111 is not allocated).

You can check if a port is free by running `lsof -i :11111`
