import {
  ApolloClient,
  InMemoryCache,
  HttpLink,
} from "@apollo/client";

const baseUrl =
  process.env.NEXT_PUBLIC_WORDPRESS_URL || "https://your-wordpress-site.com";
const graphqlPath = process.env.NEXT_PUBLIC_GRAPHQL_PATH || "/graphql";
const httpLink = new HttpLink({
  uri: `${baseUrl}${graphqlPath}`,
});

export default function getApolloClient() {
  return new ApolloClient({
    link: httpLink,
    cache: new InMemoryCache(),
  });
}
