import { ref, onMounted, onServerPrefetch } from "vue";
import { useRuntimeConfig, useState } from "#app";

/**
 * Hybrid SSR + CSR approach for data fetching in Nuxt.
 *  Runs on server during initial page load (SSR)
 *  Automatically hydrates on client
 *  Built-in reactivity, caching, and state management
 *  Automatic loading states and error handling
 * @param {string} query - The GraphQL query string
 * @param {Object} initialVariables - Initial variables for the GraphQL query
 * @param {Object} options - Additional options for the fetch request
 * @param {string} [options.key] - Custom key for the SSR state
 *
 * @returns {Object} The composable state and methods
 * @returns {Readonly<Ref<Object|null>>} returns.data - The GraphQL response data
 * @returns {Readonly<Ref<boolean>>} returns.loading - Loading state indicator
 * @returns {Readonly<Ref<Error|null>>} returns.error - Error state, if any
 * @returns {Function} returns.refetch - Function to refetch the data with optional new variables
 * @returns {Ref<Object>} returns.currentVariables - Current variables being used for the query
 */
export function useGraphQL(query, initialVariables = {}, options = {}) {
  // Create a unique key for SSR state
  const key =
    options.key ||
    `gql-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`;

  // Use useState for proper SSR hydration
  const data = useState(`${key}-data`, () => null);
  const loading = useState(`${key}-loading`, () => true);
  const error = useState(`${key}-error`, () => null);
  const currentVariables = ref(initialVariables);

  const fetchData = async (newVariables) => {
    // Only show loading if we don't have data yet
    if (!data.value) {
      loading.value = true;
    }
    error.value = null;

    if (newVariables) {
      currentVariables.value = newVariables;
    }

    try {
      const config = useRuntimeConfig();
      const wpUrl = config.public.wordpressUrl;
      const graphqlEndpoint = `${wpUrl}/graphql`;

      const response = await fetch(graphqlEndpoint, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          query,
          variables: currentVariables.value,
        }),
        ...options,
      });

      if (!response.ok) {
        throw new Error(
          `Network error: ${response.status} ${response.statusText}`
        );
      }

      const result = await response.json();

      if (result.errors) {
        throw new Error(
          result.errors[0]?.message || "GraphQL query returned errors"
        );
      }

      data.value = result.data;
      return result;
    } catch (e) {
      error.value = e;
      console.error("GraphQL error:", e);
      throw e;
    } finally {
      loading.value = false;
    }
  };

  // Server-side prefetch
  if (process.import.meta.server) {
    onServerPrefetch(async () => {
      try {
        await fetchData();
      } catch (e) {
        console.error("SSR GraphQL error:", e);
      }
    });
  }

  // Client-side mount
  onMounted(() => {
    if (data.value) {
      // We have SSR data, just set loading to false
      loading.value = false;
    } else {
      // No SSR data, fetch on client
      fetchData().catch(() => {
        // Error already handled in fetchData
      });
    }
  });

  return {
    data: readonly(data),
    loading: readonly(loading),
    error: readonly(error),
    refetch: fetchData,
    currentVariables,
  };
}

/**
 * CSR Fetch GraphQL function
 * Used for client-side rendering (CSR) and interactive components - load more posts, button clicks etc.
 * @async
 * @param {string} query - The GraphQL query to execute.
 * @param {Object} [variables={}] - Variables to pass to the GraphQL query.
 * @param {number|null} [revalidate=null] - Cache revalidation time in seconds, or null for no revalidation.
 * @returns {Promise<Object>} The data from the GraphQL response.
 * @throws {Error} If the GraphQL response contains errors or if the fetch operation fails.
 */
export async function fetchGraphQL(query, variables = {}, revalidate = null) {
  try {
    const fetchOptions = {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        query,
        variables,
      }),
    };

    if (revalidate !== null) {
      fetchOptions.next = {
        revalidate: revalidate,
      };
    }

    const config = useRuntimeConfig();
    const response = await fetch(
      `${config.public.wordpressUrl}/graphql`,
      fetchOptions
    );

    const result = await response.json();

    if (result.errors) {
      console.error("GraphQL Error:", result.errors);
      throw new Error(
        result.errors[0]?.message || "Failed to fetch data from WordPress"
      );
    }

    return result.data;
  } catch (error) {
    console.error("Error fetching from WordPress:", error);
    throw error;
  }
}

export function gql(strings, ...values) {
  return strings.reduce((result, string, i) => {
    return result + string + (values[i] || "");
  }, "");
}

export async function useMutation(mutation, variables = {}) {
  const config = useRuntimeConfig();
  const wpUrl = config.public.wordpressUrl;
  const endpoint = `${wpUrl}/graphql`;

  try {
    console.log("Sending GraphQL mutation to:", endpoint);
    console.log("With variables:", JSON.stringify(variables, null, 2));

    const response = await fetch(endpoint, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        query: mutation,
        variables,
      }),
    });

    // Check if response is HTML (likely an error page)
    const contentType = response.headers.get("content-type");
    if (contentType && contentType.includes("text/html")) {
      console.error("Received HTML response instead of JSON");
      const htmlContent = await response.text();
      // Log full HTML response to file
      const logFile = await logToFile(htmlContent);

      // Console log brief preview
      console.error("HTML response preview:", htmlContent.substring(0, 200));
      console.error(`Full HTML response logged to ${logFile || "console"}`);

      throw new Error(
        `Received HTML response from GraphQL endpoint. See logs for details.`
      );
    }

    // For debugging, log the raw response text
    const responseText = await response.text();

    try {
      // Try to parse as JSON
      const result = JSON.parse(responseText);
      console.log("GraphQL response:", result);

      if (result.errors) {
        console.error("GraphQL returned errors:", result.errors);
      }

      return {
        data: result.data,
        errors: result.errors,
      };
    } catch (jsonError) {
      console.error("Failed to parse response as JSON:", jsonError);

      throw new Error(`Invalid JSON response: ${jsonError.message}`);
    }
  } catch (error) {
    console.error("Error executing GraphQL mutation:", error);
    return { data: null, errors: [error] };
  }
}
