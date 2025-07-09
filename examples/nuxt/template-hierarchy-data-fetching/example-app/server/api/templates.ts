import { readdir } from "node:fs/promises";
import { join } from "node:path";

/**
 * Fetches a list of available WordPress templates in Nuxt folder - componenets/wp-templates.
 * filters out unwanted files, and returns a structured list of templates.
 *
 * Used in templateHierarchy.ts uriToTemplate function to get available templates for a given URI.
 *
 * * @returns {Promise<Array<{ id: string, path: string }>>} A promise that resolves to an array of template objects.
 */

export default defineEventHandler(async () => {
  const TEMPLATE_PATH = "components/wp-templates";

  const files = await readdir(join(process.cwd(), TEMPLATE_PATH));

  const templates = files
    .filter(
      (file) =>
        file.endsWith(".vue") && !file.startsWith("+") && !file.startsWith("_")
    )
    .map((file) => {
      const slug = file.replace(".vue", "");
      return {
        id: slug,
        path: `/${TEMPLATE_PATH}/${slug}`,
      };
    });

  return templates;
});
