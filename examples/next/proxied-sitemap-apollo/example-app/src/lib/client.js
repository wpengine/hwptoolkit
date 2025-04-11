import { ApolloClient, HttpLink, InMemoryCache } from "@apollo/client";

// Get the WordPress URL from environment variables
// More info: https://nextjs.org/docs/basic-features/environment-variables
const WORDPRESS_URL = process.env.NEXT_PUBLIC_WORDPRESS_URL;

// Initialize Apollo Client with the link and cache configuration
// More info: https://www.apollographql.com/docs/react/api/core/ApolloClient/
export const client = new ApolloClient({
  link: new HttpLink({
    uri: WORDPRESS_URL + "/graphql",
    useGETForQueries: true,
  }),
  ssrMode: typeof window === "undefined", // Enable SSR mode for server-side rendering
  cache: new InMemoryCache(),
});