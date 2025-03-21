import { notFound } from "next/navigation";
import { SinglePageFragment } from "@/lib/fragments/SinglePageFragment";
import Page from "@/components/single/Page";
import { fetchGraphQL } from "@/lib/client";

const GET_CONTENT_QUERY = `
  ${SinglePageFragment}
  query GetNodeByUri($uri: String!) {
    nodeByUri(uri: $uri) {
      __typename
      ...SinglePageFragment
    }
  }
`;

export default async function HomePage({ params }) {
  const uri = Array.isArray(params?.uri) ? params.uri.join("/") : "";
  const data = await await fetchGraphQL(
    GET_CONTENT_QUERY,
    {
      uri: "/",
    },
    86400,
  );

  if (!data?.nodeByUri) {
    console.warn("No nodeByUri data found, returning 404");
    notFound();
  }

  const contentType = data?.nodeByUri?.__typename;
  if (contentType === "Page") return <Page data={data.nodeByUri} />;
  notFound();
}

// Note: We could generate static params for the pages you want to pre-render (optional) for things like popular posts etc
export async function generateStaticParams() {
  return [];
}
