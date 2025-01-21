import fs from 'fs';
import path from 'path';

const routeMappingsPath = path.resolve('./route-mappings.json');
/** @type {Record<string, string>} */
const routeMappings = JSON.parse(fs.readFileSync(routeMappingsPath, 'utf-8'));

/** @type {import('@sveltejs/kit').Reroute} */
export function reroute({ url }) {
	const mapping = routeMappings.find(mapping => mapping[url.pathname]);
	if (mapping) {
		return mapping[url.pathname];
	}
}
