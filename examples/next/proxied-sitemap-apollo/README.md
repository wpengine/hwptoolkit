# WordPress to Next.js Sitemap Integration
## Overview
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
- Install packages `cd hwptoolkit && pnpm install`
- Setup a .env file under `examples/next/hybrid-sitemap-apollo/example-app` with `NEXT_PUBLIC_WORDPRESS_URL=http://localhost:8888`
e.g.

```bash
echo "NEXT_PUBLIC_WORDPRESS_URL=http://localhost:8888" > examples/next/hybrid-sitemap-apollo/example-app/.env
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
- Then run `pnpm example:build` will build and start your application. 
- This does the following:
    - Unzips `wp-env/uploads.zip` to `wp-env/uploads` which is mapped to the wp-content/uploads directory for the Docker container.
    - Starts up [wp-env](https://developer.wordpress.org/block-editor/getting-started/devenv/get-started-with-wp-env/)
    - Imports the database from [wp-env/db/database.sql](wp-env/db/database.sql)
    - Install Next.js dependencies for `example-app`
    - Runs the Next.js dev script

Congratulations, WordPress should now be fully set up.

| Frontend | Admin                        |
|----------|------------------------------|
| [http://localhost:3000/](http://localhost:3000/) | [http://localhost:8888/wp-admin/](http://localhost:8888/wp-admin/) |


> **Note:** The login details for the admin is username "admin" and password "password"


## Command Reference

| Command                | Description                                                                 |
|------------------------|-----------------------------------------------------------------------------|
| `example:build`        | Prepares the environment by unzipping images, starting WordPress, importing the database, and starting the application. |
| `example:dev`          | Runs the Next.js development server.                                       |
| `example:dev:install`  | Installs the required Next.js packages.                                    |
| `example:start`        | Starts WordPress and the Next.js development server.                       |
| `example:stop`         | Stops the WordPress environment.                                           |
| `example:prune`        | Rebuilds and restarts the application by destroying and recreating the WordPress environment. |
| `wp:start`             | Starts the WordPress environment.                                          |
| `wp:stop`              | Stops the WordPress environment.                                           |
| `wp:destroy`           | Completely removes the WordPress environment.                              |
| `wp:db:query`          | Executes a database query within the WordPress environment.                |
| `wp:db:export`         | Exports the WordPress database to `wp-env/db/database.sql`.                |
| `wp:db:import`         | Imports the WordPress database from `wp-env/db/database.sql`.              |
| `wp:images:unzip`      | Extracts the WordPress uploads directory.                                  |
| `wp:images:zip`        | Compresses the WordPress uploads directory.                                |


>**Note** You can run `pnpm wp-env` and use any other wp-env command. You can also see <https://www.npmjs.com/package/@wordpress/env> for more details on how to use or configure `wp-env`.

### Database access

If you need database access add the following to your wp-env `"phpmyadminPort": 11111,` (where port 11111 is not allocated).

You can check if a port is free by running `lsof -i :11111`