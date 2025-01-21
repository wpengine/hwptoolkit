import { wordPressRoutes } from '../lib/wordpress-routes.js';
import { generateRouteMappings, getAllAvailablePaths, writeRouteMappingsToFile } from '../lib/route-generate.js';

const availablePaths = getAllAvailablePaths('./src/routes/wordpress', '+page.svelte');
const routeMappings = generateRouteMappings(wordPressRoutes, availablePaths, '/wordpress/');

writeRouteMappingsToFile(routeMappings, './route-mappings.json');
console.info('Route mapping generated successfully');
