import { useGraphQL } from './client';
import { fetchGraphQL } from './client';
/**
 * Nuxt.js equivalent of SvelteKit's fetchQueries function
 * Mirrors the SvelteKit implementation for consistency
 */
export async function fetchQueries({ queries, event }) {
  const results = {};
  
  if (!queries || Object.keys(queries).length === 0) {
    return results;
  }
  
  const { data } = event;
  
  try {
    // Process each query sequentially to avoid context issues
    for (const [queryName, queryConfig] of Object.entries(queries)) {
      try {
        // Prepare variables
        let variables = {};
        
        if (typeof queryConfig.variables === 'function') {
          variables = queryConfig.variables(event);
        } else if (queryConfig.variables) {
          variables = queryConfig.variables;
        }
        
        // Add common variables
        if (!variables.slug && data.slug) {
          variables.slug = data.slug;
        }
        
        if (!variables.uri && data.slug) {
          variables.uri = data.slug;
        }
        
        // Execute GraphQL query using fetchGraphQL (not useGraphQL)
        const queryData = await fetchGraphQL(
          queryConfig.query,
          variables
        );
        
        // Store result
        results[queryName] = {
          data: queryData,
          loading: false,
          error: null
        };
        
      } catch (queryError) {
        console.error(`Error fetching query ${queryName}:`, queryError);
        results[queryName] = {
          data: null,
          loading: false,
          error: queryError
        };
      }
    }
  } catch (globalError) {
    console.error('Error in fetchQueries:', globalError);
    throw globalError;
  }
  
  return results;
}

/**
 * Type definitions for better IntelliSense (similar to SvelteKit's types)
 */
export function defineTemplateQueries(queries) {
  return queries;
}

/**
 * Helper to define template with queries (similar to SvelteKit's module structure)
 */
export function defineTemplate(component, queries = {}) {
  return {
    default: component,
    queries
  };
}