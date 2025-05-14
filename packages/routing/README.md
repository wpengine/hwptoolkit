# @wpe/hwptoolkit-routing

<p align="center">
  <a aria-label="NPM version" href="https://www.npmjs.com/package/@wpe/hwptoolkit-routing">
    <img alt="" src="https://img.shields.io/npm/v/@wpe/hwptoolkit-routing?color=7e5cef&style=for-the-badge">
  </a>

  <a aria-label="License" href="https://github.com/wpengine/hwptoolkit/blob/main/LICENSE.md">
    <img alt="" src="https://img.shields.io/npm/l/@wpe/hwptoolkit-routing?color=7e5cef&style=for-the-badge">
  </a>
</p>

A routing module for headless WordPress applications using Next.js App Router. This package provides integration between WordPress and Next.js App Router, handling authentication, route matching, and secure token management.

## Features

- 🚦 Next.js App Router integration
- 🔐 Secure authentication with JWT tokens
- 🔄 Server-side rendering support
- 🌐 WordPress-compatible routing
- 🔒 Secure token storage and management
- 🚀 Server Actions for login/logout functionality

## Installation

```bash
# npm
npm install @wpe/hwptoolkit-routing

# pnpm
pnpm add @wpe/hwptoolkit-routing

# yarn
yarn add @wpe/hwptoolkit-routing
```

## Getting Started

### Environment Setup

First, configure your environment variables in your Next.js project:

```env
# .env.local
WORDPRESS_URL=https://your-wordpress-site.com
WORDPRESS_SECRET_KEY=your-secret-key
NEXT_PUBLIC_URL=http://localhost:3000 # Your frontend URL
```

### Configure WordPress Routes

Create an API route handler to manage authentication and WordPress routing:

```typescript
// app/api/hwp/[[...slug]]/route.ts
import { createRouteHandler } from '@wpe/hwptoolkit-routing/server';

// Export the route handler functions
export const { GET, POST } = createRouteHandler();
```

### Initialize Configuration

Configure the routing package in your application:

```typescript
// app/lib/routing.ts
import { setConfig } from '@wpe/hwptoolkit-routing';

export function initRouting() {
  setConfig({
    wpUrl: process.env.WORDPRESS_URL || '',
    secretKey: process.env.WORDPRESS_SECRET_KEY || '',
    graphqlEndpoint: '/graphql', // Default endpoint
  });
}
```

Call this initialization function in your app's entry point:

```typescript
// app/layout.tsx
import { initRouting } from './lib/routing';

export default function RootLayout({ children }) {
  // Initialize routing configuration
  initRouting();
  
  return (
    <html lang="en">
      <body>{children}</body>
    </html>
  );
}
```

## Authentication

### Login Form Example

```tsx
// app/login/page.tsx
'use client';

import { useState } from 'react';
import { onLogin } from '@wpe/hwptoolkit-routing/server-actions';

export default function LoginPage() {
  const [message, setMessage] = useState('');
  const [error, setError] = useState('');

  async function handleSubmit(formData: FormData) {
    const result = await onLogin(formData);
    
    if (result.error) {
      setError(result.error);
      setMessage('');
    } else {
      setMessage(result.message || 'Login successful');
      setError('');
      // Redirect or update UI as needed
    }
  }

  return (
    <div>
      <h1>Login</h1>
      
      {message && <div className="success">{message}</div>}
      {error && <div className="error">{error}</div>}
      
      <form action={handleSubmit}>
        <div>
          <label htmlFor="usernameEmail">Username or Email</label>
          <input 
            type="text" 
            id="usernameEmail" 
            name="usernameEmail" 
            required 
          />
        </div>
        
        <div>
          <label htmlFor="password">Password</label>
          <input 
            type="password" 
            id="password" 
            name="password" 
            required 
          />
        </div>
        
        <button type="submit">Login</button>
      </form>
    </div>
  );
}
```

### Logout Button Example

```tsx
// app/components/LogoutButton.tsx
'use client';

import { useRouter } from 'next/navigation';
import { onLogout } from '@wpe/hwptoolkit-routing/server-actions';

export default function LogoutButton() {
  const router = useRouter();
  
  async function handleLogout() {
    const success = await onLogout();
    
    if (success) {
      // Refresh the current page or navigate elsewhere
      router.refresh();
      // Optionally redirect
      // router.push('/login');
    }
  }
  
  return (
    <button onClick={handleLogout}>
      Logout
    </button>
  );
}
```

## API Reference

### Configuration

#### `setConfig(config)`

Sets the configuration for the routing package.

```typescript
import { setConfig } from '@wpe/hwptoolkit-routing';

setConfig({
  wpUrl: 'https://your-wordpress-site.com',
  secretKey: 'your-secret-key',
  graphqlEndpoint: '/graphql',
});
```

#### `getConfig()`

Gets the current configuration.

```typescript
import { getConfig } from '@wpe/hwptoolkit-routing';

const config = getConfig();
console.log(config.wpUrl);
```

### Server Actions

#### `onLogin(formData)`

Handles user login using a form data object containing `usernameEmail` and `password`.

```typescript
import { onLogin } from '@wpe/hwptoolkit-routing/server-actions';

// Use in a server action or form action
const result = await onLogin(formData);

if (result.error) {
  // Handle error
} else {
  // Handle success
}
```

#### `onLogout()`

Handles user logout by clearing authentication tokens.

```typescript
import { onLogout } from '@wpe/hwptoolkit-routing/server-actions';

const success = await onLogout();
```

### Route Handler

#### `createRouteHandler()`

Creates a route handler for use in Next.js App Router.

```typescript
import { createRouteHandler } from '@wpe/hwptoolkit-routing/server';

export const { GET, POST } = createRouteHandler();
```

### Utility Functions

#### `getRefreshToken()`

Gets the current refresh token from cookies (server-side only).

```typescript
import { getRefreshToken } from '@wpe/hwptoolkit-routing/server-actions';

const token = getRefreshToken();
```

## WordPress Setup Requirements

For this package to work correctly, your WordPress site needs:

1. **FaustWP Plugin**: Install and activate the [FaustWP plugin](https://wordpress.org/plugins/faustwp/)

2. **Secret Key Configuration**: In WordPress admin, navigate to FaustWP Settings and set up a secret key that matches your `WORDPRESS_SECRET_KEY` environment variable.

3. **Allowed Redirect Hosts**: Add your frontend URL to the list of allowed redirect hosts in FaustWP Settings.

4. **CORS Configuration**: Ensure your WordPress site has proper CORS headers to allow requests from your frontend application.

## TypeScript Support

This package includes TypeScript type definitions to help with development.

## License

See [LICENSE](https://github.com/wpengine/hwptoolkit/blob/main/LICENSE.md).

