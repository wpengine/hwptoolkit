#!/usr/bin/env node

const fs = require('fs');
const path = require('path');
const { program } = require('commander');

// Load environment variables from .env.local if not in production
if (process.env.NODE_ENV !== 'production') {
    try {
        const dotenv = require('dotenv');
        // First check for .env.local, then fallback to .env
        const envFile = fs.existsSync(path.resolve(process.cwd(), '.env.local'))
            ? '.env.local'
            : '.env';

        dotenv.config({ path: path.resolve(process.cwd(), envFile) });
    } catch (error) {
        console.warn('dotenv is not installed. Environment variables must be set manually.');
    }
}

// Configure CLI options with defaults from environment variables
program
    .name('fetch-wp-styles')
    .description('Fetch WordPress global stylesheets for Next.js integration')
    .option(
        '-e, --endpoint <url>',
        'WordPress GraphQL endpoint URL',
        process.env.WORDPRESS_GRAPHQL_ENDPOINT
    )
    .option(
        '-o, --output <path>',
        'Output CSS file path',
        process.env.WP_GLOBAL_STYLES_OUTPUT || 'public/hwp-global-styles.css'
    )
    .option(
        '-t, --types <types...>',
        'Style types to fetch (variables, presets, styles, base-layout-styles)',
        parseStyleTypes(process.env.WP_GLOBAL_STYLES_TYPES)
    )
    .option(
        '-v, --verbose',
        'Show verbose output',
        process.env.WP_GLOBAL_STYLES_VERBOSE === 'true'
    )
    .option(
        '--minify <boolean>',
        'Minify CSS output',
        parseBooleanOption(process.env.WP_GLOBAL_STYLES_MINIFY, process.env.NODE_ENV === 'production')
    )
    .parse(process.argv);

const options = program.opts();

// Validate required options
if (!options.endpoint) {
    console.error('Error: WordPress GraphQL endpoint is required. Use --endpoint option or set WORDPRESS_GRAPHQL_ENDPOINT env variable.');
    process.exit(1);
}

async function fetchGlobalStyles() {
    const { endpoint, output, types, verbose, minify } = options;

    if (verbose) {
        console.log(`Fetching global styles from: ${endpoint}`);
        console.log(`Requested style types: ${types.join(', ')}`);
        console.log(`Output will be saved to: ${output}`);
        console.log(`Minification enabled: ${minify}`);
    }

    // Convert type strings to enum values for the GraphQL query
    const typeEnums = types.map(type => {
        // Convert to uppercase and replace dashes with underscores for GraphQL enum format
        return type.toUpperCase().replace(/-/g, '_');
    });

    try {
        // Prepare the GraphQL query with type selection
        const query = `
      query FetchGlobalStylesheet {
        globalStylesheet(types: [${typeEnums.join(', ')}])
      }
    `;

        const response = await fetch(endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ query }),
        });

        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }

        const json = await response.json();

        if (json.errors) {
            throw new Error(`GraphQL errors: ${json.errors.map(e => e.message).join(', ')}`);
        }

        if (!json.data || !json.data.globalStylesheet) {
            throw new Error('No stylesheet data returned from the API');
        }

        let styles = json.data.globalStylesheet;

        // Process the CSS if needed (minify, etc.)
        if (minify) {
            // Simple minification - remove comments, whitespace, and newlines
            styles = styles
                .replace(/\/\*[\s\S]*?\*\//g, '')  // Remove comments
                .replace(/\s+/g, ' ')              // Collapse whitespace
                .replace(/\s*([{}:;,])\s*/g, '$1') // Remove spaces around symbols
                .trim();
        }

        // Make sure the directory exists
        const outputDir = path.dirname(output);
        fs.mkdirSync(path.resolve(process.cwd(), outputDir), { recursive: true });

        // Write the CSS file
        fs.writeFileSync(path.resolve(process.cwd(), output), styles);

        if (verbose) {
            console.log(`Global styles successfully saved to: ${output} (${styles.length} bytes)`);
        } else {
            console.log(`Global styles successfully saved to: ${output}`);
        }
    } catch (error) {
        console.error('Error fetching global styles:', error);
        process.exit(1);
    }
}

// Helper function to parse style types from env variable
function parseStyleTypes(typesString) {
    if (!typesString) {
        return ['variables', 'presets', 'styles', 'base-layout-styles'];
    }

    return typesString.split(',').map(type => type.trim());
}

// Helper function to parse boolean options.
function parseBooleanOption(value, defaultValue = false) {
    if (value === undefined || value === null) {
        return defaultValue;
    }

    if (typeof value === 'boolean') {
        return value;
    }

    if (typeof value === 'string') {
        return value.toLowerCase() === 'true';
    }

    return Boolean(value);
}

fetchGlobalStyles();
