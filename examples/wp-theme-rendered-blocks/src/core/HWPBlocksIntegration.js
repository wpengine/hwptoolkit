/**
 * WordPress Blocks Integration with Global Styles
 * A vanilla JavaScript implementation to apply global styles to WordPress blocks
 * in a headless frontend environment.
 */

import HWPGlobalStylesFetcher from './simplified-wp-styles.js';

/**
 * Enhances WordPress blocks with global styles
 */
class HWPBlocksStyleIntegration {
    /**
     * Constructor
     * @param {string} graphqlEndpoint - WordPress GraphQL endpoint URL
     * @param {Object} options - Configuration options
     */
    constructor(graphqlEndpoint, options = {}) {
        this.options = {
            blockSelector: '.wp-block',
            styleTypesToLoad: null, // null means load all
            useCaching: true,
            cacheMaxAge: 3600000, // 1 hour
            applyOnLoad: true,
            autoRefresh: false,
            autoRefreshInterval: 1800000, // 30 minutes
            ...options
        };

        this.stylesFetcher = new WPGlobalStylesFetcher(graphqlEndpoint);
        this.refreshTimer = null;
    }

    /**
     * Initialize the integration
     * @returns {Promise<void>}
     */
    async init() {
        try {
            // Load global styles
            await this.stylesFetcher.init(
                this.options.styleTypesToLoad,
                this.options.useCaching,
                this.options.cacheMaxAge
            );

            // Apply styles to existing blocks if requested
            if (this.options.applyOnLoad) {
                this.applyStylesToBlocks();
            }

            // Set up automatic refresh if enabled
            if (this.options.autoRefresh) {
                this.startAutoRefresh();
            }

            // Set up mutation observer to watch for new blocks
            this.setupBlockObserver();

            console.log('WordPress blocks style integration initialized');
        } catch (error) {
            console.error('Failed to initialize WordPress blocks style integration:', error);
            throw error;
        }
    }

    /**
     * Apply WordPress block classes and attributes to elements
     */
    applyStylesToBlocks() {
        const blocks = document.querySelectorAll(this.options.blockSelector);
        if (blocks.length === 0) {
            console.log('No WordPress blocks found on the page');
            return;
        }

        blocks.forEach(block => {
            this.enhanceBlock(block);
        });

        console.log(`Applied global styles to ${blocks.length} WordPress blocks`);
    }

    /**
     * Enhance a single block with additional classes and attributes
     * @param {Element} block - The block element to enhance
     */
    enhanceBlock(block) {
        // Skip if already processed
        if (block.hasAttribute('data-wp-styled')) {
            return;
        }

        // Get block name from class or data attribute
        let blockName = '';
        const blockClass = Array.from(block.classList).find(cls => cls.startsWith('wp-block-'));
        if (blockClass) {
            blockName = blockClass.replace('wp-block-', '');
        } else if (block.hasAttribute('data-block-name')) {
            blockName = block.getAttribute('data-block-name');
        }

        if (!blockName) {
            return; // Not a WP block or already processed
        }

        // Add theme classes that might be expected by global styles
        block.classList.add('has-global-styles');

        // Handle specific block types with special styling requirements
        if (blockName.includes('heading')) {
            this.enhanceHeadingBlock(block);
        } else if (blockName.includes('paragraph')) {
            this.enhanceParagraphBlock(block);
        } else if (blockName.includes('columns') || blockName.includes('column')) {
            this.enhanceColumnsBlock(block);
        } else if (blockName.includes('button')) {
            this.enhanceButtonBlock(block);
        } else if (blockName.includes('image')) {
            this.enhanceImageBlock(block);
        } else if (blockName.includes('cover')) {
            this.enhanceCoverBlock(block);
        }

        // Mark as processed
        block.setAttribute('data-wp-styled', 'true');
    }

    /**
     * Apply specific enhancements for heading blocks
     * @param {Element} block - The heading block
     */
    enhanceHeadingBlock(block) {
        const headingElement = block.querySelector('h1, h2, h3, h4, h5, h6') || block;

        // Apply font attributes if present
        ['font-size', 'font-family', 'font-weight', 'text-transform'].forEach(attr => {
            if (block.hasAttribute(`data-${attr}`)) {
                const value = block.getAttribute(`data-${attr}`);
                headingElement.style[attr] = value;
            }
        });

        // Apply theme class if specified
        if (block.hasAttribute('data-style')) {
            const style = block.getAttribute('data-style');
            headingElement.classList.add(`has-${style}-style`);
        }
    }

    /**
     * Apply specific enhancements for paragraph blocks
     * @param {Element} block - The paragraph block
     */
    enhanceParagraphBlock(block) {
        const paragraphElement = block.querySelector('p') || block;

        // Apply text and background color classes
        ['text-color', 'background-color'].forEach(attr => {
            if (block.hasAttribute(`data-${attr}`)) {
                const color = block.getAttribute(`data-${attr}`);
                paragraphElement.classList.add(`has-${color}-${attr.replace('-color', '')}`);
            }
        });

        // Apply text alignment
        if (block.hasAttribute('data-align')) {
            const align = block.getAttribute('data-align');
            paragraphElement.classList.add(`has-text-align-${align}`);
        }
    }

    /**
     * Apply specific enhancements for columns blocks
     * @param {Element} block - The columns block
     */
    enhanceColumnsBlock(block) {
        // Add specific classes for columns layout
        if (block.hasAttribute('data-columns')) {
            const columns = block.getAttribute('data-columns');
            block.classList.add(`has-${columns}-columns`);
        }

        // Apply vertical alignment
        if (block.hasAttribute('data-vertical-alignment')) {
            const alignment = block.getAttribute('data-vertical-alignment');
            block.classList.add(`are-vertically-aligned-${alignment}`);
        }
    }

    /**
     * Apply specific enhancements for button blocks
     * @param {Element} block - The button block
     */
    enhanceButtonBlock(block) {
        const buttonElement = block.querySelector('.wp-block-button__link') ||
            block.querySelector('a') ||
            block;

        // Add button classes
        buttonElement.classList.add('wp-block-button__link');

        // Apply button style
        if (block.hasAttribute('data-style')) {
            const style = block.getAttribute('data-style');
            block.classList.add(`is-style-${style}`);
        } else {
            // Default to filled style
            block.classList.add('is-style-fill');
        }

        // Apply color classes
        ['text-color', 'background-color'].forEach(attr => {
            if (block.hasAttribute(`data-${attr}`)) {
                const color = block.getAttribute(`data-${attr}`);
                buttonElement.classList.add(`has-${color}-${attr.replace('-color', '')}`);
            }
        });
    }

    /**
     * Apply specific enhancements for image blocks
     * @param {Element} block - The image block
     */
    enhanceImageBlock(block) {
        const imageElement = block.querySelector('img');
        if (!imageElement) return;

        // Add image size classes
        if (block.hasAttribute('data-size')) {
            const size = block.getAttribute('data-size');
            block.classList.add(`size-${size}`);
        }

        // Apply alignment
        if (block.hasAttribute('data-align')) {
            const align = block.getAttribute('data-align');
            block.classList.add(`align${align}`);
        }

        // Add figure wrapping if needed
        if (!block.querySelector('figure') && imageElement.parentNode === block) {
            const figure = document.createElement('figure');
            block.insertBefore(figure, imageElement);
            figure.appendChild(imageElement);

            // Add figcaption if caption is present
            if (block.hasAttribute('data-caption')) {
                const caption = block.getAttribute('data-caption');
                const figcaption = document.createElement('figcaption');
                figcaption.textContent = caption;
                figure.appendChild(figcaption);
            }
        }
    }

    /**
     * Apply specific enhancements for cover blocks
     * @param {Element} block - The cover block
     */
    enhanceCoverBlock(block) {
        // Add necessary classes
        block.classList.add('has-background-dim');

        // Apply overlay opacity
        if (block.hasAttribute('data-dimRatio')) {
            const ratio = block.getAttribute('data-dimRatio');
            block.classList.add(`has-background-dim-${ratio}`);
        }

        // Apply background position
        if (block.hasAttribute('data-position')) {
            const position = block.getAttribute('data-position');
            const [x, y] = position.split(' ');
            block.style.backgroundPosition = `${x} ${y}`;
        }

        // Apply minimum height
        if (block.hasAttribute('data-minHeight')) {
            const height = block.getAttribute('data-minHeight');
            block.style.minHeight = `${height}px`;
        }
    }

    /**
     * Refresh global styles
     * @returns {Promise<void>}
     */
    async refreshStyles() {
        try {
            // Clear cached styles
            if (this.options.useCaching) {
                localStorage.removeItem('wp-global-stylesheet');
                localStorage.removeItem('wp-global-stylesheet-timestamp');
            }

            // Fetch fresh styles
            await this.stylesFetcher.fetchAndApply(this.options.styleTypesToLoad);

            console.log('WordPress global styles refreshed');
        } catch (error) {
            console.error('Failed to refresh WordPress global styles:', error);
        }
    }

    /**
     * Start auto-refresh timer
     */
    startAutoRefresh() {
        if (this.refreshTimer) {
            clearInterval(this.refreshTimer);
        }

        this.refreshTimer = setInterval(() => {
            this.refreshStyles();
        }, this.options.autoRefreshInterval);

        console.log(`Auto-refresh enabled, interval: ${this.options.autoRefreshInterval / 60000} minutes`);
    }

    /**
     * Stop auto-refresh timer
     */
    stopAutoRefresh() {
        if (this.refreshTimer) {
            clearInterval(this.refreshTimer);
            this.refreshTimer = null;
            console.log('Auto-refresh disabled');
        }
    }

    /**
     * Set up mutation observer to watch for new blocks
     */
    setupBlockObserver() {
        const observer = new MutationObserver(mutations => {
            let newBlocksFound = false;

            mutations.forEach(mutation => {
                // Check for new nodes
                if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                    mutation.addedNodes.forEach(node => {
                        // Check if the node is an element and matches our block selector
                        if (node.nodeType === Node.ELEMENT_NODE) {
                            // Check if the node itself is a block
                            if (node.matches(this.options.blockSelector) && !node.hasAttribute('data-wp-styled')) {
                                this.enhanceBlock(node);
                                newBlocksFound = true;
                            }

                            // Check if the node contains blocks
                            const childBlocks = node.querySelectorAll(this.options.blockSelector);
                            if (childBlocks.length > 0) {
                                childBlocks.forEach(block => {
                                    if (!block.hasAttribute('data-wp-styled')) {
                                        this.enhanceBlock(block);
                                        newBlocksFound = true;
                                    }
                                });
                            }
                        }
                    });
                }
            });

            if (newBlocksFound) {
                console.log('Applied styles to dynamically added WordPress blocks');
            }
        });

        // Start observing the document
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }
}

// Example usage
document.addEventListener('DOMContentLoaded', async () => {
    const blockIntegration = new HWPBlocksStyleIntegration('https://your-wordpress-site.com/graphql', {
        blockSelector: '.wp-block, [data-block-type]',
        styleTypesToLoad: ['VARIABLES', 'PRESETS', 'STYLES'],
        useCaching: true,
        applyOnLoad: true,
        autoRefresh: false
    });

    try {
        await blockIntegration.init();

        // Example: refresh styles when theme changes
        document.addEventListener('theme-changed', () => {
            blockIntegration.refreshStyles();
        });

    } catch (error) {
        console.error('Block integration initialization failed:', error);
    }
});

// Export for module usage
export default HWPBlocksStyleIntegration;