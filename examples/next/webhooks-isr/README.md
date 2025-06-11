# WordPress to Next.js Webhooks Plugin Integration
## Overview
This integration enables seamless communication between a WordPress backend and a Next.js frontend using webhooks. When content updates occur in WordPress, webhooks notify the Next.js application to revalidate and update its cached pages, ensuring fresh and consistent content delivery.

## Features

*Incremental Static Regeneration (ISR) Showcase – Demonstrates Next.js ISR fully working with WordPress-triggered webhooks.

*On-Demand Revalidation – Webhooks notify Next.js to revalidate specific pages when WordPress content changes.

*Relative Path Payloads – Webhook payloads send clean relative paths (e.g., /posts/my-post) for accurate revalidation.

*Secure Webhook Requests – Uses secret tokens in headers to authenticate webhook calls.

*Flexible HTTP Methods & Headers – Supports POST requests with custom headers for integration flexibility.

*WordPress Native Integration – Uses WordPress Custom Post Types and hooks for managing webhooks.

*Extensible & Developer Friendly – Easily customizable payloads and event triggers via WordPress filters and actions.

## Prerequisites

* WordPress site with the wpgraphql-headless-webhooks plugin installed.
* Next.js project (Node.js v18+ recommended).
* Environment variables configured for WordPress URL and webhook secret.

## Setup
### Environment Variables
Create or update your .env.local in your Next.js project:

```ini
NEXT_PUBLIC_WORDPRESS_URL=http://your-wordpress-site.com
WEBHOOK_REVALIDATE_SECRET=your_webhook_secret_token
```

### Creating a Test Webhook in WordPress
Add this PHP snippet to your theme’s `functions.php` or a custom plugin to create a webhook that triggers on post updates and calls your Next.js revalidation API:

```php
function create_test_post_published_webhook() {
    // Get the repository instance from your plugin
    $repository = \WPGraphQL\Webhooks\Plugin::instance()->get_repository();

    // Define webhook properties
    $name = 'Test Post Published Webhook';
    $event = 'post_updated';
    $url = 'http://localhost:3000/api/revalidate'; // Update to your Next.js API URL
    $method = 'POST';

    $headers = [
        'X-Webhook-Secret' => 'your_webhook_secret_token', // Must match Next.js secret
        'Content-Type' => 'application/json',
    ];
    $result = $repository->create( $name, $event, $url, $method, $headers );

    if ( is_wp_error( $result ) ) {
        error_log( 'Failed to create webhook: ' . $result->get_error_message() );
    } else {
        error_log( 'Webhook created successfully with ID: ' . $result );
    }
}

// Run once, for example on admin_init or manually trigger it
add_action( 'admin_init', 'create_test_post_published_webhook' );
```

## Modifying the Webhook Payload to Send Relative Paths
Add this filter to your WordPress plugin or theme to ensure the webhook payload sends a relative path (required by Next.js revalidate API):

```php
add_filter( 'graphql_webhooks_payload', function( array $payload, $webhook ) {
    error_log('[Webhook] Initial payload: ' . print_r($payload, true));
    if ( ! empty( $payload['post_id'] ) ) {
        $post_id = $payload['post_id'];
        error_log('[Webhook] Processing post ID: ' . $post_id);
        
        $permalink = get_permalink( $post_id );
        
        if ( $permalink ) {
            // Extract relative path from permalink URL
            $path = parse_url( $permalink, PHP_URL_PATH );
            $payload['path'] = $path;
            error_log('[Webhook] Added relative path: ' . $path);
        } else {
            error_log('[Webhook] Warning: Failed to get permalink for post ID: ' . $post_id);
        }
    } else {
        error_log('[Webhook] Notice: No post_id in payload');
    }

    // Log final payload state
    error_log('[Webhook] Final payload: ' . print_r($payload, true));
    
    return $payload;
}, 10, 2 );
```



## How It Works
This integration:

* When a post is updated in WordPress, the webhook triggers and sends a POST request to the Next.js revalidation API.
* The payload includes a relative path extracted from the post permalink.
* The Next.js API verifies the secret token from the header and calls res.revalidate(path) to refresh the cached page.
* This keeps your frontend content in sync with WordPress backend updates.

# Running the example with wp-env

## Prerequisites

**Note** Please make sure you have all prerequisites installed as mentioned above and Docker running (`docker ps`)

## Setup Repository and Packages

- Clone the repo `git clone https://github.com/wpengine/hwptoolkit.git`
- Install packages `cd hwptoolkit && pnpm install`
- Setup a .env file under `examples/next/webhooks-isr/example-app` with `NEXT_PUBLIC_WORDPRESS_URL=http://localhost:8888`
e.g.

```bash
echo "NEXT_PUBLIC_WORDPRESS_URL=http://localhost:8888" > examples/next/webhooks-isr/example-app/.env
echo "WEBHOOK_REVALIDATE_SECRET=your_webhook_secret_token" > examples/next/webhooks-isr/example-app/.env
```

## Build and start the application

- `cd examples/next/webhooks-isr`
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