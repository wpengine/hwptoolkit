/**
 * HWP Global Styles Integration
 */

import WPGlobalStylesFetcher from './core/hwp-global-styles-fetcher.js';
import WPBlocksIntegration from './core/hwp-blocks-integration.js';
import { debounce, scopeCSS, createStyleElement } from './utils/helpers.js';

// Export all components
export {
    WPGlobalStylesFetcher,
    WPBlocksIntegration,
    // Utility functions
    debounce,
    scopeCSS,
    createStyleElement
};

// Default export for convenient import
export default {
    WPGlobalStylesFetcher,
    WPBlocksIntegration
};

// Auto-initialize if in browser environment with data attributes
if (typeof window !== 'undefined' && document.currentScript) {
    const script = document.currentScript;

    // Check for auto-init attribute
    if (script.hasAttribute('data-auto-init')) {
        const endpoint = script.getAttribute('data-endpoint');

        if (endpoint) {
            document.addEventListener('DOMContentLoaded', () => {
                const options = {};

                // Get options from data attributes
                if (script.hasAttribute('data-block-selector')) {
                    options.blockSelector = script.getAttribute('data-block-selector');
                }

                if (script.hasAttribute('data-cache-disabled')) {
                    options.useCaching = false;
                }

                const integration = new WPBlocksIntegration(endpoint, options);
                integration.init()
                    .then(() => {
                        console.log('WordPress Global Styles Integration auto-initialized');
                    })
                    .catch(error => {
                        console.error('Auto-initialization failed:', error);
                    });
            });
        }
    }
}