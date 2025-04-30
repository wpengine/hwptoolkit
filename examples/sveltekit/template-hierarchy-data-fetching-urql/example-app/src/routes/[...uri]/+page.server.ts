import type { PageServerLoad } from "./$types";
import { uriToTemplate } from "$lib/templateHierarchy";
import { error } from "@sveltejs/kit";
import type { Component } from "svelte";
import { fetchQueries, type TemplateQueries } from "$lib/queryHandler";

type WordPressTemplate = {
  default: Component;
  queries?: TemplateQueries;
};

export const load: PageServerLoad = async (event) => {
  const {
    params: { uri },
    fetch,
  } = event;

  const workingUri = uri || "/";

  const templateData = await uriToTemplate({ uri: workingUri, fetch });

  if (!templateData || !templateData.template) {
    console.error("No template data found for URI:", workingUri);
    return error(422, "Template not found");
  }

  if (templateData.template.id == "404") {
    return error(404, "Not Found");
  }

  const template: WordPressTemplate = await import(
    `$wp/${templateData.template.id}.svelte`
  );

  const queryResults = await fetchQueries({ queries: template.queries, event });

  return {
    uri: workingUri,
    templateData,
    graphqlData: queryResults,
  };
};
