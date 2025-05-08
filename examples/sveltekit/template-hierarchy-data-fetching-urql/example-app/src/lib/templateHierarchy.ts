import {
  getTemplate,
  getPossibleTemplates,
  type WordPressTemplate,
} from "./templates";
import { SEED_QUERY } from "./seedQuery";
import { client } from "./client";
import { error } from "@sveltejs/kit";

export type TemplateData = {
  uri: string;
  seedQuery: any;
  availableTemplates: any[];
  possibleTemplates: string[];
  template: WordPressTemplate;
};

export async function uriToTemplate({
  fetch,
  uri,
}: {
  fetch: typeof globalThis.fetch;
  uri: string;
}): Promise<TemplateData> {
  const { data: seedQueryData, error: errorMessage } = await client.query(
    SEED_QUERY,
    { uri },
    { fetch }
  );

  if (errorMessage) {
    console.error("Error fetching seedQuery:", error);
    throw error(500, "Error fetching seedQuery");
  }

  if (!seedQueryData?.nodeByUri) {
    console.error("HTTP/404 - Not Found in WordPress:", uri);
    throw error(404, "Not Found");
  }

  const resp = await fetch(`/api/templates?uri=${uri}`);

  if (!resp.ok) {
    console.error("Error fetching available templates:", resp.statusText);

    throw error(500, "Error fetching available templates");
  }

  const availableTemplates = await resp.json();

  if (!availableTemplates || availableTemplates.length === 0) {
    console.error("No templates found");

    throw error(500, "No available templates");
  }

  const possibleTemplates = getPossibleTemplates(seedQueryData.nodeByUri);

  if (!possibleTemplates || possibleTemplates.length === 0) {
    console.error("No possible templates found");
    throw error(500, "No possible templates for this URI");
  }
  const template = getTemplate(availableTemplates, possibleTemplates);

  if (!template) {
    console.error("No template not found for route");
    throw error(500, "No template found for this URI");
  }

  return {
    uri,
    seedQuery: seedQueryData,
    availableTemplates,
    possibleTemplates,
    template,
  };
}
