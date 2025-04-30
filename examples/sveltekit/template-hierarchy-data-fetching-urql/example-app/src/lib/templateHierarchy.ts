import {
  getTemplate,
  getPossibleTemplates,
  type WordPressTemplate,
} from "./templates";
import { SEED_QUERY } from "./seedQuery";
import { client } from "./client";

export type TemplateData = {
  uri: string;
  seedQuery?: {
    data: any;
    error: any;
  };
  availableTemplates?: any[];
  possibleTemplates?: string[];
  template?: WordPressTemplate;
};

export async function uriToTemplate({
  fetch,
  uri,
}: {
  fetch: typeof globalThis.fetch;
  uri: string;
}): Promise<TemplateData> {
  const returnData: TemplateData = {
    uri,
    seedQuery: undefined,
    availableTemplates: undefined,
    possibleTemplates: undefined,
    template: undefined,
  };

  const { data, error } = await client.query(SEED_QUERY, { uri }, { fetch });

  returnData.seedQuery = { data, error };

  if (error) {
    console.error("Error fetching seedQuery:", error);
    return returnData;
  }

  if (!data.nodeByUri) {
    console.error("HTTP/404 - Not Found in WordPress:", uri);
    returnData.template = { id: "404", path: "/404" };
    return returnData;
  }

  const resp = await fetch(`/api/templates?uri=${uri}`);

  if (!resp.ok) {
    console.error("Error fetching templates:", resp.statusText);

    return returnData;
  }

  const availableTemplates = await resp.json();

  returnData.availableTemplates = availableTemplates;

  if (!availableTemplates || availableTemplates.length === 0) {
    console.error("No templates found");
    return returnData;
  }

  const possibleTemplates = getPossibleTemplates(data.nodeByUri);

  returnData.possibleTemplates = possibleTemplates;

  if (!possibleTemplates || possibleTemplates.length === 0) {
    console.error("No possible templates found");
    return returnData;
  }
  const template = getTemplate(availableTemplates, possibleTemplates);

  returnData.template = template;

  if (!template) {
    console.error("No template not found for route");
  }

  return returnData;
}
