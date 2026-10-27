---
title: "Next.js Authentication with Apollo Client"
description: "This example demonstrates authentication with username and password in a headless WordPress setup, running Next.js as a frontend framework. Example is using WPGraphQL and WPGraphQL Headless Login plugins to enable authentication features."
---

# Next.js pages Apollo Authentication

This example demonstrates authentication with username and password in a headless WordPress setup, running Next.js as a frontend framework. Example is using WPGraphQL and WPGraphQL Headless Login plugins to enable authentication features.

## Screenshots

After following the installation steps, you should have the example webpage as shown in the screenshots below:

|                                                 |                                                            |
| :---------------------------------------------: | :--------------------------------------------------------: |
| ![login](./screenshots/login.png)<br>Login page | ![logged](./screenshots/logged.png "Posts")<br>After login |

## Project Structure

```
.
├── example-app/                    # Next.js application folder containing frontend code
│   └── src/
│       ├── components/             # Reusable React components
│       ├── lib/                    # Apollo Client configuration, client and helpers
│       └── pages/                  # Next.js page routes
├── .wp-env.json                    # wp-env configuration file
└── wp-env
    └── db
        └── database.sql            # WordPress database including all demo data for the example
```

## Running the example with wp-env

### Prerequisites

- Node.js (v18+ recommended)
- [Docker](https://www.docker.com/) (if you plan on running the example see details below)

**Note** Please make sure you have all prerequisites installed as mentioned above and Docker running (`docker ps`)

### Setup Repository and Packages

- Clone the repo `git clone https://github.com/wpengine/hwptoolkit.git`
- Install packages `cd hwptoolkit && npm install`
- Setup a .env file under `examples/next/apollo-authentication/example-app` and add these values inside:

```
NEXT_PUBLIC_WORDPRESS_URL=http://localhost:8888
```

or run the command below:

```bash
echo "NEXT_PUBLIC_WORDPRESS_URL=http://localhost:8888" > examples/next/apollo-authentication/example-app/.env
```

### Build and start the application

- `cd examples/next/apollo-authentication`
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

## Running the example with custom WordPress setup

### Prerequisites

Before running this project, ensure you have the following:

- **Node.js** (version 18 or higher recommended)
- **npm**, **yarn**, **pnpm**, or **bun** package manager
- A **WordPress** instance with the following plugins installed and configured:
  - **WPGraphQL**
  - **WPGraphQL Headless Login**

### WordPress Setup

1.  **Install Plugins:** In your WordPress admin dashboard, navigate to "Plugins" and install the following plugins:

    - **WPGraphQL**: This plugin exposes your WordPress data via GraphQL.
    - **WPGraphQL Headless Login**: This plugin enables authentication for headless WordPress setups.

2.  **Configure Headless Login:** After installing WPGraphQL Headless Login, go to the plugin's settings page (usually found under the "GraphQL" menu in your WordPress admin dashboard). **Enable Credentials Authentication**.

    ![Enable Credentials Authentication](./screenshots/enable-credentials-auth.png)

### Environment Variables

Create a `.env` file in the root of your project with the following variable:

```
NEXT_PUBLIC_WORDPRESS_URL=<your_wordpress_url>
```

Replace `<your_wordpress_url>` with the URL of your WordPress instance (e.g., `https://your-wordpress-site.com`). **Do not include a trailing slash.**

### Getting Started

1.  **Install Dependencies:**

    ```
    npm install
    # or
    yarn install
    # or
    pnpm install
    # or
    bun install
    ```

2.  **Run the Development Server:**

    ```
    npm run dev
    # or
    yarn dev
    # or
    pnpm dev
    # or
    bun dev
    ```
