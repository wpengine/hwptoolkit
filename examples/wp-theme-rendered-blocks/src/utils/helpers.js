/**
 * WordPress Global Styles Integration
 * Utility helper functions
 */

/**
 * Creates a debounced function that delays invoking func until after wait milliseconds
 * @param {Function} func - Function to debounce
 * @param {number} wait - Milliseconds to wait
 * @returns {Function} Debounced function
 */
export function debounce(func, wait = 300) {
    let timeout;

    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };

        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Scopes CSS rules to a specific selector
 * @param {string} css - The original CSS
 * @param {string} scopeSelector - The selector to scope styles to
 * @returns {string} Scoped CSS
 */
export function scopeCSS(css, scopeSelector) {
    // Simple CSS scoping implementation
    return css
        .replace(/}[\s]?/g, '}\n') // Ensure each rule is on a new line
        .split('\n')
        .filter(line => line.trim().length > 0)
        .map(line => {
            // Skip comments, keyframes, media queries, and imports
            if (
                line.includes('/*') ||
                line.includes('@keyframes') ||
                line.includes('@media') ||
                line.includes('@import') ||
                line.includes('@font-face')
            ) {
                return line;
            }

            const ruleSplit = line.split('{');
            if (ruleSplit.length < 2) return line;

            const selectors = ruleSplit[0].split(',');
            const rules = ruleSplit.slice(1).join('{');

            const scopedSelectors = selectors.map(selector => {
                selector = selector.trim();
                // Don't scope html and body selectors, just combine them
                if (selector === 'html' || selector === 'body') {
                    return `${scopeSelector}`;
                }
                // Add scope to each selector
                return `${scopeSelector} ${selector}`;
            });

            return `${scopedSelectors.join(', ')} {${rules}`;
        })
        .join('\n');
}

/**
 * Creates a style element with the given ID and content
 * @param {string} css - CSS content
 * @param {string} id - ID for the style element
 * @returns {HTMLStyleElement} The created style element
 */
export function createStyleElement(css, id) {
    // Remove existing style element if it exists
    const existingStyle = document.getElementById(id);
    if (existingStyle) {
        existingStyle.parentNode.removeChild(existingStyle);
    }

    // Create new style element
    const styleElement = document.createElement('style');
    styleElement.id = id;
    styleElement.textContent = css;

    // Add to document head
    document.head.appendChild(styleElement);

    return styleElement;
}

/**
 * Safely parses JSON with error handling
 * @param {string} jsonString - JSON string to parse
 * @param {*} fallback - Fallback value if parsing fails
 * @returns {*} Parsed object or fallback value
 */
export function safeJSONParse(jsonString, fallback = {}) {
    try {
        return JSON.parse(jsonString);
    } catch (error) {
        console.warn('Failed to parse JSON:', error);
        return fallback;
    }
}

/**
 * Extracts color value from a WordPress color class
 * @param {Element} element - DOM element to check
 * @returns {Object} Object with text and background colors
 */
export function extractWPColors(element) {
    const colors = {
        text: null,
        background: null
    };

    if (!element) return colors;

    // Extract from class names
    Array.from(element.classList).forEach(className => {
        if (className.startsWith('has-') && className.endsWith('-color')) {
            colors.text = className.replace('has-', '').replace('-color', '');
        }

        if (className.startsWith('has-') && className.endsWith('-background')) {
            colors.background = className.replace('has-', '').replace('-background', '');
        }
    });

    // Extract from inline style
    const style = element.style;
    if (style.color) colors.text = style.color;
    if (style.backgroundColor) colors.background = style.backgroundColor;

    return colors;
}

/**
 * Check if the browser supports localStorage
 * @returns {boolean} True if localStorage is available
 */
export function isLocalStorageAvailable() {
    try {
        const test = 'test';
        localStorage.setItem(test, test);
        localStorage.removeItem(test);
        return true;
    } catch (e) {
        return false;
    }
}

/**
 * Generates a unique ID
 * @param {string} prefix - Optional prefix for the ID
 * @returns {string} Unique ID
 */
export function generateUniqueId(prefix = 'wp-') {
    return `${prefix}${Date.now()}-${Math.floor(Math.random() * 1000000)}`;
}
