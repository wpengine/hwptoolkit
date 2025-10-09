import { Toolbar } from '@wpengine/hwp-toolbar';

/**
 * Singleton toolbar instance
 * This ensures the same toolbar state is shared across the app
 */
export const toolbar = new Toolbar({
  onPreviewChange: (enabled) => {
    console.log('Preview mode:', enabled);
    // In a real app, you'd integrate with Next.js draft mode here
    // router.push(`/api/preview?enabled=${enabled}`);
  },
});

/**
 * Register custom nodes
 */
toolbar.register('home', 'Home', () => {
  window.location.href = '/';
});

toolbar.register('demo-path', 'examples/next/toolbar-demo');
