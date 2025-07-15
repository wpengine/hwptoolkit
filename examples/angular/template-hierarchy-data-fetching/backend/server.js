import express from 'express';
import { readdir } from 'node:fs/promises';
import { join, dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';
import cors from 'cors';
import dotenv from 'dotenv';

dotenv.config();

const app = express();
const port = process.env.PORT || 3000;

const __filename = fileURLToPath(import.meta.url);
const __dirname = dirname(__filename);

const TEMPLATES_PATH = process.env.TEMPLATE_PATH || 
  resolve(__dirname, '../example-app/src/app/components/wp-templates');

app.use(cors({
  origin: process.env.FRONTEND_URL 
}));

app.get('/api/templates', async (req, res) => {
  //console.log(`🔍 Reading templates from: ${TEMPLATES_PATH}`);

  try {
    try {
      await readdir(TEMPLATES_PATH);
    } catch (error) {
      throw new Error(`Template directory does not exist: ${TEMPLATES_PATH}`);
    }

    const entries = await readdir(TEMPLATES_PATH, { withFileTypes: true });

    const templates = [];

    for (const entry of entries) {
      if (entry.isDirectory() &&
          !entry.name.startsWith("+") &&
          !entry.name.startsWith("_") &&
          !entry.name.startsWith(".")) {

        const folderPath = join(TEMPLATES_PATH, entry.name);

        try {
          const folderContents = await readdir(folderPath);
          const hasComponentFile = folderContents.some(file =>
            file.endsWith('.component.ts')
          );

          if (hasComponentFile) {
            templates.push({
              id: entry.name,
              path: `/wp-templates/${entry.name}`,
            });
            //console.log(`✅ Added template: ${entry.name}`);
          }
        } catch (error) {
          //console.warn(`❌ Could not read template folder: ${entry.name}`, error.message);
        }
      }
    }

    res.json(templates);

  } catch (error) {
    //console.error('❌ Error reading template directories:', error);
    
    // Return fallback templates
    const fallbackTemplates = [
      { id: 'front-page', path: '/wp-templates/front-page' },
      { id: 'home', path: '/wp-templates/home' },
      { id: 'page', path: '/wp-templates/page' },
      { id: 'single', path: '/wp-templates/single' },
      { id: 'archive', path: '/wp-templates/archive' },
    ];
    
    res.status(500).json(fallbackTemplates);
  }
});

app.listen(port, () => {
//   console.log(`🚀 Template discovery API running at http://localhost:${port}`);
//   console.log(`📂 Templates path: ${TEMPLATES_PATH}`);
});