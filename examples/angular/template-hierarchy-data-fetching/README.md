---
title: "Angular Template Hierarchy and Data Fetching"
description: "In this example we show how to implement the **WordPress Template Hierarchy in Angular** for use with a Headless WordPress backend using WPGraphQL."
---

# Angular Template Hierarchy and Data fetching Example

In this example we show how to implement the **WordPress Template Hierarchy in Angular** for use with a Headless WordPress backend using WPGraphQL.

## Getting Started

> [!IMPORTANT]
> **Docker Desktop** needs to be installed to run WordPress locally.

1. Run `npm run setup` to install dependencies and configure the local WP server.
2. **Configure CORS:** `setup` copies **`/backend/.env.example`** to **`/backend/.env`**, which sets the allowed frontend URL. Change it if your Angular app runs elsewhere:
    ```
    FRONTEND_URL=http://localhost:4200
    ```
    *(For more details on the backend service, please refer to the **`/backend/README.md`** file.)*
3. Run `npm run backend:dev` to start the backend server for template fetching at **http://localhost:3000/api/templates**
4. Run `npm run dev` to start the WordPress server and Angular development server.

> [!NOTE]
> When you kill the long running process this will not shutdown the local WP instance, only Angular. Run `npm run app:dev` to restart only the front end, or `npm run stop` to stop the local WP server.

## Trouble Shooting

1. I get "Page Not Found. Sorry, the page you are looking for does not exist. Please check the URL." when opening the Angular app and trying to navigate through it.
- Run `npm run backend:dev` and verify that **http://localhost:3000/api/templates** returns correct data.
- **Verify that `/backend/.env` exists and has the correct `FRONTEND_URL`** to resolve CORS issues.
- Check for any errors in the console.
2. In some cases, you might have to install **`@angular/cli`** globally. In `/example-app/` run `npm install -g @angular/cli@latest`
3. To reset the WP server, run `npm run clean` and confirm "Yes" at the prompt, then run `npm run setup` again.