import Page from "@/components/Page";
import { client } from "@/lib/client";
import { gql } from "@apollo/client";

const GET_PAGE = gql`
  query GetPage($id: ID!) {
    page(id: $id, idType: URI) {
      ...Page
    }
  }
`;

export default function GetPageStatic({ data }) {
  // Render the Page component with the fetched data
  return <Page data={data?.page} />;
}

export async function getStaticProps() {
  // Fetch the page data at build time using the Apollo Client
  const { data } = await client.query({
    query: GET_PAGE,
    variables: {
      id: process.env.NEXT_PRIVACY_POLICY_URI, // Use the environment variable for the page URI
    },
  });

  // If no page data is found, return a 404 response
  if (!data?.page) {
    return {
      notFound: true,
    };
  }

  // Pass the fetched data to the page component as props
  return {
    props: {
      data,
    },
  };
}
