import fs from 'fs';


// Should be an API endpoint
const wordPressRoutes = JSON.parse(fs.readFileSync('../lib/fixtures/site.json', 'utf-8'));
import { generateRouteMappings, getAllAvailablePaths, writeRouteMappingsToFile } from '../lib/route-generate.js';

const availablePaths = getAllAvailablePaths('./src/routes/wordpress', '+page.svelte');
const routeMappings = generateRouteMappings(wordPressRoutes, availablePaths, '/wordpress/');

writeRouteMappingsToFile(routeMappings, './route-mappings.json');
console.info('Route mapping generated successfully');
