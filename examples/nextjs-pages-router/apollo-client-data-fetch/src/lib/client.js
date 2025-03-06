import { ApolloClient, gql, HttpLink, InMemoryCache } from "@apollo/client";
import { createFragmentRegistry } from "@apollo/client/cache";
import { relayStylePagination } from "@apollo/client/utilities";
import { createPersistedQueryLink } from "@apollo/client/link/persisted-queries";
import { sha256 } from "crypto-hash";

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

const WORDPRESS_URL = process.env.NEXT_PUBLIC_WORDPRESS_URL;

const link = createPersistedQueryLink({ sha256 }).concat(
  new HttpLink({ uri: WORDPRESS_URL + "/graphql", useGETForQueries: true })
);

export const client = new ApolloClient({
  link,
  ssrMode: typeof window === "undefined",
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
