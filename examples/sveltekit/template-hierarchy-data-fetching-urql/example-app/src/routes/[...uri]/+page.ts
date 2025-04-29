import type { PageLoad } from "./$types";
import { uriToTemplate } from "$lib/templateHierarchy";
import { error } from "@sveltejs/kit";
import { createClient } from "$lib/client";
import merge from "just-merge";

export const load: PageLoad = async (event) => {
  const { params, fetch } = event;
  const { uri } = params;

  const workingUri = uri || "/";

  // const indexTemplate = await import("$wp/index.svelte");

  // Convert the URI to a template name
  const templateData = await uriToTemplate({ uri: workingUri, fetch });
  console.log("Template data:", templateData);

  if (!templateData || !templateData.template) {
    console.error("No template data found for URI:", workingUri);
    return error(404, "Template not found");
  }

  const template = await import(`$wp/${templateData.template.id}.svelte`);

  if (template.queries) {
    const client = createClient(fetch);
    console.log("Queting queries");
    let queries: Promise<any>[] = [];
    let streamedQueries: Promise<any>[] = [];
    for (const query of template.queries) {
      console.log("Querying:", query.query.definitions[0].name?.value);
      const queryResp = client.query(query.query, query.variables(event));
      if (query.stream) {
        streamedQueries.push(queryResp);
      } else {
        queries.push(queryResp);
      }
    }

    const results = await Promise.all(queries);

    const errors = results.filter((result) => result.error);
    if (errors.length > 0) {
      console.error("Error fetching template data:", errors);
      return error(500, "Error fetching template data");
    }

    // Pass the data to the template
    const data = results.map((result) => result.data);

    const mergedData = merge(...data);

    return {
      uri: workingUri,
      template: template.default,
      graphqlData: mergedData,
      templateData: templateData,
      data,
    };
  } else {
    // Return the template name as a prop
    return {
      uri: workingUri,
      template: template.default,
      templateData,
      graphqlData: null,
    };
  }
};
