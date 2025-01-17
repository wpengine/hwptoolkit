import { templateHierarchy } from './template-hierarchy.js';
import { wordPressRoutes } from './wordpress-routes.js';
import fs from 'fs';
import path from 'path';


/**
 * This does the following
 *
 * 1. Gets an array of all WP routes
 * 2. Loops through and works out the available templates
 * 3. Checks if the template exists
 * 4. Writes the mappings to a JSON file
 *
 * This is then used in next.config.mjs for the dynamic routes
 * It could also be used in middleware.js too as it would bypass the file system but there is no ISR cache
 */
function generateTemplateMappings(availablePaths = '', subDirectory = '/') {
    const mappings = [];

    for (const [route, routeData] of Object.entries(wordPressRoutes)) {
        const templateKey = routeData['template'] || 'index';
        const availableTemplates = templateHierarchy[templateKey] || ['index'];

        const template = findAvailableTemplate(availableTemplates, availablePaths, routeData);

        // Update as needed to match any framework rewrites
        mappings.push({
            source: route,
            destination: `${subDirectory}${template}`
        });
    }

    return mappings;
}


function getAllAvailablePaths(dir, fileName = '', parentPath = '') {
    let directories = [];
    const files = fs.readdirSync(dir);

    files.forEach(file => {
        const fullPath = path.join(dir, file);
        const relativePath = path.join(parentPath, file);

        if (fs.statSync(fullPath).isDirectory()) {
            if (fs.existsSync(path.join(fullPath, fileName))) {
                directories.push(relativePath);
            }
            directories = directories.concat(getAllAvailablePaths(fullPath, fileName, relativePath));
        }
    });

    return directories;
}

/**
 * Searches for an available template.
 * 
 * 
 * @param {Array} availableTemplates 
 * @param {Array} availablePaths 
 * @param {Array} routeData 
 * @returns string
 */
function findAvailableTemplate(availableTemplates, availablePaths, routeData) {
    const routeDataKeys = Object.keys(routeData);

    for (const template of availableTemplates) {
        let templateName = template;

        // Replace -$slug with the actual slug e.g. category-$slug -> category/news
        if (template.includes('$')) {
            routeDataKeys.forEach(key => {
                const variable = `-$${key}`;
                templateName = templateName.replace(variable, '/' + routeData[key]);
            });
        }

        if (availablePaths.includes(templateName)) {
            return templateName;
        }
    }
    return 'index';
}

const availablePaths = getAllAvailablePaths('./app//wordpress', 'page.js');
const templateMappings = generateTemplateMappings(availablePaths, '/wordpress/');
console.log('Available Paths:', availablePaths);
console.log('TemplateMappings:', templateMappings);

fs.writeFileSync('./template-mappings.json', JSON.stringify(templateMappings, null, 2));

console.info('Template mappings generated successfully');
