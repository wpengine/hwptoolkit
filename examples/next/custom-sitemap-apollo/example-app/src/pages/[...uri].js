import Archive from "@/components/Archive";
import CouldNotLoad from "@/components/CouldNotLoad";
import Single from "@/components/Single";
import { client } from "@/lib/client";
import { gql } from "@apollo/client";

const ARCHIVE_TYPES = ["User", "Category", "Tag", "Period"];
const SINGLE_TYPES = ["Post", "Page", "Building"];

export default function Content({ data }) {
  const contentType = data?.nodeByUri?.__typename;

  // Periods have buildings instead of posts, so we need to handle them separately
  if (contentType === "Period") {
    const posts = data.nodeByUri?.buildings?.edges;

    return <Archive posts={posts} type={contentType} title={data?.nodeByUri?.name} />;
  }

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
  }

  fragment Building on Building {
    id
    title
    uri
    excerpt
    date
    content
  }

  query GetNodeByUri($uri: String!) {
    nodeByUri(uri: $uri) {
      __typename
      ...Page
      ...Post
      ...Building
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
      ... on Period {
        uri
        name
        buildings {
          edges {
            node {
              ...Building
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

// Next.js function to fetch data on the server side before rendering the page
export async function getServerSideProps({ params }) {
  const { data } = await client.query({
    query: GET_CONTENT,
    variables: {
      uri: params.uri.join("/"),
    },
  });

  // Return 404 page if no data is found
  if (!data?.nodeByUri)
    return {
      notFound: true,
    };

  // Pass the fetched data to the page component as props
  return {
    props: {
      data,
    },
  };
}
