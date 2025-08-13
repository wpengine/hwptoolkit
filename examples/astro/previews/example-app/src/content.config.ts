import { defineCollection, z } from "astro:content";
import { readdir } from "node:fs/promises";
import { join } from "node:path";

const TEMPLATE_PATH = "wp-templates";

const templates = defineCollection({
  loader: async () => {
    const files = await readdir(join("src", "pages", TEMPLATE_PATH));
    return files.map((file) => {
      const slug = file.replace(".astro", "");

      if (slug === "index") {
        return {
          id: slug,
          path: join(TEMPLATE_PATH, "/"),
        };
      }

      return {
        id: slug,
        path: join(TEMPLATE_PATH, slug),
      };
    });
  },
  schema: z.object({
    id: z.string(),
    path: z.string(),
  }),
});

export const collections = { templates };
