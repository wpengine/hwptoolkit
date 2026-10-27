---
title: "Next.js Proxied Sitemap with Apollo Client"
description: "This solution provides a proxied sitemap for a WordPress site that integrates seamlessly with a Next.js frontend. The WordPress XML sitemaps are fetched, and the domain URLs within the sitemap are replaced with the frontend domain (headless site URL). These transformed sitemaps are then served via a Next.js API route, ensuring SEO-friendly URLs that point to your frontend domain."
---

# WordPress to Next.js Sitemap Integration

This solution provides a proxied sitemap for a WordPress site that integrates seamlessly with a Next.js frontend. The WordPress XML sitemaps are fetched, and the domain URLs within the sitemap are replaced with the frontend domain (headless site URL). These transformed sitemaps are then served via a Next.js API route, ensuring SEO-friendly URLs that point to your frontend domain.


## Features

* Proxy Sitemap Handling: Intercepts requests for WordPress sitemaps and proxies them through the Next.js application.

* Sitemap URL Replacement: Replaces all URLs in the sitemaps to point to the frontend domain rather than the WordPress backend domain.

* Sitemap Index Support: Supports both individual sitemaps and sitemap index files.

* SEO Friendly: Maintains the SEO benefits of XML sitemaps while serving them via your frontend domain, ensuring proper search engine indexing.

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

## How It Works

Proxying Sitemap Requests:

1. When a request for a sitemap (e.g., `/wp-sitemap-posts-post-1.xml`) is made on the Next.js frontend, the Next.js server fetches the corresponding sitemap from WordPress.

2. The bundled plugin automatically replaces any URLs pointing to the WordPress backend domain (e.g., http://your-wordpress-site.com) with URLs pointing to the frontend domain (e.g., http://your-nextjs-site.com).

3. The transformed sitemap is returned to the client with the correct URLs pointing to the frontend domain and the appropriate Content-Type header (application/xml).

# Running the example with wp-env

## Prerequisites

**Note** Please make sure you have all prerequisites installed as mentioned above and Docker running (`docker ps`)

## Setup Repository and Packages

- Clone the repo `git clone https://github.com/wpengine/hwptoolkit.git`
- Install packages `cd hwptoolkit && npm install`
- Setup a .env file under `examples/next/proxied-sitemap-apollo/example-app` with `NEXT_PUBLIC_WORDPRESS_URL=http://localhost:8888`
e.g.

```bash
echo "NEXT_PUBLIC_WORDPRESS_URL=http://localhost:8888\\nNEXT_PUBLIC_FRONTEND_URL=http://localhost:3000" > examples/next/proxied-sitemap-apollo/example-app/.env
```

## FRONTEND_URL (Optional Customization)
The FRONTEND_URL is automatically set by default to `http://localhost:3000`. This is the URL to which your sitemaps will point. If you want to change it to a different frontend domain (for example, if your Next.js site is hosted elsewhere), you can define the FRONTEND_URL constant in your wp-config.php file.

To define it manually, add the following line to your wp-config.php:

```php
define( 'FRONTEND_URL', 'http://your-nextjs-site.com' );
```
If you don't define it explicitly, the default value of http://localhost:3000 will be used.

## Build and start the application

- `cd examples/next/proxied-sitemap-apollo`
- Run `npm run setup` to set up the example, then `npm run dev` to start it.
- `setup` does the following:
    - Starts up [wp-env](https://developer.wordpress.org/block-editor/getting-started/devenv/get-started-with-wp-env/)
    - Imports the database from [wp-env/db/database.sql](wp-env/db/database.sql)
    - Install Next.js dependencies for `example-app`

Congratulations, WordPress should now be fully set up.

| Frontend | Admin                        |
|----------|------------------------------|
| [http://localhost:3000/](http://localhost:3000/) | [http://localhost:8888/wp-admin/](http://localhost:8888/wp-admin/) |
http://localhost:3000/sitemap.xml

> **Note:** The login details for the admin is username "admin" and password "password"

| Sitemap access | Description                        |
|----------|------------------------------|
| [http://localhost:3000/sitemap.xml](http://localhost:3000/sitemap.xml) | Base sitemap index |


## Command Reference

This example uses the [standard example scripts](../../README.md#running-an-example): run `npm run setup` once, then `npm run dev`. Use `npm run app:dev` to restart only the front end while WordPress keeps running, `npm run stop` to stop WordPress, and `npm run clean` to remove it.

> **Note** You can run `npx wp-env` for any other wp-env command. See <https://www.npmjs.com/package/@wordpress/env> for details.

### Database access

If you need database access add the following to your wp-env `"phpmyadminPort": 11111,` (where port 11111 is not allocated).

You can check if a port is free by running `lsof -i :11111`
