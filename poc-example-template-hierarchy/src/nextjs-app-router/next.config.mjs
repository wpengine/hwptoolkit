import fs from 'fs';
import path from 'path';

const routeMappingsPath = path.resolve('./route-mappings.json');
const routeMappings = JSON.parse(fs.readFileSync(routeMappingsPath, 'utf-8'));

/** @type {import('next').NextConfig} */
const nextConfig = {
    async rewrites() {
        return routeMappings.map(mapping => {
            const [route, destination] = Object.entries(mapping)[0];
            return {
                source: route,
                destination: destination,
            };
        });
    },
};

export default nextConfig;
