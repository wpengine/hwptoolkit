---
title: "Next.js Data Fetching with Apollo Client"
description: "This example demonstrates various approaches to integrate WordPress as a headless CMS with a Next.js frontend using Apollo Client. It showcases different data fetching strategies, state management techniques, and modern web development patterns in a real-world application context."
---

# Example: Fetching data from WordPress with Apollo Client in Next.js

This example demonstrates various approaches to integrate WordPress as a headless CMS with a Next.js frontend using Apollo Client. It showcases different data fetching strategies, state management techniques, and modern web development patterns in a real-world application context.

## Features

- **Covers various rendering patterns of Next.js**

  - Server-Side Rendering (SSR) for dynamic pages
  - Static Site Generation (SSG) for static pages
  - Client-Side data fetching (CSR) for blog settings
  - Hybrid data fetching, combining SSR and CSR

- **Blog features**

  - Listing posts with pagination
  - Live search of posts
  - Fetching posts and pages using nodeByUri of WPGraphQL
  - Fetching static pages at build time
  - Commenting posts
  - Header with dynamic blog title

- **Apollo Client integration**
  - Relay-style pagination
  - Fragment management
  - Error handling
  - Custom fetch policies
  - Custom error policies
  - useLazyQuery example
  - useMutation example
  - Automatic Persisted Queries

## Screenshots

|                                                                                          |                                                                           |                                                                   |
| :--------------------------------------------------------------------------------------: | :-----------------------------------------------------------------------: | :---------------------------------------------------------------: |
| ![post](./screenshots/post-with-comments.png "Post with comments")<br>Post with comments | ![newComment](./screenshots/new-comment.png "New comment")<br>New comment |          ![home](./screenshots/home.png "Home")<br>Home           |
|        ![liveSearch](./screenshots/live-search.png "Live search")<br>Live search         | ![staticPage](./screenshots/static-page.png "Static page")<br>Static page | ![loadMore](./screenshots/load-more.png "Load more")<br>Load more |

## Project Structure

```
├── example-app
│   └── src
│       ├── components                  # React components
│       ├── lib
│       │   └── client.js               # Apollo Client instance
│       └── pages
│           ├── [uri].js                # Catch-all route for posts and pages
│           ├── index.js                # Home page
│           └── privacy-policy.js       # Statically generated page route
├── .wp-env.json                        # wp-env configuration file
└── wp-env
    ├── db
    │   └── database.sql                # WordPress database including all demo data for
    └── setup
```

## Running the example with wp-env

### Prerequisites

- Node.js (v18+ recommended)
- [Docker](https://www.docker.com/) (if you plan on running the example see details below)

**Note** Please make sure you have all prerequisites installed as mentioned above and Docker running (`docker ps`)

### Setup Repository and Packages

- Clone the repo `git clone https://github.com/wpengine/hwptoolkit.git`
- Install packages `cd hwptoolkit && npm install`
- Setup a .env file under `examples/next/apollo-client-data-fetch/example-app` and add these values inside:

```bash
NEXT_PUBLIC_WORDPRESS_URL=http://localhost:8888
NEXT_PRIVACY_POLICY_URI=/privacy-policy
```

or run the command below:

```bash
echo "NEXT_PUBLIC_WORDPRESS_URL=http://localhost:8888\\nNEXT_PRIVACY_POLICY_URI=/privacy-policy" > examples/next/apollo-client-data-fetch/example-app/.env
```

### Build and start the application

- `cd examples/next/apollo-client-data-fetch`
- Run `npm run setup` to set up the example, then `npm run dev` to start it.

Congratulations, WordPress should now be fully set up.

| Frontend                                         | Admin                                                              |
| ------------------------------------------------ | ------------------------------------------------------------------ |
| [http://localhost:3000/](http://localhost:3000/) | [http://localhost:8888/wp-admin/](http://localhost:8888/wp-admin/) |

> **Note:** The login details for the admin is username "admin" and password "password"

### Command Reference

This example uses the [standard example scripts](../../README.md#running-an-example): run `npm run setup` once, then `npm run dev`. Use `npm run app:dev` to restart only the front end while WordPress keeps running, `npm run stop` to stop WordPress, and `npm run clean` to remove it.

> **Note** You can run `npx wp-env` for any other wp-env command. See <https://www.npmjs.com/package/@wordpress/env> for details.

### Database access

If you need database access add the following to your wp-env `"phpmyadminPort": 11111,` (where port 11111 is not allocated).

You can check if a port is free by running `lsof -i :11111`
