# Toolbar Demo - Vanilla JavaScript + Vite

> A complete example of `@wpengine/hwp-toolbar` with vanilla JavaScript and Vite

This example demonstrates how to integrate the Headless WordPress Toolbar into a vanilla JavaScript application using Vite as the build tool.

## Features

- ✅ Vanilla JavaScript (no framework)
- ✅ Vite for fast development
- ✅ TypeScript support (types available)
- ✅ WordPress toolbar integration
- ✅ State management example
- ✅ Custom node registration
- ✅ Dark/light mode support
- ✅ wp-env configuration

## Prerequisites

- Node.js >= 18
- pnpm (for workspace setup)
- Docker (for wp-env)

## Quick Start

From the example directory:

```bash
# Install dependencies and start WordPress + Vite
npm run example:build

# Or, if already set up:
npm run example:start
```

The example will be available at:
- **Frontend**: http://localhost:3000
- **WordPress**: http://localhost:8888
- **WordPress Admin**: http://localhost:8888/wp-admin (`admin` / `password`)
- **GraphQL**: http://localhost:8888/?graphql

## Project Structure

```
toolbar-demo/
├── example-app/           # Vite application
│   ├── src/
│   │   ├── main.js       # Toolbar implementation
│   │   └── style.css     # Demo styles
│   ├── index.html        # Entry point
│   ├── package.json
│   └── vite.config.js
├── wp-env/               # WordPress environment
│   └── db/               # Database
├── .wp-env.json          # wp-env configuration
├── package.json
└── README.md
```

## What This Example Shows

### 1. Basic Integration

```javascript
import { Toolbar, VanillaRenderer } from '@wpengine/hwp-toolbar';
import '@wpengine/hwp-toolbar/styles';

const toolbar = new Toolbar({
  onPreviewChange: (enabled) => {
    console.log('Preview mode:', enabled);
  }
});

const renderer = new VanillaRenderer(toolbar, 'toolbar');
```

### 2. Real WordPress Integration

The example fetches real data from WordPress:

```javascript
// Fetch WordPress user via REST API
const response = await fetch('http://localhost:8888/?rest_route=/wp/v2/users/1');
const user = await response.json();

toolbar.setState({
  user: {
    id: user.id,
    name: user.name,
    email: user.email
  },
  site: {
    url: 'http://localhost:8888',
    adminUrl: 'http://localhost:8888/wp-admin'
  }
});
```

### 3. GraphQL Posts

Fetch posts using WPGraphQL:

```javascript
const response = await fetch('http://localhost:8888/?graphql', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    query: `
      query GetPosts {
        posts(first: 5) {
          nodes {
            databaseId
            title
            slug
            status
          }
        }
      }
    `
  })
});

const { data } = await response.json();
// Use posts to populate toolbar
```

### 4. Custom Nodes

```javascript
toolbar.register('home', 'Home', () => {
  window.location.href = '/';
});
```

### 5. State Subscription

```javascript
toolbar.subscribe((nodes, state) => {
  console.log('Toolbar state updated:', state);
});
```

## Available Scripts

```bash
# Start development server + WordPress
npm run example:start

# Stop WordPress
npm run example:stop

# Rebuild everything from scratch
npm run example:prune

# Just run Vite dev server (requires WordPress running)
npm run example:dev

# WordPress-only commands
npm run wp:start
npm run wp:stop
npm run wp:destroy
```

## WordPress Setup

The wp-env configuration includes:

- WordPress with WPGraphQL plugin
- Admin credentials: `admin` / `password`
- GraphQL endpoint: `http://localhost:8888/?graphql`
- REST API endpoint: `http://localhost:8888/?rest_route=/wp/v2/...`
- Pretty permalinks enabled
- CORS headers enabled for localhost:3000

## Using with Vite

Vite provides:
- Hot module replacement (HMR)
- Fast dev server
- ES modules support
- No build step needed for development

The toolbar package is imported via workspace protocol:
```json
{
  "dependencies": {
    "@wpengine/hwp-toolbar": "workspace:*"
  }
}
```

## Styling

The example imports the base toolbar styles:

```javascript
import '@wpengine/hwp-toolbar/styles';
```

Custom styles can override CSS variables:

```css
:root {
  --hwp-toolbar-bg: #1a1a1a;
  --hwp-toolbar-primary: #00a0d2;
}
```

## Learn More

- [@wpengine/hwp-toolbar documentation](../../../packages/toolbar/README.md)
- [Vite Documentation](https://vitejs.dev/)
- [WPGraphQL](https://www.wpgraphql.com/)

## License

BSD-0-Clause
