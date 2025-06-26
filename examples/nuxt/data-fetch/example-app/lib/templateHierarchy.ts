import {
  getTemplate,
  getPossibleTemplates,
  type WordPressTemplate,
} from "./templates";
import { SEED_QUERY } from "./seedQuery";

export type TemplateData = {
  uri: string;
  seedQuery: any;
  availableTemplates: any[];
  possibleTemplates: string[];
  template: WordPressTemplate;
};

/**
 * Resolves a URI to its corresponding WordPress template data by querying the GraphQL endpoint
 * and determining the appropriate template based on the content type and available templates.
 *
 * @param params - The function parameters
 * @param params.uri - The URI path to resolve (e.g., "/about", "/blog/post-slug")
 *
 * @returns A promise that resolves to template data containing:
 *   - uri: The original URI that was resolved
 *   - seedQuery: Raw data from the WordPress GraphQL query
 *   - availableTemplates: List of template files available in the system - from server/api/templates.ts
 *   - possibleTemplates: Templates that could be used for this content type
 *   - template: The final selected template to render this URI
 *
 * @throws {Error} With status 404 if the URI is not found in WordPress
 * @throws {Error} With status 500 if:
 *   - GraphQL query fails
 *   - No templates are available
 *   - No possible templates match the content type
 *   - No final template can be determined
 *
 * @example
 * const templateData = await uriToTemplate({ uri: "/about" });
 */
export async function uriToTemplate({
  uri,
}: {
  uri: string;
}): Promise<TemplateData> {

  const config = useRuntimeConfig();
  const wpUrl = config.public.wordpressUrl;
  const graphqlEndpoint = `${wpUrl}/graphql`;
  
  const { data: seedQueryData, error: errorMessage } = await $fetch<{
    data: any;
    error?: any;
  }>(graphqlEndpoint, {
    method: "POST",
    body: {
      query: SEED_QUERY,
      variables: { uri },
    },
  });

  if (errorMessage) {
    console.error("Error fetching seedQuery:", errorMessage);
    throw createError({
      statusCode: 500,
      statusMessage: "Error fetching seedQuery",
    });
  }

  if (!seedQueryData?.nodeByUri) {
    console.error("HTTP/404 - Not Found in WordPress:", uri);
    throw createError({ statusCode: 404, statusMessage: "Not Found" });
  }

  /* Fetch available templates from the API. Logic located in server/api/templates.ts */
  const availableTemplates = await $fetch(`/api/templates`, {
    query: { uri },
  });

  if (!availableTemplates) {
    console.error("No templates found");
    throw createError({
      statusCode: 500,
      statusMessage: "No available templates",
    });
  }
  const possibleTemplates = getPossibleTemplates(seedQueryData.nodeByUri);

  if (!possibleTemplates || possibleTemplates.length === 0) {
    console.error("No possible templates found");
    throw createError({
      statusCode: 500,
      statusMessage: "No possible templates for this URI",
    });
  }
  const template = getTemplate(availableTemplates, possibleTemplates);

  if (!template) {
    console.error("No template not found for route");
    throw createError({
      statusCode: 500,
      statusMessage: "No template found for this URI",
    });
  }

  return {
    uri,
    seedQuery: seedQueryData,
    availableTemplates,
    possibleTemplates,
    template,
  };
}
