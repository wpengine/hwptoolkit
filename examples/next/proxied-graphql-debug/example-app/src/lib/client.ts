import { ApolloClient, InMemoryCache, HttpLink, ApolloLink, Observable, FetchResult } from "@apollo/client";
 
export function getGraphqlPath() {
  const baseUrl =
    process.env.NEXT_PUBLIC_WORDPRESS_URL || "https://your-wordpress-site.com";
  const graphqlPath = process.env.NEXT_PUBLIC_GRAPHQL_PATH || "/graphql";
  return `${baseUrl}${graphqlPath}`;
}

const ForwardExtensionsLink = new ApolloLink((operation, forward) => {
  return new Observable(observer => {
    const sub = forward(operation).subscribe({
      next: result => {
        result.data.extensions = () => result.extensions
        observer.next(result)
      },
      complete: observer.complete.bind(observer),
    })
    return () => {
      if (sub) sub.unsubscribe()
    }
  })
})

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
    link: ForwardExtensionsLink.concat(httpLink),
    cache: new InMemoryCache(),
  });
}
