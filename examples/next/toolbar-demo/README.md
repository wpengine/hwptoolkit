# Toolbar Demo - React Hooks

React hooks example with Next.js App Router demonstrating the Headless WordPress Toolbar.

## Features

- React hooks (`useToolbar`, `useToolbarState`, `useToolbarNodes`)
- Next.js App Router (App Directory)
- TypeScript
- Framework-agnostic state management
- WordPress context integration

## Quick Start

```bash
# Install dependencies (from monorepo root)
pnpm install

# Start WordPress (from this directory)
npx wp-env start

# Start Next.js dev server (from example-app directory)
cd example-app
pnpm dev
```

Open:
- Next.js App: [http://localhost:3001](http://localhost:3001)
- WordPress Admin: [http://localhost:8001/wp-admin](http://localhost:8001/wp-admin)
  - Username: `admin`
  - Password: `password`

## Key Files

- `lib/toolbar.ts` - Singleton toolbar instance
- `lib/wordpress.ts` - WordPress REST API integration
- `app/components/Toolbar.tsx` - Toolbar component using React hooks
- `app/page.tsx` - Demo page with WordPress integration

## Usage Pattern

```tsx
import { toolbar } from '@/lib/toolbar';
import { useToolbar } from '@wpengine/hwp-toolbar/react';

function MyComponent() {
  const { state, nodes } = useToolbar(toolbar);

  return (
    <div>
      {nodes.map(node => (
        <button key={node.id} onClick={node.onClick}>
          {typeof node.label === 'function' ? node.label() : node.label}
        </button>
      ))}
    </div>
  );
}
```

## State Management

The toolbar follows modern state management patterns (TanStack/Zustand):

1. **Framework-agnostic core** - `Toolbar` class manages state
2. **React integration** - Hooks subscribe to state changes
3. **Full UI control** - You render the toolbar however you want

## WordPress Integration

The demo integrates with a local WordPress instance via REST API.

### Demo Authentication

By default, the demo uses **mock authentication** to simplify the setup:

```ts
// Demo user (no actual WordPress login required)
const user = await getCurrentUser(); // Returns mock user

// Public posts endpoint (no authentication needed)
const posts = await getPosts(); // Fetches from /wp/v2/posts
```

This lets you test the toolbar immediately without WordPress login complexity.

### Production Authentication

For production use, implement proper authentication using **WordPress Application Passwords**:

1. **Generate Application Password**:
   - Go to WordPress Admin → Users → Profile
   - Scroll to "Application Passwords"
   - Create a new password

2. **Configure Environment**:
   ```bash
   cp .env.local.example .env.local
   # Add your credentials to .env.local
   ```

3. **Update `lib/wordpress.ts`**:
   ```ts
   const auth = btoa(`${process.env.WP_USERNAME}:${process.env.WP_APP_PASSWORD}`);

   export async function fetchFromWordPress(endpoint: string) {
     const response = await fetch(`${WP_API_URL}/wp-json${endpoint}`, {
       headers: {
         'Authorization': `Basic ${auth}`
       }
     });
     // ...
   }
   ```

### Features Demonstrated

- **WordPress Connection**: Fetch data from WordPress REST API
- **Post Management**: Load and display WordPress posts
- **Dynamic Toolbar**: Nodes appear/disappear based on WordPress context
- **Error Handling**: Clear error messages for connection issues

### Troubleshooting

**CORS Errors**
- Make sure wp-env is running: `npx wp-env start`
- Check WordPress is accessible at http://localhost:8001
- MU plugin should enable CORS headers automatically

**Connection Failed**
- Verify wp-env is running: `npx wp-env start`
- Check the port matches `.wp-env.json` (default: 8001)
- Try accessing http://localhost:8001 in your browser

**No Posts Available**
- Create sample posts in WordPress Admin
- Or run: `npx wp-env run cli wp post generate --count=5`
