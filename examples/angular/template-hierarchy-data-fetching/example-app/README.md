# Template Hierarchy and Data Fetching

This project was generated using [Angular CLI](https://github.com/angular/angular-cli) version 20.0.5.

## Development server

To start a local development server, run:

`npm run backend:start` - starts the backend server for template fetching at http://localhost:3000/api/templates

`npm run example:start` - starts WordPress and Angular project

Once the server is running, open your browser and navigate to `http://localhost:4200/`. The application will automatically reload whenever you modify any of the source files.

## FAQ
1. I get "Page Not Found
Sorry, the page you are looking for does not exist. Please check the URL." when opening the Angular app.
- Run `npm run backend:start` and verify that http://localhost:3000/api/templates returns correct data