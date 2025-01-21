import { templateHierarchy } from './template-hierarchy.js';
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
export function generateRouteMappings(wordPressRoutes, availablePaths = '', subDirectory = '/') {
    const mappings = [];

    for (const [route, routeData] of Object.entries(wordPressRoutes)) {
        const templateKey = routeData['template'] || 'index';
        const availableTemplates = templateHierarchy[templateKey] || ['index'];

        const template = findAvailableTemplate(availableTemplates, availablePaths, routeData);

        mappings.push({ [route]: `${subDirectory}${template}` });
    }

    return mappings;
}


export function getAllAvailablePaths(dir, fileName = '', parentPath = '') {
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
export function findAvailableTemplate(availableTemplates, availablePaths, routeData) {
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

export function writeRouteMappingsToFile(routeMappings, fileName) {
    fs.writeFileSync(fileName, JSON.stringify(routeMappings, null, 2));
}
