import { ApolloClient, gql, HttpLink, InMemoryCache } from "@apollo/client";
import { createFragmentRegistry } from "@apollo/client/cache";
import { relayStylePagination } from "@apollo/client/utilities";

const WORDPRESS_URL = process.env.NEXT_PUBLIC_WORDPRESS_URL;

const fragments = gql`
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

  fragment Page on Page {
    title
    content
  }

  fragment Post on Post {
    __typename
    id
    databaseId
    date
    uri
    content
    title
    comments {
      edges {
        node {
          ...Comment
        }
      }
    }
    ...Author
  }

  fragment Comment on Comment {
    id
    content
    date
    author {
      node {
        name
      }
    }
  }

  fragment Author on NodeWithAuthor {
    author {
      node {
        name
      }
    }
  }
`;

export const client = new ApolloClient({
  ssrMode: typeof window === "undefined",
  link: new HttpLink({
    uri: WORDPRESS_URL + "/graphql",
    useGETForQueries: true,
  }),
  cache: new InMemoryCache({
    typePolicies: {
      Query: {
        fields: {
          posts: relayStylePagination(),
        },
      },
    },
    fragments: createFragmentRegistry(fragments),
  }),
});
