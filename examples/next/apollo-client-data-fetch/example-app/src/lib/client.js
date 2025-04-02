import { ApolloClient, gql, HttpLink, InMemoryCache } from "@apollo/client";
import { createFragmentRegistry } from "@apollo/client/cache";
import { relayStylePagination } from "@apollo/client/utilities";
import { createPersistedQueryLink } from "@apollo/client/link/persisted-queries";
import { sha256 } from "crypto-hash";

// Define GraphQL fragments for reuse in queries and mutations
// More info: https://www.apollographql.com/docs/react/data/fragments/
const fragments = gql`
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
    author {
      node {
        name
      }
    }
    featuredImage {
      node {
        sourceUrl(size: LARGE)
        caption
      }
    }
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
`;

// Get the WordPress URL from environment variables
// More info: https://nextjs.org/docs/basic-features/environment-variables
const WORDPRESS_URL = process.env.NEXT_PUBLIC_WORDPRESS_URL;

// Create a link for persisted queries with SHA-256 hashing
// More info: https://www.apollographql.com/docs/apollo-server/performance/apq
const link = createPersistedQueryLink({ sha256 }).concat(
  new HttpLink({
    uri: WORDPRESS_URL + "/graphql",
    useGETForQueries: true,
  })
);

// Initialize Apollo Client with the link and cache configuration
// More info: https://www.apollographql.com/docs/react/api/core/ApolloClient/
export const client = new ApolloClient({
  link,
  ssrMode: typeof window === "undefined", // Enable SSR mode for server-side rendering
  cache: new InMemoryCache({
    typePolicies: {
      Query: {
        fields: {
          posts: relayStylePagination(), // Enable relay-style pagination for posts
          // More info: https://www.apollographql.com/docs/react/pagination/cursor-based#relay-style-cursor-pagination
        },
      },
    },
    fragments: createFragmentRegistry(fragments), // Register the defined fragments
    // More info: https://www.apollographql.com/docs/react/data/fragments#registering-named-fragments-using-createfragmentregistry
  }),
});
