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
const SESSION_TOKEN_LS_KEY = 'woocommerce_session_token';

const httpLink = new HttpLink({
  uri: `${baseUrl}${graphqlPath}`,
});

// Helper function to get session token
async function getSessionToken(refresh = false) {
  if (typeof window === 'undefined') return null;
  
  if (refresh) {
    localStorage.removeItem(SESSION_TOKEN_LS_KEY);
  }
  
  return localStorage.getItem(SESSION_TOKEN_LS_KEY);
}

// Helper function to set session token
function setSessionToken(token) {
  if (typeof window !== 'undefined' && token) {
    localStorage.setItem(SESSION_TOKEN_LS_KEY, token);
  }
}

// Session management link
function createSessionLink() {
  return setContext(async (operation) => {
    const headers = {};
    const sessionToken = await getSessionToken();

    if (sessionToken) {
      headers['woocommerce-session'] = `Session ${sessionToken}`;
    }

    return { headers };
  });
}

// Error handling link
function createErrorLink() {
  return onError(({ graphQLErrors, operation, forward }) => {
    const targetErrors = [
      'The iss do not match with this server',
      'invalid-secret-key | Expired token',
      'invalid-secret-key | Signature verification failed',
      'Expired token',
      'Wrong number of segments',
    ];
    
    let observable;
    
    if (graphQLErrors?.length) {
      graphQLErrors.map(({ debugMessage, message }) => {
        if (targetErrors.includes(message) || targetErrors.includes(debugMessage)) {
          observable = new Observable((observer) => {
            getSessionToken(true)
              .then((sessionToken) => {
                operation.setContext(({ headers = {} }) => {
                  const nextHeaders = { ...headers };

                  if (sessionToken) {
                    nextHeaders['woocommerce-session'] = `Session ${sessionToken}`;
                  } else {
                    delete nextHeaders['woocommerce-session'];
                  }

                  return { headers: nextHeaders };
                });
              })
              .then(() => {
                const subscriber = {
                  next: observer.next.bind(observer),
                  error: observer.error.bind(observer),
                  complete: observer.complete.bind(observer),
                };
                forward(operation).subscribe(subscriber);
              })
              .catch((error) => {
                observer.error(error);
              });
          });
        }
      });
    }
    
    return observable;
  });
}

// Session update link - properly structured as Apollo Link
function createUpdateLink() {
  return new ApolloLink((operation, forward) => {
    return forward(operation).map((response) => {
      // Check for session header and update session in local storage accordingly
      const context = operation.getContext();
      
      if (context.response && context.response.headers) {
        const { headers } = context.response;
        const oldSessionToken = localStorage.getItem(SESSION_TOKEN_LS_KEY);
        const sessionToken = headers.get('woocommerce-session');
        
        if (sessionToken && sessionToken !== oldSessionToken) {
          setSessionToken(sessionToken);
          console.log('🔗 Updated session token:', sessionToken);
        }
      }

      return response;
    });
  });
}
// const authLink = (storage) =>
//   new ApolloLink((operation, forward) => {
//     const storedTokens = storage.getItem("authTokens");
//     if (!storedTokens) {
//       return forward(operation);
//     }

//     try {
//       const tokens = JSON.parse(storedTokens);
//       if (!tokens || !tokens.authToken) {
//         return forward(operation);
//       }

//       if (tokens.expiresAt && new Date() > new Date(tokens.expiresAt)) {
//         console.warn("Auth token expired, removing from storage");
//         storage.removeItem("authTokens");
//         return forward(operation);
//       }

//       operation.setContext({
//         headers: {
//           Authorization: `Bearer ${tokens.authToken}`,
//         },
//       });

//       return forward(operation);
//     } catch (error) {
//       console.error("Error parsing auth tokens:", error);
//       storage.removeItem("authTokens");
//       return forward(operation);
//     }
//   });
// Auth link for JWT tokens
function createAuthLink() {
  return setContext(async (operation) => {
    if (typeof window === 'undefined') return {};
    
    const storage = useLocalStorage;
    const storedTokens = storage.getItem("authTokens");
    
    if (!storedTokens) {
      return {};
    }

    try {
      const tokens = JSON.parse(storedTokens);
      if (!tokens || !tokens.authToken) {
        return {};
      }

      if (tokens.authTokenExpiration && new Date() > new Date(tokens.authTokenExpiration)) {
        console.warn("Auth token expired, removing from storage");
        storage.removeItem("authTokens");
        return {};
      }
      console.log('auth OK')
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

// Error handling for auth issues
const errorLink = onError(
  ({ graphQLErrors, networkError, operation, forward }) => {
    if (networkError && networkError.statusCode === 403) {
      console.warn(
        "403 Forbidden - clearing auth tokens and retrying without auth"
      );

      // Clear invalid tokens
      if (typeof window !== 'undefined') {
        const storage = useLocalStorage;
        storage.removeItem("authTokens");
      }

      // Retry the operation without auth
      return forward(operation);
    }

    if (graphQLErrors) {
      graphQLErrors.forEach(({ message, locations, path }) =>
        console.log(
          `GraphQL error: Message: ${message}, Location: ${locations}, Path: ${path}`
        )
      );
    }

    if (networkError) {
      console.log(`Network error: ${networkError}`);
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
      errorLink,
      createErrorLink(),
      createAuthLink(),
      createSessionLink(),
      createUpdateLink(),
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