# Example: Next.js App Router using the Fetch API


# Overview

This project is a complete example of a **headless WordPress site** powered by **Next.js App Router**, using the `fetch` API to retrieve content from the WordPress backend. It also includes a robust **local development environment** using [`wp-env`](https://www.npmjs.com/package/@wordpress/env) and comes with built-in database management tools.


## Installation

### Prerequisites

- Node.js (v18+ recommended)
- pnpm
- Docker (if you plan on running the example)

### Project Structure

```
/
├── example-app/            # Next.js App Router project
├── wp-env/                 # WordPress local environment setup
│   ├── wp-env.json
│   ├── uploads/            # WordPress uploaded media directory for example application
│   ├── db/                 # Example database export
│   ├── tools/              # Adminer for accessing the database
├── package.json            # Root scripts to control both systems
```

### Steps

#### Prerequisites
1. Clone the repo `git clone https://github.com/wpengine/hwptoolkit.git`
2. Make sure you have Docker running `e.g. docker ps`
3. Make sure you have a `.env` file in `examples/next/client-app-router-fetch-data/example-app` with the following:

```.env
NEXT_PUBLIC_WORDPRESS_URL=http://localhost:8888
```

#### Build and Start Process

1. Install project dependencies `pnpm install`
1. cd into the current directory `cd examples/next/client-app-router-fetch-data`
3. Then run `pnpm example:build` and this will do the following:
    - unzip `wp-env/uploads`, 
    - startup [wp-env](https://developer.wordpress.org/block-editor/getting-started/devenv/get-started-with-wp-env/) 
    - Import the database
    - Install Next.js dependencies
4. Finally to run `pnpm example:start` to start both the frontend and backend applications

Congratulations, WordPress should now be fully set up.

| Frontend | Admin                        |
|----------|------------------------------|
| [http://localhost:3000/](http://localhost:3000/) | [http://localhost:8888/wp-admin/](http://localhost:8888/wp-admin/) |

The login details for the admin is username "admin" and password "password"


> **Note** If permalinks are not working, you might need to re-save permalinks to clear the cache.
> **Note** You can also see <https://www.npmjs.com/package/@wordpress/env> for more details on how to use `wp-env`.

### Accessing the database

>**Note:** This is an optional feature and you can also use `pnpm wp:db:query` also to query the database. The database can be accessed at `http://localhost:8888/adminer.php` with the following details:

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
| `example:build`        | Unzips images, starts WordPress, imports the database                        |
| `example:dev`          | Runs the Next.js development server.                                         |
| `example:dev:install`  | Installs the Next.js packages.                                               |
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
