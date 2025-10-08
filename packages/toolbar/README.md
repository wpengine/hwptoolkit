# @wpengine/hwp-toolbar

> Framework-agnostic toolbar for headless WordPress applications

A lightweight, performant toolbar for headless WordPress. Works with any JavaScript framework or vanilla JS.

## Features

- 🎯 **Framework Agnostic** - Works with React, Vue, Svelte, or vanilla JavaScript
- ⚡ **Zero Dependencies** - Lightweight and fast
- 🔒 **Type Safe** - Full TypeScript support
- 🎨 **Themeable** - CSS custom properties for styling
- 🌓 **Dark Mode** - Automatic support

## Installation

```bash
npm install @wpengine/hwp-toolbar
```

## Quick Start

```javascript
import { Toolbar, VanillaRenderer } from '@wpengine/hwp-toolbar';
import '@wpengine/hwp-toolbar/styles';

// Create toolbar
const toolbar = new Toolbar({
  onPreviewChange: (enabled) => {
    console.log('Preview mode:', enabled);
  }
});

// Set WordPress context
toolbar.setWordPressContext({
  user: { id: 1, name: 'Admin' },
  site: { url: 'https://example.com', adminUrl: 'https://example.com/wp-admin' },
  post: { id: 123, title: 'Hello World', type: 'post', status: 'draft', slug: 'hello-world' }
});

// Render
const renderer = new VanillaRenderer(toolbar, 'toolbar');
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

## Framework Examples

### React

```tsx
import { useEffect, useRef } from 'react';
import { Toolbar, VanillaRenderer } from '@wpengine/hwp-toolbar';

function WordPressToolbar({ user, post, site }) {
  const ref = useRef<HTMLDivElement>(null);

  useEffect(() => {
    if (!ref.current) return;

    const toolbar = new Toolbar();
    toolbar.setWordPressContext({ user, post, site });
    const renderer = new VanillaRenderer(toolbar, ref.current);

    return () => {
      renderer.destroy();
      toolbar.destroy();
    };
  }, [user, post, site]);

  return <div ref={ref} />;
}
```

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

BSD-0-Clause
