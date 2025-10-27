import {
  ApolloClient,
  InMemoryCache,
  ApolloLink,
  HttpLink,
  from,
  Observable,
} from "@apollo/client";
import { onError } from "@apollo/client/link/error";
import { setContext } from '@apollo/client/link/context';
import useLocalStorage from "./storage";

const baseUrl = process.env.NEXT_PUBLIC_WORDPRESS_URL || "http://localhost:8890";
const graphqlPath = process.env.NEXT_PUBLIC_GRAPHQL_PATH || "/graphql";

const httpLink = new HttpLink({
  uri: `${baseUrl}${graphqlPath}`,
});

// Unified auth link - handles auth token for both logged-in users and cart sessions
function createAuthLink() {
  return setContext((operation) => {
    if (typeof window === 'undefined') return {};
    
    const storage = useLocalStorage;
    const storedTokens = storage.getItem("authTokens");
    
    if (!storedTokens) {
      return {};
    }

    try {
      const tokens = JSON.parse(storedTokens);
      
      if (!tokens?.authToken) {
        return {};
      }      

      // Use auth token for both authentication and session management
      return {
        headers: {
          Authorization: `Bearer ${tokens.authToken}`,
        },
      };
    } catch (error) {
      console.error("Error parsing auth tokens:", error);
      storage.removeItem("authTokens");
      return {};
    }
  });
}

// Error handling link for expired/invalid tokens
function createErrorLink() {
  return onError(({ graphQLErrors, operation, forward }) => {
    const targetErrors = [
      'The iss do not match with this server',
      'invalid-secret-key | Expired token',
      'invalid-secret-key | Signature verification failed',
      'Expired token',
      'Wrong number of segments',
      'Internal server error',
    ];
    
    if (graphQLErrors?.length) {
      for (const { debugMessage, message } of graphQLErrors) {
        if (targetErrors.includes(message) || targetErrors.includes(debugMessage)) {
          console.warn('🔄 Auth error detected, clearing tokens...');
          
          // Clear expired/invalid tokens
          const storage = useLocalStorage;
          storage.removeItem("authTokens");
          
          // Return observable to retry without auth
          return new Observable((observer) => {
            operation.setContext(({ headers = {} }) => {
              const { Authorization, ...restHeaders } = headers;
              return { headers: restHeaders };
            });

            const subscriber = {
              next: observer.next.bind(observer),
              error: observer.error.bind(observer),
              complete: observer.complete.bind(observer),
            };
            
            forward(operation).subscribe(subscriber);
          });
        }
      }
    }
  });
}

// Network error handling
const networkErrorLink = onError(
  ({ graphQLErrors, networkError, operation, forward }) => {
    if (networkError) {
      console.log(`Network error: ${networkError}`);
      
      if (networkError.statusCode === 403) {
        console.warn("403 Forbidden - clearing auth tokens");
        
        if (typeof window !== 'undefined') {
          const storage = useLocalStorage;
          storage.removeItem("authTokens");
        }

        return forward(operation);
      }
    }

    if (graphQLErrors) {
      graphQLErrors.forEach(({ message, locations, path }) =>
        console.log(
          `GraphQL error: Message: ${message}, Location: ${locations}, Path: ${path}`
        )
      );
    }
  }
);

// Console logging link for debugging
const consoleLink = new ApolloLink((operation, forward) => {
  console.log(`🔍 GraphQL Operation: ${operation.operationName}`);
  
  return forward(operation).map((response) => {
    console.log(`✅ GraphQL Response: ${operation.operationName}`, response);
    return response;
  });
});

export default function getApolloClient() {
  return new ApolloClient({
    link: from([
      consoleLink,
      networkErrorLink,
      createErrorLink(),
      createAuthLink(),
      httpLink
    ]),
    cache: new InMemoryCache(),
    defaultOptions: {
      watchQuery: {
        errorPolicy: "ignore",
      },
      query: {
        errorPolicy: "all",
      },
    },
  });
}