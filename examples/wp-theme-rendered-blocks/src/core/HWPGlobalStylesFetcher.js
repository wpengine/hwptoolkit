/**
 * WordPress Global Stylesheet Fetcher
 * A vanilla JavaScript implementation to fetch and apply global stylesheets
 * from WordPress via GraphQL for headless frontend environments.
 */

class HWPGlobalStylesFetcher {
    /**
     * Constructor
     * @param {string} graphqlEndpoint - The WordPress GraphQL endpoint URL
     */
    constructor(graphqlEndpoint) {
        this.graphqlEndpoint = graphqlEndpoint;
    }

    /**
     * Fetches the global stylesheet from WordPress
     * @param {Array} types - Optional array of style types to fetch (VARIABLES, PRESETS, STYLES, etc.)
     * @returns {Promise<string>} The stylesheet content
     */
    async fetchGlobalStylesheet(types = null) {
        // Build the query - with or without types parameter
        let query;
        if (types && Array.isArray(types) && types.length > 0) {
            query = `
        {
          globalStylesheet(types: [${types.join(', ')}])
        }
      `;
        } else {
            query = `
        {
          globalStylesheet
        }
      `;
        }

        try {
            const response = await fetch(this.graphqlEndpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    variables: {},
                    query: query,
                }),
            });

            if (!response.ok) {
                throw new Error(`GraphQL request failed: ${response.status} ${response.statusText}`);
            }

            const json = await response.json();

            if (json.errors) {
                const errors = json.errors.map(error => error.message);
                throw new Error(`GraphQL errors: ${errors.join(', ')}`);
            }

            return json.data.globalStylesheet;
        } catch (error) {
            console.error('Error fetching global stylesheet:', error);
            throw error;
        }
    }

    /**
     * Apply the stylesheet to the document
     * @param {string} stylesheet - The CSS stylesheet content
     * @param {string} id - Optional ID for the style element
     * @returns {HTMLStyleElement} The created style element
     */
    applyStylesheet(stylesheet, id = 'wp-global-stylesheet') {
        // Remove existing style element if it exists
        const existingStyle = document.getElementById(id);
        if (existingStyle) {
            existingStyle.parentNode.removeChild(existingStyle);
        }

        // Create and add the new style element
        const styleElement = document.createElement('style');
        styleElement.id = id;
        styleElement.textContent = stylesheet;
        document.head.appendChild(styleElement);

        return styleElement;
    }

    /**
     * Fetch and apply the global stylesheet in one operation
     * @param {Array} types - Optional array of style types to fetch
     * @returns {Promise<HTMLStyleElement>} The created style element
     */
    async fetchAndApply(types = null) {
        try {
            const stylesheet = await this.fetchGlobalStylesheet(types);
            return this.applyStylesheet(stylesheet);
        } catch (error) {
            console.error('Failed to fetch and apply global stylesheet:', error);
            throw error;
        }
    }

    /**
     * Save the stylesheet to localStorage for offline use
     * @param {string} stylesheet - The CSS stylesheet content
     * @param {string} key - Key to use in localStorage
     */
    saveStylesheetToCache(stylesheet, key = 'wp-global-stylesheet') {
        try {
            localStorage.setItem(key, stylesheet);
            localStorage.setItem(`${key}-timestamp`, Date.now().toString());
        } catch (error) {
            console.warn('Failed to cache stylesheet to localStorage:', error);
        }
    }

    /**
     * Get cached stylesheet from localStorage
     * @param {string} key - Key used in localStorage
     * @param {number} maxAge - Maximum age in milliseconds (default: 1 hour)
     * @returns {string|null} The cached stylesheet or null if not found/expired
     */
    getCachedStylesheet(key = 'wp-global-stylesheet', maxAge = 3600000) {
        try {
            const stylesheet = localStorage.getItem(key);
            const timestamp = localStorage.getItem(`${key}-timestamp`);

            if (!stylesheet || !timestamp) {
                return null;
            }

            // Check if cache is still valid
            const age = Date.now() - parseInt(timestamp, 10);
            if (age > maxAge) {
                // Clear expired cache
                localStorage.removeItem(key);
                localStorage.removeItem(`${key}-timestamp`);
                return null;
            }

            return stylesheet;
        } catch (error) {
            console.warn('Failed to retrieve cached stylesheet:', error);
            return null;
        }
    }

    /**
     * Initialize with caching support
     * @param {Array} types - Optional array of style types to fetch
     * @param {boolean} useCache - Whether to use localStorage caching
     * @param {number} cacheMaxAge - Maximum cache age in milliseconds
     * @returns {Promise<HTMLStyleElement>} The created style element
     */
    async init(types = null, useCache = true, cacheMaxAge = 3600000) {
        let stylesheet = null;

        // Try to get from cache first if enabled
        if (useCache) {
            stylesheet = this.getCachedStylesheet('wp-global-stylesheet', cacheMaxAge);
            if (stylesheet) {
                console.log('Using cached global stylesheet');
                return this.applyStylesheet(stylesheet);
            }
        }

        // Fetch from server if not in cache or cache disabled
        stylesheet = await this.fetchGlobalStylesheet(types);

        // Cache the result if caching is enabled
        if (useCache) {
            this.saveStylesheetToCache(stylesheet);
        }

        // Apply the stylesheet
        return this.applyStylesheet(stylesheet);
    }
}

// Example usage
document.addEventListener('DOMContentLoaded', async () => {
    const stylesManager = new HWPGlobalStylesFetcher('https://your-wordpress-site.com/graphql'); // TODO: example.

    try {
        // Initialize with caching (refresh every hour)
        await stylesManager.init(null, true, 3600000);
        console.log('WordPress global styles loaded successfully');

        // Optional: Dispatch an event when styles are loaded
        document.dispatchEvent(new CustomEvent('wp-styles-loaded'));
    } catch (error) {
        console.error('Failed to load WordPress global styles:', error);
    }
});

// Export for module usage
export default HWPGlobalStylesFetcher;