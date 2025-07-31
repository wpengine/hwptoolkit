import {
  getTemplate,
  getPossibleTemplates,
  getAvailableTemplates,
} from "./templates.js";
import { getSeedQuery } from "./seedQuery";

export async function uriToTemplate({ uri }) {
  const returnData = {
    uri,
    seedQuery: undefined,
    availableTemplates: undefined,
    possibleTemplates: undefined,
    template: undefined,
  };

  const { data, error } = await getSeedQuery({ uri });

  returnData.seedQuery = { data, error };

  if (error) {
    console.error("Error fetching seedQuery:", error);
    return returnData;
  }

  if (!data.nodeByUri) {
    console.error("HTTP/404 - Not Found in WordPress:", uri);

    returnData.template = { id: "404 Not Found", path: "/404" };

    return returnData;
  }

  const availableTemplates = await getAvailableTemplates();

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
