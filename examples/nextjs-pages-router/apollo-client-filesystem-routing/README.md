# Next.js + WPGraphQL Headless CMS

This is a [Next.js](https://nextjs.org) project integrated with **WPGraphQL** and **WPGraphQL for ACF** to build a headless WordPress-powered site.

## Prerequisites

Before running this project, ensure you have the following:

- **Node.js** (version 18 or higher recommended)
- **npm**, **yarn**, **pnpm**, or **bun** package manager
- A **WordPress** instance with the following plugins installed and configured:
  - **WPGraphQL** ([wpgraphql.com](https://www.wpgraphql.com/))
  - **WPGraphQL for ACF** ([acf.wpgraphql.com](https://acf.wpgraphql.com/))

## WordPress Setup

### Required Plugins
1. **Install WPGraphQL**: This exposes your WordPress data via a GraphQL API.
2. **Install WPGraphQL for ACF**: This allows custom fields from ACF to be queried via GraphQL.

### Permalink Structure
This project follows a **custom permalink structure** to match Next.js file-based routing:

```
/posts/%postname%/
```

To set this, go to **Settings > Permalinks** in WordPress and choose **Custom Structure**, then enter the pattern.

### Custom Post Types (CPT)
With ACF installed you can create a new Custom Post type called **Movies**. 

The **rewrite structure** for custom post types should be set to:
```php
'rewrite' => array('slug' => 'movies', 'with_front' => false),
```

This ensures URLs for movies follow:

```
/movies/movie-title/
```
And not:

```
/posts/movies/movie-title/
```

## Categories
Categories are structured as:

```bash
/category/category-name/
```

To achieve this category structure make sure the `Category base` in the Settings -> Permalinks page has the value of `category`. 

The rest is handled dynamically through Next.js.

## Environment Variables
Create a `.env.local` file in the root of your project and add:

```ini
NEXT_PUBLIC_WORDPRESS_URL=<your_wordpress_url>
```
Replace <your_wordpress_url> with the actual WordPress site URL (e.g., https://your-wordpress-site.com).
**Do not include a trailing slash.**

## Project Structure
This project follows Next.js file-based routing. Based on the WordPress permalink structure, the key pages are:

```bash
src/pages
├── [slug].js         # Dynamic page for general posts or pages
├── _app.js           # Next.js global settings
├── _document.js      # Document structure
├── api
│   └── hello.js      # Example API route
├── category
│   ├── [category].js # Dynamic page for categories
│   └── index.js      # Categories index page
├── index.js          # Homepage (lists latest posts, movies, categories)
├── movies
│   ├── [slug].js     # Dynamic page for individual movies
│   └── index.js      # Movies listing page
└── posts
    ├── [slug].js     # Dynamic page for individual posts
    └── index.js      # Posts listing page
```
## Getting Started
1. Install Dependencies:

```bash
npm install
# or
yarn install
# or
pnpm install
# or
bun install
```
2. Run the Development Server:

```bash
npm run dev
# or
yarn dev
# or
pnpm dev
# or
bun dev
```

3. Access the Application: Open http://localhost:3000 in your browser.

