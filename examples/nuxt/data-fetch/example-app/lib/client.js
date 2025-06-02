import { ref, onMounted, onServerPrefetch } from 'vue';
import { useRuntimeConfig } from 'nuxt/app';


// Make sure we're using the correct imports 
export function useGraphQL(query, initialVariables = {}, options = {}) {
  const data = ref(null);
  const loading = ref(true);
  const error = ref(null);
  const currentVariables = ref(initialVariables);
  
  const fetchData = async (newVariables) => {
    loading.value = true;
    error.value = null;
    
    // Update variables if new ones are provided
    if (newVariables) {
      currentVariables.value = newVariables;
    }
    
    try {
      // Get runtime config
      const config = useRuntimeConfig();
      const wpUrl = config.public.wordpressUrl;
      const graphqlEndpoint = `${wpUrl}/graphql`;
      
      //console.log('Executing GraphQL query with variables:', currentVariables.value);
      
      // Make the request
      const response = await fetch(graphqlEndpoint, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          query,
          variables: currentVariables.value
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
      
      // Return the result for refetch operations
      return result;
    } catch (e) {
      error.value = e;
      console.error('GraphQL error:', e);
      throw e; // Re-throw to allow handling in the component
    } finally {
      loading.value = false;
    }
  };
  
  // Important: This ensures the data is fetched during SSR
  onServerPrefetch(() => fetchData());
  
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
    refetch: fetchData,
    currentVariables
  };
}

// Helper for GraphQL tag template literals
export function gql(strings, ...values) {
  return strings.reduce((result, string, i) => {
    return result + string + (values[i] || '');
  }, '');
}
// Add this to your lib/client.js
export async function useMutation(mutation, variables = {}) {
  const config = useRuntimeConfig();
  // Fix: Use wordpressUrl + /graphql consistent with useGraphQL
  const wpUrl = config.public.wordpressUrl;
  const endpoint = `${wpUrl}/graphql`;
  
  try {
    console.log("Sending GraphQL mutation to:", endpoint);
    console.log("With variables:", JSON.stringify(variables, null, 2));
    
    const response = await fetch(endpoint, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        query: mutation,
        variables
      }),
    });
    
    // Check if response is HTML (likely an error page)
    const contentType = response.headers.get('content-type');
    if (contentType && contentType.includes('text/html')) {
      console.error('Received HTML response instead of JSON');
      const htmlContent = await response.text();
      
      // Log full HTML response to file
      const logFile = await logToFile(htmlContent);
      
      // Console log brief preview
      console.error('HTML response preview:', htmlContent.substring(0, 200));
      console.error(`Full HTML response logged to ${logFile || 'console'}`);
      
      throw new Error(`Received HTML response from GraphQL endpoint. See logs for details.`);
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
        errors: result.errors
      };
    } catch (jsonError) {
      console.error("Failed to parse response as JSON:", jsonError);
      
      // Log the invalid JSON response to file
      await logToFile(responseText, 'invalid-json.txt');
      
      throw new Error(`Invalid JSON response: ${jsonError.message}`);
    }
    
  } catch (error) {
    console.error('Error executing GraphQL mutation:', error);
    return { data: null, errors: [error] };
  }
}
const logToFile = async (content, filename = 'graphql-error.html') => {
  // Only works on the server side
  if (process.server) {
    try {
      const logDir = path.resolve(process.cwd(), 'logs');
      
      // Create logs directory if it doesn't exist
      if (!fs.existsSync(logDir)) {
        fs.mkdirSync(logDir, { recursive: true });
      }
      
      const timestamp = new Date().toISOString().replace(/:/g, '-');
      const logPath = path.join(logDir, `${timestamp}-${filename}`);
      
      // Write the content to file
      fs.writeFileSync(logPath, content);
      console.log(`Logged error content to ${logPath}`);
      return logPath;
    } catch (err) {
      console.error('Failed to write log file:', err);
    }
  } else {
    // For client-side, just log to console
    console.log('HTML Content (client-side):', content.substring(0, 1000) + '...');
  }
};