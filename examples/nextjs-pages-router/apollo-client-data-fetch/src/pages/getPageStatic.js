import Single from "@/components/Single";
import { client } from "@/lib/client";
import { gql } from "@apollo/client";

const PAGE_URI = "/sample-page";

const GET_PAGE = gql`
  query GetPage($id: ID!) {
    page(id: $id, idType: URI) {
      content
      id
      uri
      title
      date
      author {
        node {
          name
        }
      }
    }
  }
`;

export default function GetPageStatic({ data }) {
  return <Single data={data?.page} />;
}

export async function getStaticProps() {
  const { data } = await client.query({
    query: GET_PAGE,
    variables: {
      id: PAGE_URI,
    },
  });

  return {
    props: {
      data,
    },
  };
}
