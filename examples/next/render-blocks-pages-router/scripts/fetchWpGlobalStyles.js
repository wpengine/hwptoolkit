const fs = require('fs');
const path = require('path');
const fetch = require('node-fetch'); // make sure node-fetch is installed

async function fetchWpGlobalStyles(endpoint, outputPath = 'public/hwp-global-styles.css', types = ['variables', 'presets', 'styles', 'base-layout-styles']) {
    const typeEnums = types.map(type => type.toUpperCase().replace(/-/g, '_'));

    const query = `
        query {
          globalStylesheet(types: [${typeEnums.join(', ')}])
        }
    `;

    try {
        const response = await fetch(endpoint, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({query}),
        });

        const json = await response.json();

        if (!json.data || !json.data.globalStylesheet) {
            throw new Error('No stylesheet data returned from the API.');
        }

        let css = json.data.globalStylesheet;

        // Optional minification for production use
        css = css.replace(/\/\*[\s\S]*?\*\//g, '')
            .replace(/\s+/g, ' ')
            .replace(/\s*([{}:;,])\s*/g, '$1')
            .trim();

        const absoluteOutputPath = path.resolve(process.cwd(), outputPath);
        fs.mkdirSync(path.dirname(absoluteOutputPath), {recursive: true});
        fs.writeFileSync(absoluteOutputPath, css);

        console.log(`Global styles saved to: ${absoluteOutputPath}`);
    } catch (error) {
        console.error('Error fetching global styles:', error.message);
    }
}
