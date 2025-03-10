# Example: Next.js App Router using the Fetch API


# Overview

This project is a complete example of a **headless WordPress site** powered by **Next.js App Router**, using the `fetch` API to retrieve content from the WordPress backend. It also includes a robust **local development environment** using `wp-env` and comes with built-in database management tools.


## Installation

### Prerequisites

- Node.js (v18+ recommended)
- pnpm
- Docker (if you plan on running the example)

### Project Structure

```
/
├── example-app/            # Next.js App Router project
├── wp-env/                  # WordPress local environment setup
│   ├── wp-env.json
│   ├── uploads/             # WordPress uploaded media directory for example application
│   ├── db/                   # Example database export
│   ├── tools/                # Adminer for accessing the database
├── package.json             # Root scripts to control both systems
```

### Steps

1. Clone the repo `git clone git@github.com:wpengine/hwptoolkit.git`
2. Open your terminal and cd into the current directory e.g. `cd examples/nextjs-app-router/client-fetch-data`
3. Install Next.js dependencies `cd example-app and npm install`
4. Have Docker running `e.g. docker ps`
5. Run `pnpm example:build` and this will unzip `wp-env/uploads`, startup [wp-env](https://developer.wordpress.org/block-editor/getting-started/devenv/get-started-with-wp-env/) and run Next.js application.


You should now have WordPress installed and setup at `http://localhost:8888/wp-admin` and your frontend should be running at `http://localhost:3000/`
The login details for WordPress is `admin` and `password`.

> **Note** If permalinks are not working, you might need to re-save permalinks to clear the cache.

### Accessing the database

The database can be accessed at `http://localhost:8888/adminer.php` with the following details:

| Field     | Value      |
|-----------|------------|
| Server    | mysql      |
| User      | root       |
| Password  | password   |
| Database  | wordpress  |

This uses [https://www.adminer.org/](https://www.adminer.org/)

>**Tip** You can prefill values so this URL might work better <http://localhost:8888/adminer.php?server=mysql&username=root&db=wordpress>

>**Mote** If you ever need to debug the database details you run `docker exec -it 6d90a6769e2b33bf4b44a75350dc4b9d-wordpress-1 bash` and then `printenv | grep WORDPRESS_DB` to get the credentials. `6d90a6769e2b33bf4b44a75350dc4b9d` is the container name which you can get from running `docker ps` 


## Command Reference

| Command                | Description                                                                  |
|------------------------|------------------------------------------------------------------------------|
| `example:build`        | Unzips images, starts WordPress, imports the database, and runs Next.js app  |
| `example:dev`          | Runs the Next.js development server.                                         |
| `example:start`        | Starts WordPress and runs the Next.js development server.                    |
| `example:stop`         | Stops the WordPress environment.                                             |
| `example:prune`        | Destroys the WordPress environment and rebuilds the project.                 |
| `wp:start`             | Starts the WordPress environment and flushes rewrite rules.                  |
| `wp:stop`              | Stops the WordPress environment.                                             |
| `wp:destroy`           | Destroys the WordPress environment.                                          |
| `wp:db:query`          | Runs a database query in the WordPress environment.                          |
| `wp:db:export`         | Exports the WordPress database.                                              |
| `wp:db:import`         | Imports the WordPress database.                                              |
| `wp:images:unzip`      | Unzips the WordPress uploads directory.                                      |
| `wp:images:zip`        | Zips the WordPress uploads directory.                                        |

>**Note** You can run `pnpm wp-env` and use any other wp-env command.
