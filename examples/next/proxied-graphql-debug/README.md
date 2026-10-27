---
title: "Next.js GraphQL Proxy Debugger"
description: "A debugging utility for proxied GraphQL APIs within WordPress environments, offering enhanced query inspection, request/response logging, and real-time query complexity estimation."
---

# WordPress GraphQL Proxy Debugger

A debugging utility for proxied GraphQL APIs within WordPress environments, offering enhanced query inspection, request/response logging, and real-time query complexity estimation.

## Features

* **GraphQL Proxy Debugging** - Intercepts and forwards GraphQL requests through a custom proxy.
* **Debug Print Output** - Logs proxied GraphQL queries and their responses for easier debugging.
* **Query Complexity Estimation** - Calculates the complexity of each GraphQL query using a customizable estimation algorithm.

## Prerequisites

* Node.js (v18+ recommended)
* WordPress with WPGraphQL plugin installed
* [wp-env](https://developer.wordpress.org/block-editor/getting-started/devenv/get-started-with-wp-env/)
* Docker running (`docker ps`)

## Setup

### Install Dependencies

Run:
```
npm install
```

### Environment Variables

Create a `.env` file and put the content below inside. If your proxy requires environment-specific configs add them too.

```
NEXT_PUBLIC_WORDPRESS_URL=http://localhost:8888
```

or run the command below to create the .env file:

```bash
echo "NEXT_PUBLIC_WORDPRESS_URL=http://localhost:8888" > examples/next/proxied-graphql-debug/example-app/.env
```

### Start the Environment

To start WordPress and run your proxy/debugger setup:
```
npm run setup
npm run dev
```

## How It Works

This debugging tool:

* Intercepts GraphQL POST requests sent to the WordPress GraphQL endpoint.
* Logs the incoming query, variables, and response payload.
* Estimates the complexity of the query based on field nesting and predefined multipliers.
* Provides output via console or log files.

## Complexity Estimation

The complexity estimator:

* Traverses the GraphQL AST to measure query depth and individual field cost.
* Applies configurable multipliers for specific fields or types.
* Can be integrated into request handlers to reject or warn on overly expensive queries.

## Running the Example with wp-env

### Clone and Install

```
git clone https://github.com/wpengine/hwptoolkit.git
cd hwptoolkit
npm install
```

### Build and Start Application

```
cd examples/next/proxied-graphql-debug
npm run setup
npm run dev
```

This will:

- Start [wp-env](https://developer.wordpress.org/block-editor/getting-started/devenv/get-started-with-wp-env/)
- Import the database from `wp-env/db/database.sql`
- Install Next.js dependencies for `example-app`

| Frontend | Admin                        |
|----------|------------------------------|
| [http://localhost:3000/](http://localhost:3000/) | [http://localhost:8888/wp-admin/](http://localhost:8888/wp-admin/) |

> **Login details:** Username `admin`, Password `password`

## Command Reference

This example uses the [standard example scripts](../../README.md#running-an-example): run `npm run setup` once, then `npm run dev`. Use `npm run app:dev` to restart only the front end while WordPress keeps running, `npm run stop` to stop WordPress, and `npm run clean` to remove it.

> **Note** You can run `npx wp-env` for any other wp-env command. See <https://www.npmjs.com/package/@wordpress/env> for details.

## Database Access (Optional)

If you need direct database access, add the following to your `wp-env.json`:
```
"phpmyadminPort": 11111
```
Check if the port is free:
```
lsof -i :11111
```