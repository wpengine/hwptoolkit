---
title: "WPGraphQL Logging with Next.js Pages Router"
description: "This demonstrates the usage of WPGraphQL Logging with Next.js Pages Router and WPGraphQL. It shows how GraphQL queries are logged and can be viewed, filtered, and exported from the WordPress admin."
---

# Example: WPGraphQL Logging with Next.js

## Overview

This example shows the WPGraphQL Logging plugin in action. The example uses a simple Next.js application that makes various GraphQL queries to WordPress, and demonstrates how those queries are logged and can be monitored through the WordPress admin interface.

The example includes a wp-env setup, which will allow you to build and start this example quickly. With this wp-env setup, you don't need to have a separate WordPress instance or demo data to inspect the example.

## What does this example do

1. Fetch and display a list of posts from WordPress using GraphQL
2. Fetch and display individual posts and pages using GraphQL with variables
3. Automatically log all GraphQL queries to the WordPress database
4. View logged queries in the WordPress admin (GraphQL Logs → All Logs)
5. Filter logs by date range and log level
6. Export logs to CSV format
7. Configured WordPress instance with demo data and required plugins, using wp-env

## Project Structure

```
├── example-app
│   └── src
│       ├── components                         # Reusable React components
│       ├── lib
│       │   └── client.js                      # Apollo client instance
│       ├── pages
│       │   ├── index.js                       # Home page - list of posts
│       │   ├── posts.js                       # Posts list with pagination
│       │   └── [slug].js                      # Dynamic route for single post/page
│       └── styles
│           └── globals.css                    # Global styles
├── .wp-env.json                               # wp-env configuration file
└── wp-env
    ├── db
    │   └── database.sql                       # WordPress database including all demo data for the example
    └── uploads.zip                            # WordPress content to be used by wp-env
```

## Running the example with wp-env

### Prerequisites

- Node.js (v18+ recommended)
- [Docker](https://www.docker.com/) (if you plan on running the example see details below)

**Note** Please make sure you have all prerequisites installed as mentioned above and Docker running (`docker ps`)

### 1. Setup Repository and Packages

- Clone the repo `git clone https://github.com/wpengine/hwptoolkit.git`
- Install packages `cd hwptoolkit && npm install`

### 2. Build and start the application

- `cd plugins/wpgraphql-logging/examples/wpgraphql-logging-nextjs`
- Then run `npm run example:build` will build and start your application.

This starts the wp-env instance and frontend application.

> [!IMPORTANT]
> After the wp-env instance starts, ensure that all installed plugins are activated for this example to work properly.

| Frontend               | Admin                           |
| ---------------------- | ------------------------------- |
| http://localhost:3000/ | http://localhost:8888/wp-admin/ |

> **Note:** The login details for the admin is username "admin" and password "password"

### 3. Create environment variables for Next.js

Create a `.env.local` file in the `example-app` directory with the following content:

```bash
NEXT_PUBLIC_WORDPRESS_URL=http://localhost:8888
```

This tells the Next.js application where to find your WordPress GraphQL endpoint.

### 4. Explore the logged queries

1. Navigate to the Next.js frontend at http://localhost:3000/
2. Browse through the posts by clicking on them
3. Visit http://localhost:3000/posts to see a different query
4. Login to WordPress admin at http://localhost:8888/wp-admin/
5. Go to **GraphQL Logs → All Logs** to see all logged queries
6. Click on any log entry to see detailed information including:
   - Query text and variables
   - Response data
   - Execution time and memory usage
   - Request headers and context
7. Use the filters to narrow down logs by date range or log level
8. Export logs to CSV for offline analysis

### 5. Configure logging settings (Optional)

Navigate to **GraphQL Logs → Settings** in the WordPress admin to configure:

- Enable/disable logging
- Adjust data sampling rate (default is 100% for this example)
- Configure data retention period
- Set up data sanitization rules
- Exclude specific queries from logging

If you want to learn more about the logging plugin, check out [the documentation](../../../docs/plugins/wpgraphql-logging/index.md).

### Command Reference

| Command               | Description                                                                                                             |
| --------------------- | ----------------------------------------------------------------------------------------------------------------------- |
| `example:build`       | Prepares the environment by unzipping images, starting WordPress, importing the database, and starting the application. |
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
| `wp:images:unzip`     | Extracts the WordPress uploads directory.                                                                               |
| `wp:images:zip`       | Compresses the WordPress uploads directory.                                                                             |

> **Note** You can run `npm run wp-env` and use any other wp-env command. You can also see <https://www.npmjs.com/package/@wordpress/env> for more details on how to use or configure `wp-env`.

### Database access

If you need database access add the following to your `.wp-env.json`: `"phpmyadminPort": 11111,` (where port 11111 is not allocated).

You can check if a port is free by running `lsof -i :11111`
