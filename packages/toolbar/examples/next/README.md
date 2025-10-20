# Next.js Toolbar Demo

In this example we show how to integrate the Headless WordPress Toolbar into a Next.js application using React hooks and WordPress backend using WPGraphQL.

## Table of Contents

## Getting Started

> [!IMPORTANT]
> Docker Desktop needs to be installed to run WordPress locally.

1. Create a `.env.local` file in the `examples/next/toolbar-demo` directory with the following content:

```env
   NEXT_PUBLIC_WP_URL=http://localhost:8888
```

2. Run `npm run example:setup` to install dependencies and configure the local WP server.
3. Run `npm run example:start` to start the WP server and Next.js development server.

> [!NOTE]
> Port 8888 is the default port for wp-env.

The example will be available at:

- **Frontend**: http://localhost:3000
- **WordPress**: http://localhost:8888
- **WordPress Admin**: http://localhost:8888/wp-admin (`admin` / `password`)
- **GraphQL**: http://localhost:8888/?graphql

> [!NOTE]
> When you kill the long running process this will not shutdown the local WP instance, only Next.js. You must run `npm run example:stop` to kill the local WP server.

## What This Example Shows

### 1. React Hooks Integration

```tsx
import { toolbar } from "@/lib/toolbar";
import { useToolbar } from "@wpengine/hwp-toolbar/react";

function MyComponent() {
  const { state, nodes } = useToolbar(toolbar);

  return <div>{/* Toolbar UI */}</div>;
}
```

### 2. WordPress Context Integration

```tsx
import { fetchWordPressUser } from "@/lib/wordpress";

// Fetch user and set WordPress context
const user = await fetchWordPressUser();
toolbar.setWordPressContext({
  user: {
    id: user.id,
    name: user.name,
    email: user.email,
  },
  site: {
    url: "http://localhost:8888",
    adminUrl: "http://localhost:8888/wp-admin",
  },
});
```

### 3. State Management

```tsx
const { state, nodes } = useToolbar(toolbar);

// Subscribe to state changes
useEffect(() => {
  console.log("Toolbar state:", state);
}, [state]);
```

### 4. Custom Node Registration

```tsx
toolbar.register("home", "Home", () => {
  router.push("/");
});
```

## Features

- React hooks (`useToolbar`, `useToolbarState`, `useToolbarNodes`)
- Next.js App Router (App Directory)
- TypeScript support
- Framework-agnostic state management
- WordPress context integration
- Real WordPress data integration
- WPGraphQL support

## Project Structure

```
toolbar-demo/
├── example-app/           # Next.js application
│   ├── app/
│   │   ├── components/    # React components
│   │   │   └── Toolbar.tsx
│   │   ├── globals.css
│   │   ├── layout.tsx
│   │   └── page.tsx      # Demo page
│   ├── lib/
│   │   ├── toolbar.ts    # Singleton toolbar instance
│   │   └── wordpress.ts  # WordPress API integration
│   ├── next.config.ts
│   ├── package.json
│   └── tsconfig.json
├── wp-env/               # WordPress environment
├── .wp-env.json          # wp-env configuration
├── package.json
└── README.md
```

## Available Scripts

```bash
# Initial setup - installs dependencies and starts WordPress
npm run example:setup

# Start development servers (WordPress + Next.js)
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

## Key Files

- **`lib/toolbar.ts`** - Singleton toolbar instance configuration
- **`lib/wordpress.ts`** - WordPress REST API integration helpers
- **`app/components/Toolbar.tsx`** - Main toolbar component using React hooks
- **`app/page.tsx`** - Demo page showing WordPress integration

## WordPress Setup

The wp-env configuration includes:

- WordPress with WPGraphQL plugin
- Admin credentials: `admin` / `password`
- GraphQL endpoint: `http://localhost:8888/?graphql`
- REST API endpoint: `http://localhost:8888/?rest_route=/wp/v2/...`
- Pretty permalinks enabled
- CORS headers enabled for localhost:3000

## TypeScript Support

The example includes full TypeScript support with proper types for:

- Toolbar state and nodes
- WordPress API responses
- React hook return types

## Trouble Shooting

To reset the WP server and re-run setup you can run `npm run example:prune` and confirm "Yes" at any prompts.

## Learn More

- [@wpengine/hwp-toolbar documentation](../../../packages/toolbar/README.md)
- [Next.js Documentation](https://nextjs.org/docs)
- [React Hooks Documentation](https://reactjs.org/docs/hooks-intro.html)
- [WPGraphQL](https://www.wpgraphql.com/)

## License

BSD-3-Clause
