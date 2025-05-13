import { ApolloClient, InMemoryCache, HttpLink } from "@apollo/client";
 
export function getGraphqlPath() {
  const baseUrl =
    process.env.NEXT_PUBLIC_WORDPRESS_URL || "https://your-wordpress-site.com";
  const graphqlPath = process.env.NEXT_PUBLIC_GRAPHQL_PATH || "/graphql";
  return `${baseUrl}${graphqlPath}`;
}

// const loggingLink = new ApolloLink((operation, forward) => {
//   const { query, variables, operationName } = operation;

//   console.log(`🚀 GraphQL Operation: ${operationName}`);
//   console.log('📄 Query:', query.loc?.source.body);
//   console.log('🛠️ Variables:', variables);

//   return forward(operation);
// });
// const endpoint = getGraphqlPath();

const endpoint =
  process.env.NODE_ENV === "development"
    ? "/api/proxy/graphql"
    : getGraphqlPath();

const httpLink = new HttpLink({
  uri: endpoint,
});

export default function getApolloClient() {
  return new ApolloClient({
    link: httpLink,
    cache: new InMemoryCache(),
  });
}
