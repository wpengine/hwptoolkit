# Overview

This is a very rough POC which might not be something we use.

The idea is as follows.

1. On build time we do the following (see src/template-mapping/generate.js)

- Get a list of available URLS from WP with parameters such as template type etc (see wordpress-routes.js as dummy content)
- Get a list of available paths by scanning the app directory (or whatever framework and file pattern)
- From the template-hierarchy.js (copy of how WP does template hierarchy) workout for each route what type it is 
- Then find the first available template. We also replace variables (e.g. category-$slug as category/news or category/wordpress) so that you can use these templates
- Save this into a JSON file in template-mappings.json e.g.

```json
  {
    "source": "/about-us",
    "destination": "/wordpress/singular"
  },
  {
    "source": "/contact-us",
    "destination": "/wordpress/singular"
  },
  ....```

Then in next.config.mjs (or middleware but not tested) add these as rewrites

e.g. ```js
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
```

So in the example data the following routes (added app directory from next) will be resolved as follows

/category/news = /app/wordpress/category/news/pages.js
/category/wordpress = /app/wordpress/category/pages.js

So as you can see above news used a category-$slug template.


## Testing

Note I didn't test this. 

1. Setup a vanilla next.js app hello world example
2. Copy all files across from /src/

Then run node template-mapping/generate.js to generate mapping and then run `npm run dev`

Test out URLS, add new templates and see what you think.
