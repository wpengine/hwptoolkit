import { ApolloClient, HttpLink, InMemoryCache, ApolloLink } from "@apollo/client";
import { createFragmentRegistry } from "@apollo/client/cache";
import { relayStylePagination } from "@apollo/client/utilities";
import { createPersistedQueryLink } from "@apollo/client/link/persisted-queries";
import { sha256 } from "crypto-hash";
import { loadErrorMessages, loadDevMessages } from "@apollo/client/dev";

export async function fetchGraphQL(query, variables) {
  try {
    //console.log('🚀 fetchGraphQL called with:');
    //console.log('  URL:', `${process.env.NEXT_PUBLIC_WORDPRESS_URL}/graphql`);
    // console.log('  Query:', query);
    // console.log('  Variables:', variables);

    const body = JSON.stringify({
      query,
      variables: {
        ...variables,
      },
    });

    //console.log('📦 Request body:', body);

    const response = await fetch(
      `${process.env.NEXT_PUBLIC_WORDPRESS_URL}/graphql`,
      {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body,
        cache: "default",
        next: {
          tags: ["wordpress"],
        },
      }
    );


    if (!response.ok) {
      const errorText = await response.text();
      console.error("❌ HTTP Error Details:");
      console.error("  Status:", response.status);
      console.error("  Status Text:", response.statusText);
      console.error("  Response Body:", errorText);
      throw new Error(`HTTP ${response.status}: ${response.statusText} - ${errorText}`);
    }

    const data = await response.json();


    if (data.errors) {
      console.error("❌ GraphQL Errors:", data.errors);
      throw new Error(`GraphQL Error: ${JSON.stringify(data.errors)}`);
    }
    //console.log(data);
    return data;
  } catch (error) {
    console.error("❌ fetchGraphQL Error Details:");
    console.error("  Error type:", error.constructor.name);
    console.error("  Error message:", error.message);
    console.error("  Error stack:", error.stack);
    throw error;
  }
}

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
// const link = createPersistedQueryLink({ sha256 }).concat(
//   new HttpLink({
//     uri: WORDPRESS_URL + "/graphql",
//     useGETForQueries: true,
//   })
// );
const link = new HttpLink({
  uri: WORDPRESS_URL + "/graphql",
  // useGETForQueries: true,
});
// --- DEBUGGING STEP ---
// This custom link will log the entire GraphQL operation before it's sent.
const logLink = new ApolloLink((operation, forward) => {
  //console.log('📬 [ApolloLink] Operation being sent:');
  // Using JSON.stringify to get a deep look into the operation object
  //console.log(JSON.stringify(operation, null, 2));
  return forward(operation);
});
// Initialize Apollo Client with the link and cache configuration
// More info: https://www.apollographql.com/docs/react/api/core/ApolloClient/
export const client = new ApolloClient({
  link: logLink.concat(link),
  ssrMode: typeof window === "undefined",
  cache: new InMemoryCache({
    typePolicies: {
      Query: {
        fields: {
          posts: relayStylePagination(), // Enable relay-style pagination for posts
          // More info: https://www.apollographql.com/docs/react/pagination/cursor-based#relay-style-cursor-pagination
        },
      },
    },
    //fragments: createFragmentRegistry(fragments), // Register the defined fragments
    //More info: https://www.apollographql.com/docs/react/data/fragments#registering-named-fragments-using-createfragmentregistry
 }),
});

// export const client = new ApolloClient({
//     // IMPORTANT: Replace this with your WordPress site's GraphQL endpoint.
//     uri: WORDPRESS_URL + "/graphql",
//     cache: new InMemoryCache(),
// });
export function gql(strings, ...values) {
  return strings.reduce((result, string, i) => {
    return result + string + (values[i] || "");
  }, "");
}