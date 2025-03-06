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
  return <Page data={data?.page} />;
}

export async function getStaticProps() {
  const { data } = await client.query({
    query: GET_PAGE,
    variables: {
      id: process.env.NEXT_PRIVACY_POLICY_URI,
    },
  });

  if (!data?.page) {
    return {
      notFound: true,
    };
  }

  return {
    props: {
      data,
    },
  };
}
