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
    throw createError({ statusCode: 500, statusMessage: "Error fetching seedQuery" });
  }
  
  if (!seedQueryData?.nodeByUri) {
    
    console.error("HTTP/404 - Not Found in WordPress:", uri);
    throw createError({ statusCode: 404, statusMessage: "Not Found" });
  }
  const availableTemplates = await $fetch(`/api/templates`, {
    query: { uri }
  })

  if (!availableTemplates) {
    console.error("No templates found");
    throw createError({ statusCode: 500, statusMessage: "No available templates" });
  }

  const possibleTemplates = getPossibleTemplates(seedQueryData.nodeByUri);

  if (!possibleTemplates || possibleTemplates.length === 0) {
    console.error("No possible templates found");
    throw createError({ statusCode: 500, statusMessage: "No possible templates for this URI" });
  }
  const template = getTemplate(availableTemplates, possibleTemplates);

  if (!template) {
    console.error("No template not found for route");
    throw createError({ statusCode: 500, statusMessage: "No template found for this URI" });
  }

  return {
    uri,
    seedQuery: seedQueryData,
    availableTemplates,
    possibleTemplates,
    template,
  };
}

// Nuxt.js composable version for use in pages/components
export async function useTemplateHierarchy(uri: string) {
  try {
    return await uriToTemplate({ uri });
  } catch (error) {
    // Handle errors in Nuxt context
    if (process.client) {
      // Client-side error handling
      console.error("Template hierarchy error:", error);
      await navigateTo("/404");
    } else {
      // Server-side error handling
      throw error;
    }
  }
}

// Reactive composable for template data
export function useTemplateData(uri: Ref<string> | string) {
  const uriRef = ref(uri);
  const templateData = ref<TemplateData | null>(null);
  const loading = ref(false);
  const error = ref<Error | null>(null);

  const loadTemplate = async () => {
    if (!uriRef.value) return;

    loading.value = true;
    error.value = null;

    try {
      templateData.value = await uriToTemplate({ uri: uriRef.value });
    } catch (err) {
      error.value = err as Error;
      templateData.value = null;
    } finally {
      loading.value = false;
    }
  };

  // Watch for URI changes
  watch(uriRef, loadTemplate, { immediate: true });

  return {
    templateData: readonly(templateData),
    loading: readonly(loading),
    error: readonly(error),
    refresh: loadTemplate,
  };
}
