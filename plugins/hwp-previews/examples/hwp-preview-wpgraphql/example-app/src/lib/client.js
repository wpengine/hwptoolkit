import { ApolloClient, gql, HttpLink, InMemoryCache } from "@apollo/client";
import { createFragmentRegistry } from "@apollo/client/cache";
import { relayStylePagination } from "@apollo/client/utilities";

// Define GraphQL fragments for reuse in queries and mutations
// More info: https://www.apollographql.com/docs/react/data/fragments/
const fragments = gql`
  fragment FeaturedImage on MediaItem {
    sourceUrl(size: LARGE)
    caption
  }

  fragment Page on Page {
    title
    content
    featuredImage {
      node {
        ...FeaturedImage
      }
    }
  }

  fragment Post on Post {
    __typename
    id
    databaseId
    date
    uri
    content
    excerpt
    title
    author {
      node {
        name
      }
    }
    featuredImage {
      node {
        ...FeaturedImage
      }
    }
  }

  fragment Building on Building {
    id
    title
    uri
    excerpt
    date
    content
    featuredImage {
      node {
        ...FeaturedImage
      }
    }
  }
`;

// Get the WordPress URL from environment variables
// More info: https://nextjs.org/docs/basic-features/environment-variables
const WORDPRESS_URL = process.env.NEXT_PUBLIC_WORDPRESS_URL;

// Initialize Apollo Client with the link and cache configuration
// More info: https://www.apollographql.com/docs/react/api/core/ApolloClient/
export const client = new ApolloClient({
  link: new HttpLink({
    uri: WORDPRESS_URL + "/graphql",
  }),
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
