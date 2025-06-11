import Archive from "@/components/Archive";
import CouldNotLoad from "@/components/CouldNotLoad";
import Single from "@/components/Single";
import { getApolloClient } from "@/lib/client";
import { gql } from "@apollo/client";

const ARCHIVE_TYPES = ["User", "Category", "Tag"];
const SINGLE_TYPES = ["Post", "Page"];

export default function Content({ data }) {
  const contentType = data?.nodeByUri?.__typename;

  if (ARCHIVE_TYPES.includes(contentType)) {
    const posts = data.nodeByUri?.posts?.edges;

    return <Archive posts={posts} type={contentType} title={data?.nodeByUri?.name} />;
  }

  if (SINGLE_TYPES.includes(contentType)) {
    return <Single data={data.nodeByUri} />;
  }

  // Render CouldNotLoad component if content type is unknown
  return <CouldNotLoad />;
}

// GraphQL query with the Page and Post fragments
const GET_CONTENT = gql`
  fragment Page on Page {
    title
    content
    featuredImage {
      node {
        sourceUrl(size: LARGE)
        caption
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
        sourceUrl(size: LARGE)
        caption
      }
    }
  }

  query GetNodeByUri($uri: String!) {
    nodeByUri(uri: $uri) {
      __typename
      ...Page
      ...Post
      ... on User {
        name
        posts {
          edges {
            node {
              ...Post
            }
          }
        }
      }
      ... on Category {
        uri
        name
        posts {
          edges {
            node {
              ...Post
            }
          }
        }
      }
      ... on Tag {
        uri
        name
        posts {
          edges {
            node {
              ...Post
            }
          }
        }
      }
      ... on PostFormat {
        uri
        name
        posts {
          edges {
            node {
              ...Post
            }
          }
        }
      }
    }
  }
`;

// Static generation with ISR
export async function getStaticProps({ params }) {
  try {
    const { data } = await getApolloClient().query({
      query: GET_CONTENT,
      variables: {
        uri: params.uri.join("/"),
      },
    });
    if (!data?.nodeByUri) {
      return {
        notFound: true,
      };
    }

    console.debug("Fetched data:", data);
    return {
      props: {
        data,
      },
      revalidate: 60,
    };
  } catch (error) {
    console.error("Error fetching data:", error);
    return {
      notFound: true,
    };
  }
}
export async function getStaticPaths() {
  return {
    paths: [],
    fallback: 'blocking',
  };
}