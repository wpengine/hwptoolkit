import Single from "@/components/Single";
import { client } from "@/lib/client";
import { gql } from "@apollo/client";

const GET_POST = gql`
  query GetPost($slug: ID!) {
    post(id: $slug, idType: SLUG) {
      content
      ...PostFragment
    }
  }
`;

export default function GetPostServerSide({ data }) {
  return <Single data={data?.post} />;
}

export async function getServerSideProps({ params }) {
  const { data } = await client.query({
    query: GET_POST,
    variables: {
      slug: params.postSlug,
    },
  });

  if (!data?.post)
    return {
      notFound: true,
    };

  return {
    props: {
      data,
    },
  };
}
