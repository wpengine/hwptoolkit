import { ApolloClient, HttpLink, InMemoryCache } from "@apollo/client";

const WORDPRESS_URL = process.env.NEXT_PUBLIC_WORDPRESS_URL;

function createApolloClient() {
  return new ApolloClient({
    link: new HttpLink({
      uri: WORDPRESS_URL + "/graphql",
      useGETForQueries: true,
    }),
    ssrMode: typeof window === "undefined",
    cache: new InMemoryCache(),
  });
}


let apolloClient;

export function getApolloClient() {
  // On the server, always create a new client
  if (typeof window === "undefined") {
    return createApolloClient();
  }
  
  if (!apolloClient) {
    apolloClient = createApolloClient();
  }
  
  return apolloClient;
}

export const client = getApolloClient();