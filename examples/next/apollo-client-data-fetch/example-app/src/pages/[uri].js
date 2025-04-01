import CouldNotLoad from "../components/CouldNotLoad";
import Page from "../components/Page";
import Single from "../components/Single";
import { client } from "../lib/client";
import { gql } from "@apollo/client";

// GraphQL query with the Page and Post fragments
const GET_CONTENT = gql`
  query GetNodeByUri($uri: String!) {
    nodeByUri(uri: $uri) {
      __typename
      ...Page
      ...Post
    }
  }
`;

export default function Content({ data }) {
  const contentType = data?.nodeByUri?.__typename;

  // Render Single component if content type is "Post"
  if (contentType === "Post") return <Single data={data.nodeByUri} />;

  // Render Page component if content type is "Page"
  if (contentType === "Page") return <Page data={data.nodeByUri} />;

  // Render CouldNotLoad component if content type is unknown
  return <CouldNotLoad />;
}

// Next.js function to fetch data on the server side before rendering the page
export async function getServerSideProps({ params }) {
  const { data } = await client.query({
    query: GET_CONTENT,
    variables: {
      uri: params.uri,
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
