import {
  ApolloClient,
  InMemoryCache,
  ApolloLink,
  HttpLink,
} from "@apollo/client";
import useLocalStorage from "./storage";

const baseUrl =
  process.env.NEXT_PUBLIC_WORDPRESS_URL || "http://127.0.0.1:8890";
const graphqlPath = process.env.NEXT_PUBLIC_GRAPHQL_PATH || "/graphql";
const httpLink = new HttpLink({
  uri: `${baseUrl}${graphqlPath}`,
});
const authLink = (storage) =>
  new ApolloLink((operation, forward) => {
    const storedTokens = storage.getItem("authTokens");
    if (!storedTokens) {
      return forward(operation);
    }

    const tokens = JSON.parse(storedTokens);
    if (!tokens || !tokens.authToken) {
      return forward(operation);
    }

    operation.setContext({
      headers: {
        Authorization: `Bearer ${tokens.authToken}`,
      },
    });

    return forward(operation);
  });

export default function getApolloClient() {
  return new ApolloClient({
    link: authLink(useLocalStorage).concat(httpLink),
    cache: new InMemoryCache(),
  });
}
