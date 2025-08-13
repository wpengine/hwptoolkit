import {
  ApolloClient,
  InMemoryCache,
  ApolloLink,
  HttpLink,
  from,
} from "@apollo/client";
import { onError } from "@apollo/client/link/error";
import useLocalStorage from "./storage";

const baseUrl =
  process.env.NEXT_PUBLIC_WORDPRESS_URL || "http://localhost:8890";
const graphqlPath = process.env.NEXT_PUBLIC_GRAPHQL_PATH || "/graphql";

const httpLink = new HttpLink({
  uri: `${baseUrl}${graphqlPath}`,
});

// error handling for auth issues
const errorLink = onError(
  ({ graphQLErrors, networkError, operation, forward }) => {
    if (networkError && networkError.statusCode === 403) {
      console.warn(
        "403 Forbidden - clearing auth tokens and retrying without auth"
      );

      // Clear invalid tokens
      const storage = useLocalStorage;
      storage.removeItem("authTokens");

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

const authLink = (storage) =>
  new ApolloLink((operation, forward) => {
    const storedTokens = storage.getItem("authTokens");
    if (!storedTokens) {
      return forward(operation);
    }

    try {
      const tokens = JSON.parse(storedTokens);
      if (!tokens || !tokens.authToken) {
        return forward(operation);
      }

      if (tokens.expiresAt && new Date() > new Date(tokens.expiresAt)) {
        console.warn("Auth token expired, removing from storage");
        storage.removeItem("authTokens");
        return forward(operation);
      }

      operation.setContext({
        headers: {
          Authorization: `Bearer ${tokens.authToken}`,
        },
      });

      return forward(operation);
    } catch (error) {
      console.error("Error parsing auth tokens:", error);
      storage.removeItem("authTokens");
      return forward(operation);
    }
  });

export default function getApolloClient() {
  return new ApolloClient({
    link: from([errorLink, authLink(useLocalStorage), httpLink]),
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
