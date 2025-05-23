import { ref, onMounted, onServerPrefetch } from 'vue';
import { useRuntimeConfig } from 'nuxt/app';

// Make sure we're using the correct imports 
export function useGraphQL(query, variables = {}, options = {}) {
  const data = ref(null);
  const loading = ref(true);
  const error = ref(null);
  
  const fetchData = async () => {
    loading.value = true;
    error.value = null;
    
    try {
      // Get runtime config
      const config = useRuntimeConfig();
      const wpUrl = config.public.wordpressUrl;
      const graphqlEndpoint = `${wpUrl}/graphql`;
      
      // Make the request
      const response = await fetch(graphqlEndpoint, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          query,
          variables
        }),
        ...options
      });
      
      if (!response.ok) {
        throw new Error(`Network error: ${response.status} ${response.statusText}`);
      }
      
      const result = await response.json();
      
      if (result.errors) {
        throw new Error(result.errors[0]?.message || 'GraphQL query returned errors');
      }
      
      data.value = result.data;
    } catch (e) {
      error.value = e;
      console.error('GraphQL error:', e);
    } finally {
      loading.value = false;
    }
  };
  
  // Important: This ensures the data is fetched during SSR
  onServerPrefetch(fetchData);
  
  // Also fetch on mount for client-side navigation
  onMounted(() => {
    if (!data.value) {
      fetchData();
    }
  });
  
  return {
    data,
    loading,
    error,
    refetch: fetchData
  };
}

// Helper for GraphQL tag template literals
export function gql(strings, ...values) {
  return strings.reduce((result, string, i) => {
    return result + string + (values[i] || '');
  }, '');
}