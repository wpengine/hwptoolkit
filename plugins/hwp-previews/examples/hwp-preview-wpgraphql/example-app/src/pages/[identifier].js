import CouldNotLoad from "@/components/CouldNotLoad";
import Single from "@/components/Single";
import { client } from "@/lib/client";
import { getAuthString } from "@/utils/getAuthString";
import { gql } from "@apollo/client";

// We'll render Single component for these types
const SINGLE_TYPES = ["Post", "Page", "Building"];

// Get contentNode for previews and nodeByUri for normal content
const GET_CONTENT = gql`
  query GetSeedNode($id: ID! = 0, $uri: String! = "", $asPreview: Boolean = false) {
    nodeByUri(uri: $uri) @skip(if: $asPreview) {
      __typename
      ...Page
      ...Post
      ...Building
    }

    contentNode(id: $id, idType: DATABASE_ID, asPreview: true) @include(if: $asPreview) {
      __typename
      ...Page
      ...Post
      ...Building
    }
  }
`;

export default function Content({ data }) {
  const contentType = data?.__typename;

  if (SINGLE_TYPES.includes(contentType)) {
    return <Single data={data} />;
  }

  // Render CouldNotLoad component if content type is unknown
  return <CouldNotLoad />;
}

// Statically generate the pages, except for draft mode
// More info: https://nextjs.org/docs/pages/guides/draft-mode
export async function getStaticProps({ params, draftMode: isDraftModeEnabled }) {
  const variables = isDraftModeEnabled
    ? {
        id: params.identifier,
        asPreview: true,
      }
    : { uri: params.identifier };

  // Send the authentication string only if draft mode is enabled
  const headers = isDraftModeEnabled
    ? {
        Authorization: getAuthString(),
      }
    : null;

  const { data } = await client.query({
    query: GET_CONTENT,
    variables: variables,
    context: {
      headers,
    },
  });

  const content = data?.nodeByUri || data?.contentNode;

  // Return 404 page if no data is found
  if (!content)
    return {
      notFound: true,
    };

  // Pass the fetched data to the page component as props
  return {
    props: {
      data: content,
    },
  };
}

export async function getStaticPaths() {
  return {
    paths: [],
    fallback: "blocking",
  };
}
