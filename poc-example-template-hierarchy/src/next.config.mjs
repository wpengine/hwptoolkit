import fs from 'fs';
import path from 'path';

const templateMappingsPath = path.resolve('./template-mappings.json');
const templateMappings = JSON.parse(fs.readFileSync(templateMappingsPath, 'utf-8'));

/** @type {import('next').NextConfig} */
const nextConfig = {
    async rewrites() {
        return templateMappings.map(mapping => ({
            source: mapping.source,
            destination: mapping.destination,
        }));
    },
};

export default nextConfig;
