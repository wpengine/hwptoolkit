---
title: "Next.js Hybrid Sitemap Integration"
description: "A Next.js application that fetches and transforms WordPress sitemaps with clean URL formatting, providing a seamless integration between WordPress content and Next.js frontend."
---

# WordPress to Next.js Sitemap Integration

A Next.js application that fetches and transforms WordPress sitemaps with clean URL formatting, providing a seamless integration between WordPress content and Next.js frontend.

## Features

* XML Sitemap Transformation - Fetches WordPress sitemaps and transforms URLs to match your frontend
* Clean URL Structure - Presents sitemap links with clean, user-friendly URLs
* XSL Styling Support - Custom XSL stylesheets for better presentation of sitemap data
* Sitemap Index Support - Handles both individual sitemaps and sitemap index files
* SEO Friendly - Maintains all SEO benefits of XML sitemaps while using your frontend domain

## Prerequisites

* Node.js (v18+ recommended)
* Next.js project
* WordPress site with XML sitemaps enabled (core functionality or via plugin)

## Setup

### Environment Variables

Create or update your .env.local file with:
```
NEXT_PUBLIC_WORDPRESS_URL=http://your-wordpress-site.com
NEXT_PUBLIC_FRONTEND_URL=http://your-nextjs-site.com
```

Create API Route

Create a file at `pages/sitemap.xml.js` with the code provided in this repository.

## Add XSL Stylesheet

Place the `sitemap.xsl` file in your public directory.

## How It Works
This integration:

* Intercepts requests to `/sitemap.xml` on your Next.js site
* Fetches the corresponding sitemap from your WordPress site
* Transforms all URLs from WordPress domain to your frontend domain
* Applies custom XSL styling for better presentation
* Returns the transformed XML to the client

For sitemap index files, it also:

* Creates clean, friendly URLs for each child sitemap
* Maintains proper linking through custom attributes

## API Routes examples

`/sitemap.xml` - Main sitemap endpoint that fetches and transforms WordPress sitemaps
`/sitemap.xml?sitemap=/wp-sitemap-posts-post-1.xml` - Direct access to specific WordPress sitemaps

# Running the example with wp-env

## Prerequisites

**Note** Please make sure you have all prerequisites installed as mentioned above and Docker running (`docker ps`)

## Setup Repository and Packages

- Clone the repo `git clone https://github.com/wpengine/hwptoolkit.git`
- Install packages `cd hwptoolkit && npm install`
- Setup a .env file under `examples/next/hybrid-sitemap-apollo/example-app` with `NEXT_PUBLIC_WORDPRESS_URL=http://localhost:8888`
e.g.

```bash
echo "NEXT_PUBLIC_WORDPRESS_URL=http://localhost:8888" > examples/next/hybrid-sitemap-apollo/example-app/.env
```

## Build and start the application

- `cd examples/next/hybrid-sitemap-apollo`
- Run `npm run setup` to set up the example, then `npm run dev` to start it.
- `setup` does the following:
    - Starts up [wp-env](https://developer.wordpress.org/block-editor/getting-started/devenv/get-started-with-wp-env/)
    - Imports the database from [wp-env/db/database.sql](wp-env/db/database.sql)
    - Install Next.js dependencies for `example-app`

Congratulations, WordPress should now be fully set up.

| Frontend | Admin                        |
|----------|------------------------------|
| [http://localhost:3000/](http://localhost:3000/) | [http://localhost:8888/wp-admin/](http://localhost:8888/wp-admin/) |


> **Note:** The login details for the admin is username "admin" and password "password"


## Command Reference

This example uses the [standard example scripts](../../README.md#running-an-example): run `npm run setup` once, then `npm run dev`. Use `npm run app:dev` to restart only the front end while WordPress keeps running, `npm run stop` to stop WordPress, and `npm run clean` to remove it.

> **Note** You can run `npx wp-env` for any other wp-env command. See <https://www.npmjs.com/package/@wordpress/env> for details.

### Database access

If you need database access add the following to your wp-env `"phpmyadminPort": 11111,` (where port 11111 is not allocated).

You can check if a port is free by running `lsof -i :11111`