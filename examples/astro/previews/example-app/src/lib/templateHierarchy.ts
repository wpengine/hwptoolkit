import { getCollection } from "astro:content";
import {
  getTemplate,
  getPossibleTemplates,
  type WordPressTemplate,
} from "./templates";
import { getSeedQuery } from "./seedQuery";

export type TemplateData = {
  uri?: string;
  databaseId?: string;
  seedQuery?: {
    data: any;
    error: any;
  };
  availableTemplates?: any[];
  possibleTemplates?: string[];
  template?: WordPressTemplate;
};

interface ToTemplateArgs {
  id?: string;
  uri?: string;
  asPreview?: boolean;
}

export async function idToTemplate(
  options: ToTemplateArgs
): Promise<TemplateData> {
  const id = "id" in options ? options.id : undefined;
  const uri = "uri" in options ? options.uri : undefined;
  const asPreview = "asPreview" in options ? options.asPreview : false;

  const returnData: TemplateData = {
    uri,
    databaseId: id,
    seedQuery: undefined,
    availableTemplates: undefined,
    possibleTemplates: undefined,
    template: undefined,
  };

  if (asPreview && !id) {
    console.error("HTTP/400 - preview requires database id");
    return returnData;
  }

  const { data, error } = await getSeedQuery({ uri, id, asPreview });

  returnData.seedQuery = { data, error };
  returnData.databaseId =
    data?.contentNode?.databaseId || data?.nodeByUri?.databaseId;

  if (error) {
    console.error("Error fetching seedQuery:", error);
    return returnData;
  }

  const node = asPreview ? data.contentNode : data.nodeByUri;

  if (!node) {
    console.error("HTTP/404 - Not Found in WordPress:", uri);

    returnData.template = { id: "404 Not Found", path: "/404" };

    return returnData;
  }

  const availableTemplates = await getCollection("templates");

  returnData.availableTemplates = availableTemplates;

  if (!availableTemplates || availableTemplates.length === 0) {
    console.error("No templates found");
    return returnData;
  }

  const possibleTemplates = getPossibleTemplates(node);

  returnData.possibleTemplates = possibleTemplates;

  if (!possibleTemplates || possibleTemplates.length === 0) {
    console.error("No possible templates found");
    return returnData;
  }
  const template = getTemplate(
    availableTemplates.map((template) => template.data),
    possibleTemplates
  );

  returnData.template = template;

  if (!template) {
    console.error("No template not found for route");
  }

  return returnData;
}
