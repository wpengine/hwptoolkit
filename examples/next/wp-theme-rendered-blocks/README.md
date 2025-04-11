# Example: WordPress Global Styles in Next.js

This example demonstrates how to **fetch and apply WordPress Global Styles** in a Next.js project using the `globalStylesheet` GraphQL field. These styles reflect your active theme’s typography, spacing, colors, and layout rules — ensuring that your frontend matches the WordPress editor and theme design.

This pattern is especially helpful for users migrating from **Faust.js**, where similar global styles were automatically applied using utilities like `getGlobalStyles`.

---

## How it works

- A script (`scripts/fetchWpGlobalStyles.js`) queries your WordPress site's GraphQL API for global styles.
- The styles are saved to `styles/hwp-global-styles.css`.
- The file is imported directly in your app and bundled at build time.

This ensures that all block-rendered content in your app inherits the same styling as it would inside the WordPress editor.

---

## Example usage

In `scripts/fetchWpGlobalStyles.js`, you define the fetch:

```js
fetchWpGlobalStyles(
  'https://your-wp-site.com/graphql',                      // WordPress GraphQL endpoint
  'styles/hwp-global-styles.css',                          // Output path
  ['variables', 'presets', 'styles', 'base-layout-styles'] // Style types to include
);
```

In your `pages/_app.js`, you include the styles:

```javascript
import '@wordpress/base-styles';         // WordPress foundational styles
import '@/styles/hwp-global-styles.css'; // Theme styles fetched from WP
import '@/styles/globals.css';           // Optional custom app styles
```

## Project structure

```
/
├── hwp-global-stylesheet/*             # WordPress Plugin.
├── pages/
│   ├── _app.js                         # Includes global styles.
│   └── index.js                        # Basic page.
├── styles/
│   ├── hwp-global-styles.css           # Output of the fetch script.
│   └── globals.css                     # Your optional global app styles.
├── package.json                        # Project dependencies and scripts.
```

## Installation and Setup

### Prerequisites

1. Node.js 18.18 or later
2. npm or another package manager
3. WPGraphql
4. HWP Global Stylesheet Plugin

### Clone the repository

```bash
git clone https://github.com/wpengine/hwptoolkit.git
cd examples/next/render-blocks-pages-router
```

### Install dependencies

```bash
npm install
```

### Start the development server

```bash
npm run dev
```

http://localhost:3000/ should render the blocks as shown below.
