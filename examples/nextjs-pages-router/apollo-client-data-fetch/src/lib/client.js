import { ApolloClient, gql, HttpLink, InMemoryCache } from "@apollo/client";
import { createFragmentRegistry } from "@apollo/client/cache";
import { relayStylePagination } from "@apollo/client/utilities";

const WORDPRESS_URL = process.env.NEXT_PUBLIC_WORDPRESS_URL;

export const client = new ApolloClient({
  ssrMode: typeof window === "undefined",
  link: new HttpLink({
    uri: WORDPRESS_URL + "/graphql",
  }),
  cache: new InMemoryCache({
    typePolicies: {
      Query: {
        fields: {
          posts: relayStylePagination(),
        },
      },
    },
    fragments: createFragmentRegistry(gql`
      fragment PostFragment on Post {
        id
        databaseId
        uri
        title
        date
        author {
          node {
            name
          }
        }
      }
      fragment CommentFragment on Comment {
        id
        content
        date
        author {
          node {
            name
          }
        }
      }
    `),
  }),
});
