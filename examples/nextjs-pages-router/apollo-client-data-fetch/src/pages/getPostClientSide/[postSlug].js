import CouldNotLoad from "@/components/CouldNotLoad";
import Loading from "@/components/Loading";
import Single from "@/components/Single";
import { gql, useQuery } from "@apollo/client";
import { useRouter } from "next/router";

const GET_POST = gql`
  query GetPost($slug: ID!) {
    post(id: $slug, idType: SLUG) {
      content
      ...PostFragment
    }
  }
`;

export default function GetPostClientSide() {
  const router = useRouter();

  const { loading, data, error } = useQuery(GET_POST, {
    variables: { slug: router.query.postSlug },
  });

  if (loading) return <Loading />;

  if (error || !data?.post) return <CouldNotLoad />;

  return <Single data={data?.post} />;
}
