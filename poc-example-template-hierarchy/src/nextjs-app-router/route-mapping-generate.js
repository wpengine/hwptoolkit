import fs from 'fs';
import { generateRouteMappings, getAllAvailablePaths, writeRouteMappingsToFile } from '../lib/route-generate.js';

const siteOneRoutes = JSON.parse(fs.readFileSync('../lib/fixtures/site.json', 'utf-8'));
const siteOneAvailablePaths = getAllAvailablePaths('./app/wordpress', 'page.js');
const siteOneMappings = generateRouteMappings(siteOneRoutes, siteOneAvailablePaths, '/wordpress/');


// Example of a second site
const siteTwoRoutes = JSON.parse(fs.readFileSync('../lib/fixtures/site.2.json', 'utf-8'));
const siteTwoAvailablePaths = getAllAvailablePaths('./app/wordpress/site2', 'page.js');
const siteTwoMappings = generateRouteMappings(siteTwoRoutes, siteTwoAvailablePaths, '/wordpress/site2/');


const allMappings = [...siteOneMappings, ...siteTwoMappings];
// You could also just add each and then load both in the next.config.kjs or middleware
writeRouteMappingsToFile(allMappings, './route-mappings.json');
console.info('Route mapping generated successfully');
