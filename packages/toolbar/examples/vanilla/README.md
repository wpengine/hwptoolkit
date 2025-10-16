# Vanilla JavaScript Toolbar Demo

In this example we show how to integrate the Headless WordPress Toolbar into a vanilla JavaScript application using Vite as the build tool and WordPress backend using WPGraphQL.

## Getting Started

> [!IMPORTANT]
> Docker Desktop needs to be installed to run WordPress locally.


1. Create a `.env.local` file in the `examples/next/toolbar-demo` directory with the following content:
     ```env
VITE_FRONTEND_PORT=3000
VITE_WP_URL=http://localhost:8888
VITE_WP_PORT=8888
VITE_WP_TEST_PORT=8889
   ```

2. Run `npm run example:setup` to install dependencies and configure the local WP server.
3. Run `npm run example:start` `to start the WP server and Vite development server.

The example will be available at:
- **Frontend**: http://localhost:3000
- **WordPress**: http://localhost:8888
- **WordPress Admin**: http://localhost:8888/wp-admin (`admin` / `password`)
- **GraphQL**: http://localhost:8888/?graphql

> [!NOTE]
> When you kill the long running process this will not shutdown the local WP instance, only Vite. You must run `npm run example:stop` to kill the local WP server.

## What This Example Shows

### 1. Basic Toolbar Integration

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

### 2. WordPress Integration

The example fetches real data from WordPress:

```javascript
// Fetch WordPress user via REST API
const response = await fetch('http://localhost:8000/?rest_route=/wp/v2/users/1');
const user = await response.json();

toolbar.setWordPressContext({
  user: {
    id: user.id,
    name: user.name,
    email: user.email
  },
  site: {
    url: 'http://localhost:8000',
    adminUrl: 'http://localhost:8000/wp-admin'
  }
});
```

### 3. GraphQL Posts

```javascript
const response = await fetch('http://localhost:8000/?graphql', {
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
```

### 4. Custom Node Registration

```javascript
toolbar.register('home', 'Home', () => {
  window.location.href = '/';
});
```

### 5. State Management

```javascript
toolbar.subscribe((nodes, state) => {
  console.log('Toolbar state updated:', state);
});
```

## Features

- ✅ Vanilla JavaScript (no framework)
- ✅ Vite for fast development  
- ✅ TypeScript support (types available)
- ✅ WordPress toolbar integration
- ✅ State management example
- ✅ Custom node registration
- ✅ Dark/light mode support
- ✅ Real WordPress data integration
- ✅ WPGraphQL support

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
├── .wp-env.json          # wp-env configuration
├── package.json
└── README.md
```

## Available Scripts

```bash
# Initial setup - installs dependencies and starts WordPress
npm run example:setup

# Start development servers (WordPress + Vite)
npm run example:start

# Stop WordPress server
npm run example:stop

# Reset everything and start fresh
npm run example:prune

# WordPress-only commands
npm run wp:start
npm run wp:stop
npm run wp:destroy
```

## WordPress Setup

The wp-env configuration includes:
- WordPress with WPGraphQL plugin
- Admin credentials: `admin` / `password`
- GraphQL endpoint: `http://localhost:8000/?graphql`
- REST API endpoint: `http://localhost:8000/?rest_route=/wp/v2/...`
- Pretty permalinks enabled
- CORS headers enabled for localhost:3000

## Environment Configuration

The example uses standard ports (3000 for frontend, 8000 for WordPress) to match other hwptoolkit examples. 

To customize ports, create a `.env` file in the `example-app/` directory:

```
VITE_FRONTEND_PORT=3000
VITE_WP_URL=http://localhost:8888
VITE_WP_PORT=8888
VITE_WP_TEST_PORT=8889
```

## Styling

The example imports the base toolbar styles and adds custom demo styling:

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

## Trouble Shooting

To reset the WP server and re-run setup you can run `npm run example:prune` and confirm "Yes" at any prompts.

## Learn More

- [@wpengine/hwp-toolbar documentation](../../../packages/toolbar/README.md)
- [Vite Documentation](https://vitejs.dev/)
- [WPGraphQL](https://www.wpgraphql.com/)

## License

BSD-2-Clause
