import CouldNotLoad from "@/components/CouldNotLoad";
import Page from "@/components/Page";
import Single from "@/components/Single";
import { client } from "@/lib/client";
import { gql } from "@apollo/client";

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

  if (contentType === "Post") return <Single data={data.nodeByUri} />;

  if (contentType === "Page") return <Page data={data.nodeByUri} />;

  return <CouldNotLoad />;
}

export async function getServerSideProps({ params }) {
  const { data } = await client.query({
    query: GET_CONTENT,
    variables: {
      uri: params.uri,
    },
  });

  if (!data?.nodeByUri)
    return {
      notFound: true,
    };

  return {
    props: {
      data,
    },
  };
}
