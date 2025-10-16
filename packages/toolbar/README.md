# @wpengine/hwp-toolbar

> Framework-agnostic toolbar for headless WordPress applications

A lightweight, performant toolbar for headless WordPress. Works with any JavaScript framework or vanilla JS.

## Table of Contents

- [Features](#features)
- [Installation](#installation)
- [Quick Start](#quick-start)
  - [Example Projects](#example-projects)
  - [Vanilla JavaScript](#vanilla-javascript)
  - [React (Recommended)](#react-recommended)
- [Core API](#core-api)
  - [Toolbar Class](#toolbar-class)
  - [Renderers](#renderers)
- [Styling](#styling)
- [React Hooks API](#react-hooks-api)
  - [`useToolbar(toolbar)`](#usetoolbartoolbar)
  - [`useToolbarState(toolbar)`](#usetoolbarstatetoolbar)
  - [`useToolbarNodes(toolbar)`](#usetoolbarnodestoolbar)
- [Framework Examples](#framework-examples)
  - [Vue](#vue)
  - [Vanilla JavaScript](#vanilla-javascript-1)
- [TypeScript](#typescript)
- [Development](#development)
- [License](#license)


## Features

- 🎯 **Framework Agnostic** - Works with React, Vue, Svelte, or vanilla JavaScript
- ⚡ **Zero Dependencies** - Core library has no dependencies
- 🔒 **Type Safe** - Full TypeScript support
- 🪝 **React Hooks** - First-class React support with hooks
- 🎨 **Headless UI** - Full control over rendering and styling

## Installation

```bash
npm install @wpengine/hwp-toolbar
```

## Quick Start

### Example Projects

Check out the complete example projects in the `examples/` directory:

- **Next.js**: `examples/next/` - Full Next.js application with Apollo GraphQL integration
- **Vanilla JavaScript**: `examples/vanilla/` - Pure JavaScript implementation with demo HTML

Each example includes setup instructions and demonstrates different integration patterns.

### Vanilla JavaScript

```javascript
import { Toolbar, VanillaRenderer } from '@wpengine/hwp-toolbar';
import '@wpengine/hwp-toolbar/styles';

const toolbar = new Toolbar({
  onPreviewChange: (enabled) => {
    console.log('Preview mode:', enabled);
  }
});

toolbar.setWordPressContext({
  user: { id: 1, name: 'Admin' },
  site: { url: 'https://example.com', adminUrl: 'https://example.com/wp-admin' },
  post: { id: 123, title: 'Hello World', type: 'post', status: 'draft', slug: 'hello-world' }
});

const renderer = new VanillaRenderer(toolbar, 'toolbar');
```

### React (Recommended)

```tsx
import { Toolbar } from '@wpengine/hwp-toolbar';
import { useToolbar } from '@wpengine/hwp-toolbar/react';

const toolbar = new Toolbar({
  onPreviewChange: (enabled) => {
    console.log('Preview mode:', enabled);
  }
});

function MyToolbar() {
  const { state, nodes } = useToolbar(toolbar);

  return (
    <div className="toolbar">
      {nodes.map(node => (
        <button key={node.id} onClick={node.onClick}>
          {typeof node.label === 'function' ? node.label() : node.label}
        </button>
      ))}
      {state.user && <span>User: {state.user.name}</span>}
    </div>
  );
}
```

## API

### Toolbar

**Constructor**
```javascript
new Toolbar(config?)
```

**Config:**
- `onPreviewChange?: (enabled: boolean) => void` - Preview toggle callback

**Methods:**

```javascript
// Register a node
toolbar.register(id, label, onClick?)
toolbar.register('help', 'Help', () => window.open('/help'))

// Update state
toolbar.setState({ user, post, site, preview })

// Set WordPress context (helper)
toolbar.setWordPressContext({ user, post, site })

// Subscribe to changes
const unsubscribe = toolbar.subscribe((nodes, state) => {
  console.log('State changed:', state);
})

// Cleanup
toolbar.destroy()
```

### VanillaRenderer

```javascript
const renderer = new VanillaRenderer(toolbar, 'element-id');
// or
const renderer = new VanillaRenderer(toolbar, document.getElementById('toolbar'));

// Cleanup
renderer.destroy();
```

## Default Nodes

The toolbar includes three built-in nodes:

- **Edit Post** - Opens WordPress editor (visible when post + user)
- **WP Admin** - Dashboard link (visible when user exists)
- **Preview** - Toggle preview mode (visible when post or user)

## Styling

Import the base styles:

```javascript
import '@wpengine/hwp-toolbar/styles';
```

### Customization

Override CSS custom properties:

```css
:root {
  --hwp-toolbar-bg: #1a1a1a;
  --hwp-toolbar-primary: #00a0d2;
}
```

Available variables:
- `--hwp-toolbar-bg` - Background color
- `--hwp-toolbar-border` - Border color
- `--hwp-toolbar-text` - Text color
- `--hwp-toolbar-primary` - Primary button color
- `--hwp-toolbar-primary-hover` - Primary button hover
- And more...

## React Hooks API

### `useToolbar(toolbar)`

Returns both state and nodes in a single hook:

```tsx
import { useToolbar } from '@wpengine/hwp-toolbar/react';

function MyToolbar() {
  const { state, nodes } = useToolbar(toolbar);
  // Full control over rendering
}
```

### `useToolbarState(toolbar)`

Subscribe to toolbar state only:

```tsx
import { useToolbarState } from '@wpengine/hwp-toolbar/react';

function UserDisplay() {
  const state = useToolbarState(toolbar);
  return <div>{state.user?.name}</div>;
}
```

### `useToolbarNodes(toolbar)`

Subscribe to visible nodes only:

```tsx
import { useToolbarNodes } from '@wpengine/hwp-toolbar/react';

function ToolbarButtons() {
  const nodes = useToolbarNodes(toolbar);
  return (
    <>
      {nodes.map(node => (
        <button key={node.id} onClick={node.onClick}>
          {typeof node.label === 'function' ? node.label() : node.label}
        </button>
      ))}
    </>
  );
}
```

## Framework Examples

### Vue

```vue
<template>
  <div ref="toolbarRef" />
</template>

<script setup>
import { onMounted, onUnmounted, ref } from 'vue';
import { Toolbar, VanillaRenderer } from '@wpengine/hwp-toolbar';

const toolbarRef = ref(null);
let toolbar, renderer;

onMounted(() => {
  toolbar = new Toolbar();
  renderer = new VanillaRenderer(toolbar, toolbarRef.value);
});

onUnmounted(() => {
  renderer?.destroy();
  toolbar?.destroy();
});
</script>
```

### Vanilla JavaScript

See `demo.html` for a complete example.

## TypeScript

```typescript
import type {
  Toolbar,
  ToolbarState,
  WordPressUser,
  WordPressPost,
  WordPressSite
} from '@wpengine/hwp-toolbar';
```

## Development

```bash
# Build
npm run build

# Watch mode
npm run dev

# Clean
npm run clean

# View demo
open demo.html
```

## License

BSD-2-Clause
