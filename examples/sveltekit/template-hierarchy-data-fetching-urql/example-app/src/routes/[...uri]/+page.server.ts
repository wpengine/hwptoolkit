import type { PageServerLoad } from "./$types";
import { uriToTemplate, type TemplateData } from "$lib/templateHierarchy";

export const load: PageServerLoad = async (event) => {
  const {
    params: { uri },
    fetch,
  } = event;

  const workingUri = uri || "/";

  const templateData = await uriToTemplate({ uri: workingUri, fetch });

  return {
    uri: workingUri,
    templateData,
  };
};
